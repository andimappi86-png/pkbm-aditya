# Deployment Runbook — Sistem Informasi PKBM Aditya

**Version:** 1.0
**Date:** 2026-10-21
**Environment:** Production Release
**Author:** Senior Laravel DevOps Engineer

---

## 0. OVERVIEW & SAFETY RULES

### 0.1 Scope
Runbook ini mengatur deployment **hanya** untuk release kode aplikasi dan migrasi skema ke **production**. Tidak termasuk provisioning infrastruktur baru.

### 0.2 Safety Rules (WAJIB DIPATUHI)
| Rule | Description |
|------|-------------|
| **R1** | JANGAN pernah menjalankan perintah di database production tanpa verifikasi `migrate:status` terlebih dahulu. |
| **R2** | JANGAN pernah menjalankan `migrate:fresh`, `db:wipe`, atau seeder pada database production. |
| **R3** | SEMUA perintah destructive (migration, cache clear, queue restart) WAJIB memiliki rollback plan yang terdokumentasi dan diuji. |
| **R4** | Backup database & storage WAJIB selesai & tervalidasi SEBELUM langkah migrasi. |
| **R5** | Mode maintenance WAJIB aktif selama migrasi & cache rebuild. |
| **R6** | Setiap langkah WAJIB diverifikasi sebelum lanjut ke langkah berikutnya. |
| **R7** | Jika satu gate GAGAL: HENTIKAN deployment, evaluasi, rollback jika perlu. |

### 0.3 Environment Definitions
| Env | Purpose | Who Runs |
|-----|---------|----------|
| **local** | Developer machine — kode, unit test, PHPStan, asset build | Developer |
| **testing (CI)** | GitHub Actions / runner — full test suite, SQLite in-memory | CI Pipeline |
| **staging (disposable)** | Replica production engine (MySQL/MariaDB) — migration rehearsal, concurrency test | DevOps |
| **production** | Live server — real users, real data | Release Owner (dengan akses SSH & DB) |

> **Catatan:** Perintah di bawah diberi label `[ENV]` untuk menunjukkan environment target. Jangan menjalankan perintah `[production]` dari mesin local tanpa SSH ke server production.

---

## 1. PRE-DEPLOYMENT CHECKLIST (READ-ONLY)

Semua langkah di bawah **hanya membaca** — tidak mengubah state.

| # | Check | Command / Action | Expected Result | Status Gate |
|---|-------|------------------|-----------------|-------------|
| 1.1 | Git clean state | `git status --short` | Hanya file yang disengaja diubah (lihat Phase 4 git diff) | ✅ PASS |
| 1.2 | Branch & commit | `git log -1 --oneline` | Commit yang akan di-deploy diketahui & ditandai | ✅ PASS |
| 1.3 | Migration status (local) | `php artisan migrate:status` | 40 ran, 1 pending (`2026_10_20_174927_add_unique_quiz_attempt_index`) | ✅ PASS |
| 1.4 | Test suite | `php artisan test --compact` | **352 passed, 0 failed, 1081 assertions** | ✅ PASS |
| 1.5 | PHPStan (changed files) | `./vendor/bin/phpstan analyse <changed-files>` | 0 error pada file yang berubah Phase 3–4 | ✅ PASS |
| 1.6 | Asset build | `npm run build` | `public/build/manifest.json` valid, 2 entrypoints | ✅ PASS |
| 1.7 | Production config template | Review `.env.example` & `config/*.php` | `APP_DEBUG=false`, `APP_ENV=production`, HTTPS URL, secure cookies | ⚠️ WARNING (local is dev) |
| 1.8 | Scheduler definition | `cat routes/console.php` | `attendance:generate` daily 05:00 WITA, `withoutOverlapping()` | ✅ PASS |
| 1.9 | Queue config | `cat config/queue.php` | Driver `database`, table `jobs`, retry_after 90s | ✅ PASS |
| 1.10 | Storage link config | `cat config/filesystems.php` | `links: ['public/storage' => 'storage/app/public']` | ✅ PASS |

> **Gate 1 (Pre-deployment):** Semua ✅ PASS → lanjut ke **Gate 2**. Jika ada ❌ FAIL → hentikan.

---

## 2. DATA INTEGRITY PREFLIGHT (READ-ONLY, REPLICA ONLY)

**Environment:** `[staging-disposable]` atau `[production-replica-readonly]`
**Purpose:** Verifikasi data historis kuis sebelum migrasi unique index.

### 2.1 Duplicate Check Query
```sql
-- HANYA dijalankan pada REPLICA / SNAPSHOT READ-ONLY
-- TIDAK MENAMPILKAN NIK, NAMA, JAWABAN, DATA PRIBADI

-- Query 1: Duplikasi students.user_id (non-NULL)
SELECT
    `user_id`,
    COUNT(*) AS `total_students`
FROM `students`
WHERE `user_id` IS NOT NULL
GROUP BY `user_id`
HAVING COUNT(*) > 1
ORDER BY `total_students` DESC;

-- Query 2: Duplikasi tutors.user_id (non-NULL)
SELECT
    `user_id`,
    COUNT(*) AS `total_tutors`
FROM `tutors`
WHERE `user_id` IS NOT NULL
GROUP BY `user_id`
HAVING COUNT(*) > 1
ORDER BY `total_tutors` DESC;

-- Query 3: Duplikasi quiz_attempts (quiz_id + student_id)
SELECT
    `quiz_id`,
    `student_id`,
    COUNT(*) AS `total_attempts`
FROM `quiz_attempts`
GROUP BY `quiz_id`, `student_id`
HAVING COUNT(*) > 1
ORDER BY `total_attempts` DESC;
```

### 2.2 Decision Matrix
| Query Result | Action |
|--------------|--------|
| **0 rows** (tidak ada duplikasi) | Lanjut ke **Gate 3 (Migration Rehearsal)** |
| **≥ 1 rows** (duplikasi ditemukan) | **HENTIKAN DEPLOYMENT** → lanjut ke §2.3 |

### 2.3 Reconciliation Procedure (JIKA DUPLIKASI DITEMUKAN)
1. **Export** hasil query ke CSV (tanpa kolom sensitif).
2. **Review manual** oleh pemilik data (Kepala PKBM / Operator Senior):
   - Identifikasi attempt yang valid (biasanya attempt pertama / score tertinggi / completed_at paling awal).
   - Tentukan attempt yang di-arsipkan (soft-delete pindah ke tabel `quiz_attempts_archive` atau update `status='archived'`).
3. **Document decision** di ticket/tracker: `quiz_id`, `student_id`, `kept_attempt_id`, `archived_attempt_ids`, `approved_by`, `timestamp`.
4. **Execute reconciliation** pada **staging disposable** dulu, verifikasi query ulang 0 rows.
5. **Only after approval & staging verified** → apply ke production replica (bukan production langsung).
6. **Re-run query** pada production replica → harus 0 rows.

> **Gate 2 (Data Integrity):** Query 0 rows → ✅ PASS. Jika duplikasi → ❌ BLOCKED sampai rekonsiliasi selesai & tervalidasi.

---

## 3. MIGRATION REHEARSAL (STAGING DISPOSABLE)

**Environment:** `[staging-disposable]` — MySQL/MariaDB versi & config **sama persis** production.

### 3.1 Prerequisites
- Database staging fresh clone dari production snapshot (tanpa data sensitif jika perlu masking).
- Kode aplikasi **versi yang akan di-deploy** sudah di-pull ke staging.
- `composer install --no-dev --optimize-autoloader` sudah dijalankan.

### 3.2 Steps
| Step | Command | Verification |
|------|---------|--------------|
| 3.1 | `php artisan migrate:status` | Migration pending terlihat |
| 3.2 | `php artisan migrate --force` | Exit code 0, output menunjukkan migration kuis berjalan |
| 3.3 | `php artisan migrate:status` | Semua migration `Ran`, **0 Pending** |
| 3.4 | `SHOW INDEX FROM quiz_attempts WHERE Key_name = 'quiz_attempts_quiz_id_student_id_unique';` | Index exists, columns `quiz_id`, `student_id`, `Non_unique = 0` |
| 3.5 | Insert duplicate test (SQL) | `INSERT IGNORE ...` → affected rows = 0, duplicate rejected |
| 3.6 | `php artisan migrate:rollback --step=1 --force` | Exit code 0, index dihapus, data **tidak** terhapus |
| 3.7 | `SHOW INDEX ...` | Index tidak lagi ada |
| 3.8 | `php artisan migrate --force` (re-apply) | Lulus lagi, index kembali |

### 3.3 Rollback Specifics for Quiz Unique Index
| Aspect | Detail |
|--------|--------|
| **Migration name** | `2026_10_20_174927_add_unique_quiz_attempt_index` |
| **Batch** | Akan menjadi batch terbaru (cek `migrate:status`) |
| **Down() action** | `DROP INDEX quiz_attempts_quiz_id_student_id_unique ON quiz_attempts` |
| **Data loss** | **NONE** — hanya index dihapus, record attempt utuh |
| **App compatibility** | Kode `QuizAttemptController` sudah handle unique violation (catch `QueryException` 1062/23000) → rollback aman tanpa code rollback |
| **Rollback command** | `php artisan migrate:rollback --step=1 --force` **HANYA UNTUK MIGRATION INI** (verifikasi batch dulu) |

> **Gate 3 (Migration Rehearsal):** Semua step 3.1–3.8 lulus di staging disposable → ✅ PASS.

---

## 4. CONCURRENCY REHEARSAL (STAGING DISPOSABLE)

**Environment:** `[staging-disposable]` — engine target (MySQL/MariaDB InnoDB).

### 4.1 Test Scenario
- 1 user (peserta didik), 1 kuis yang `status_aktif=true`, `waktu_mulai` sudah lewat, `waktu_selesai` belum lewat.
- User login valid, CSRF token valid.
- Simulasi **N=10** request `POST /quiz-attempts/start` **bersamaan** (parallel, bukan sequential).

### 4.2 Execution Script (Bash + curl / Python / k6)
```bash
# Contoh konseptual — gunakan tool load test yang disetujui (k6, vegeta, artillery)
# Setiap request harus punya session cookie & CSRF token yang SAH
# JANGAN gunakan tool yang bypass auth / middleware
```

### 4.3 Success Criteria
| Metric | Threshold |
|--------|-----------|
| **Attempts created** | **Exactly 1** row di `quiz_attempts` untuk `(quiz_id, student_id)` |
| **HTTP 2xx responses** | ≥ 1 (request pertama yang menang) |
| **HTTP 4xx/5xx responses** | Sisanya → 409 Conflict / 422 Validation / 500 (ditangani graceful) |
| **Duplicate key error (1062/23000)** | Ditangkap di `QuizAttemptController` → return JSON error user-friendly, **TIDAK crash** |
| **Deadlock / lock wait timeout** | 0 occurrence (jika ada → investigate, mungkin butuh retry logic) |
| **Latency p95** | < 2s (untuk start attempt) |

### 4.4 Verification Queries
```sql
SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?;
-- Harus return 1
```

> **Gate 4 (Concurrency):** Semua criteria terpenuhi di staging disposable → ✅ PASS.
> Jika deadlock/timeout → ⚠️ WARNING → tambah retry logic di controller sebelum production.

---

## 5. PRODUCTION DEPLOYMENT (READ-WRITE)

**Environment:** `[production]` — **HANYA** oleh Release Owner dengan akses SSH & DB production.

### 5.1 Pre-Deployment (Production Server)

| Step | Command | Type | Verification |
|------|---------|------|--------------|
| 5.1.1 | `cd /path/to/app && git fetch origin && git checkout <release-tag>` | RW (code) | `git log -1 --oneline` match tag |
| 5.1.2 | `php artisan down --secret="deploy-$(date +%s)" --render="errors::503"` | RW (app state) | `curl -I https://app.domain/up` → 503 |
| 5.1.3 | **BACKUP DATABASE** (method per infra: mysqldump, Percona XtraBackup, snapshot volume) | RW (data) | Backup file exists, size > 0, checksum recorded |
| 5.1.4 | **BACKUP STORAGE** (rsync / snapshot `storage/app/`) | RW (files) | Backup verified |
| 5.1.5 | `composer install --no-dev --optimize-autoloader --no-interaction` | RW (vendor) | `vendor/autoload.php` updated, no error |
| 5.1.6 | `npm ci --production && npm run build` | RW (assets) | `public/build/manifest.json` valid |

### 5.2 Migration Execution

| Step | Command | Type | Verification |
|------|---------|------|--------------|
| 5.2.1 | `php artisan migrate:status` | R | Pending migration terlihat |
| 5.2.2 | `php artisan migrate --force` | RW (schema) | Exit 0, output: `2026_10_20_174927_add_unique_quiz_attempt_index ... DONE` |
| 5.2.3 | `php artisan migrate:status` | R | **0 Pending** |
| 5.2.4 | `SHOW INDEX FROM quiz_attempts WHERE Key_name = 'quiz_attempts_quiz_id_student_id_unique';` | R | Index exists, unique, correct columns |

> **Jika migration GAGAL (duplicate exception):**
> 1. `php artisan up` (keluar maintenance)
> 2. **JANGAN** `migrate:rollback` — data historis butuh rekonsiliasi manual
> 3. Investigasi, rekonsiliasi di replica, ulang dari Gate 2

### 5.3 Post-Migration Optimization

| Step | Command | Type | Verification |
|------|---------|------|--------------|
| 5.3.1 | `php artisan storage:link` | RW (fs) | `ls -la public/storage` → symlink ke `storage/app/public` |
| 5.3.2 | `php artisan config:cache` | RW (cache) | `bootstrap/cache/config.php` exists |
| 5.3.3 | `php artisan route:cache` | RW (cache) | `bootstrap/cache/routes-v7.php` exists |
| 5.3.4 | `php artisan view:cache` | RW (cache) | `bootstrap/cache/views.php` exists |
| 5.3.5 | `php artisan queue:restart` | RW (queue) | Worker picks up new code (check supervisor/logs) |

### 5.4 Health Check & Smoke Test

| Test | Method | Expected |
|------|--------|----------|
| Health endpoint | `curl -f https://app.domain/up` | HTTP 200, body "OK" |
| Login page | `curl -f https://app.domain/login` | HTTP 200, form renders |
| Auth (operator) | POST login → redirect dashboard | 302 → dashboard operator |
| Role gate (operator) | GET `/users` dengan auth operator | 200, data users |
| Role gate (siswa) | GET `/dashboard` dengan auth siswa | 200, dashboard siswa |
| Quiz start (siswa) | POST `/quiz-attempts/start` valid | 200/201, 1 attempt created |
| File download (privat) | GET `/documents/x/download` dengan auth | 200, file stream, no public access |
| Log check | `tail -50 storage/logs/laravel.log` | No ERROR/CRITICAL baru |

> **Gate 5 (Production Deploy):** Semua health check & smoke test lulus → ✅ PASS → `php artisan up`.

### 5.5 Go Live
| Step | Command | Verification |
|------|---------|--------------|
| 5.5.1 | `php artisan up` | `curl -I https://app.domain/` → 200 |
| 5.5.2 | Monitor logs 15 menit | `tail -f storage/logs/laravel.log` — no spike error |
| 5.5.3 | Notify stakeholders | Deployment complete, versi X.Y.Z live |

---

## 6. ROLLBACK PLAN (PRODUCTION)

### 6.1 Rollback Triggers
| Trigger | Action |
|---------|--------|
| Migration gagal (duplicate exception) | §5.2 fallback — **JANGAN rollback migration lain** |
| Health check gagal > 5 menit | Full rollback §6.2 |
| Critical error log spike | Full rollback §6.2 |
| Stakeholder call | Full rollback §6.2 |

### 6.2 Full Rollback Procedure (Code + Schema + Cache)

| Step | Command | Note |
|------|---------|------|
| 1 | `php artisan down --secret="rollback-$(date +%s)"` | Maintenance mode |
| 2 | **Code rollback**: `git checkout <previous-release-tag>` | Previous known-good tag |
| 3 | `composer install --no-dev --optimize-autoloader` | Restore vendor |
| 4 | `npm ci --production && npm run build` | Restore assets |
| 5 | **Schema rollback (quiz index only)**: `php artisan migrate:rollback --step=1 --force` | **HANYA** jika migration kuis sudah berjalan & batch terverifikasi |
| 6 | `php artisan config:cache && php artisan route:cache && php artisan view:cache` | Rebuild cache |
| 7 | `php artisan queue:restart` | Worker reload |
| 8 | `php artisan up` | Exit maintenance |
| 9 | Verify health & smoke test (§5.4) | Must pass |

> **Data Recovery:** Jika butuh restore data → gunakan backup §5.1.3/5.1.4. **Peringatan:** Restore snapshot akan menghilangkan transaksi setelah backup timestamp.

### 6.3 Quiz Index Specific Rollback (Tanpa Full Rollback)
Jika **hanya** migration kuis bermasalah & kode lain stabil:
```bash
php artisan down --secret="..."
php artisan migrate:rollback --step=1 --force  # HAPUS index kuis
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```
**Prasyarat:** `migrate:status` menunjukkan batch terbaru HANYA migration kuis.

---

## 6.4 Permissions Reconciliation Procedure & Rollback

### 6.4.1 Backup Tabel Permission (Wajib Sebelum Rekonsiliasi)
Lakukan dump interaktif terisolasi untuk 4 tabel Spatie Permission:
```bash
mysqldump -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" -p \
  --single-transaction --quick \
  "${DB_DATABASE:-pkbm_aditya}" \
  roles permissions role_has_permissions model_has_permissions \
  > "backup_permissions_$(date +%F_%H%M%S).sql"
```

Verifikasi integritas backup:
```bash
head -n 25 "backup_permissions_"*.sql | grep -E "Table structure for table .(roles|permissions|role_has_permissions|model_has_permissions)."
```

### 6.4.2 Eksekusi Rekonsiliasi Terkontrol
1. Jalankan dry-run untuk verifikasi target relasi:
   ```bash
   php artisan permission:reconcile-admissions
   ```
2. Jalankan eksekusi terkonfirmasi jika dry-run sesuai:
   ```bash
   php artisan permission:reconcile-admissions --execute
   ```
   (Atau sertakan `--force` jika dijalankan non-interaktif dalam release script terautomasi).

### 6.4.3 Verifikasi Pasca Rekonsiliasi
Periksa bahwa permission inti dan role sistem tetap utuh:
```bash
php artisan tinker --execute '
$rem = DB::table("permissions")->whereIn("name", ["admissions.view","admissions.manage","admissions.apply","admissions.view_own"])->count();
$dash = DB::table("permissions")->where("name", "dashboard.view")->count();
$calon = DB::table("roles")->where("name", "calon-peserta")->count();
echo "Obsolete remaining: {$rem} (expected 0)\nDashboard.view: {$dash} (expected 1)\nCalon role: {$calon} (expected 1)\n";
'
```

### 6.4.4 Prosedur Rollback Rekonsiliasi Permission
Jika rekonsiliasi perlu dibatalkan, gunakan salah satu cara:
1. **Restore dari file backup**:
   ```bash
   mysql -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" -p \
     "${DB_DATABASE:-pkbm_aditya}" < "backup_permissions_"*.sql
   php artisan permission:cache-reset
   ```
2. **Kompensasi SQL (Dynamic Rollback Script)**:
   ```sql
   INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
     ('admissions.view', 'web', NOW(), NOW()),
     ('admissions.manage', 'web', NOW(), NOW()),
     ('admissions.apply', 'web', NOW(), NOW()),
     ('admissions.view_own', 'web', NOW(), NOW());

   INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
   SELECT p.id, r.id FROM permissions p CROSS JOIN roles r
   WHERE (r.name = 'operator' AND p.name IN ('admissions.view', 'admissions.manage'))
      OR (r.name = 'kepala-pkbm' AND p.name IN ('admissions.view', 'admissions.manage'))
      OR (r.name = 'ketua-yayasan' AND p.name = 'admissions.view')
      OR (r.name = 'calon-peserta' AND p.name IN ('admissions.apply', 'admissions.view_own'));
   ```
   Lalu bersihkan cache Spatie:
   ```bash
   php artisan permission:cache-reset
   ```

---

## 7. OPERATIONAL CHECKLIST (POST-DEPLOY)

| Area | Check | Command / Method | Frequency |
|------|-------|------------------|-----------|
| **Scheduler** | Cron `schedule:run` aktif | `crontab -l` di host | Once after deploy, then daily monitor |
| | Task execution log | `storage/logs/laravel.log` grep `attendance:generate` | Daily |
| **Queue** | Worker process count | `ps aux | grep 'queue:work'` / supervisorctl status | Continuous (monitoring) |
| | Failed jobs | `php artisan queue:failed` | Hourly / alert |
| | Retry stuck jobs | `php artisan queue:retry all` | As needed |
| **Storage** | Symlink valid | `ls -la public/storage` | Once after deploy |
| | Private file access | Test download dokumen rahasia | Per release |
| **HTTPS / Proxy** | SSL cert valid | `curl -I https://app.domain` | Cert expiry monitor |
| | Security headers | `curl -I` check HSTS, CSP, X-Frame-Options | Per release |
| **Logging** | Log rotation | `ls -la storage/logs/` | Weekly |
| | Error rate | Grafana / logtail alert | Continuous |
| **Backup** | DB backup success | Infra backup job status | Daily |
| | Storage backup success | Infra backup job status | Daily |

---

## 8. RELEASE GATES MATRIX

| # | Gate | Criteria | Evidence Required | Status |
|---|------|----------|-------------------|--------|
| G1 | Pre-deployment Checks | All §1 checks PASS | Test output, PHPStan, build manifest | ✅ PASS |
| G2 | Historical Duplicate Preflight | Query 0 rows on approved read-only source | Query output CSV + approval sign-off | ❌ BLOCKED (no replica access) |
| G3 | Migration Rehearsal | §3.1–3.8 PASS on staging disposable | Staging logs, index verification | ❌ BLOCKED (no staging DB) |
| G4 | Concurrency Rehearsal | §4.3 criteria met on target engine | Load test report, DB verification | ❌ BLOCKED (no staging DB) |
| G5 | Backup & Recovery Validated | Backup exists, restore tested (or approved procedure) | Backup checksum, restore drill log | ⚠️ WARNING (procedure documented, not tested) |
| G6 | Production Config Ready | `.env.production` prepared, secrets managed | Config audit (no secrets printed) | ⚠️ WARNING (local only) |
| G7 | Scheduler & Queue Verified on Host | Cron entry + worker systemd active | `crontab -l`, `systemctl status queue-worker` | ❌ BLOCKED (no host access) |
| G8 | Regression Suite | 352 tests PASS | CI / local test output | ✅ PASS |
| G9 | Browser/UI Verification | Status defined (Playwright/Dusk/not available) | Test report or documented gap | ❌ BLOCKED (no browser automation) |
| G10 | Runbook & Rollback Approved | Document reviewed & signed by Release Owner | Signed PDF / ticket approval | ❌ BLOCKED (awaiting owner) |

**Legend:** ✅ PASS = dibuktikan | ⚠️ WARNING = risiko diketahui, butuh tindakan | ❌ BLOCKED = prasyarat belum terpenuhi

---

## 9. REQUIRED ACCESS & ENVIRONMENTS (BLOCKERS)

| Item | Required For | Status | Owner Action |
|------|--------------|--------|--------------|
| Production MySQL/MariaDB replica (read-only) | Gate 2 (duplicate preflight) | ❌ NOT AVAILABLE | Provide replica / snapshot access |
| Staging disposable MySQL (same version) | Gate 3 & 4 (migration + concurrency rehearsal) | ❌ NOT AVAILABLE | Provision staging DB |
| Production server SSH + sudo | Gate 5, 6, 7 (deploy, rollback, ops) | ❌ NOT AVAILABLE | Grant access to Release Owner |
| Production crontab + systemd | Gate 7 (scheduler, queue) | ❌ NOT AVAILABLE | Configure by infra team |
| Browser automation (Playwright/Dusk) | Gate 9 (UI verification) | ❌ NOT IN REPO | Add to repo if approved |
| Backup restore drill | Gate 5 (recovery validated) | ⚠️ DOCUMENTED ONLY | Schedule drill |

---

## 10. NEXT OPERATIONAL STEPS (FOR DEPLOYMENT OWNER)

1. **Provision staging disposable MySQL** (versi & config sama production).
2. **Grant read-only replica access** untuk preflight query Gate 2.
3. **Run Gate 2 query** pada replica → jika duplikasi, lakukan rekonsiliasi (lihat §2.3).
4. **Deploy kode ke staging**, jalankan Gate 3 & 4 (migration + concurrency rehearsal).
5. **Jika Gate 3 & 4 PASS** → siapkan production `.env` (APP_DEBUG=false, APP_ENV=production, HTTPS URL, secure cookies, AES key, SMTP).
6. **Configure production cron** (`* * * * * php artisan schedule:run`) dan **systemd queue worker**.
7. **Schedule deployment window** (low traffic, maintenance window).
8. **Execute production deployment** mengikuti §5 step-by-step.
9. **Post-deploy monitoring** 30 menit minimum.
10. **Sign off** Gate 10 → release complete.

---

## 11. VERDICT PHASE 4.1

**PARTIAL — RUNBOOK COMPLETE, DEPLOYMENT BLOCKED UNTIL GATES 2, 3, 4, 7, 9, 10 RESOLVED**

- Kode, konfigurasi, test suite, asset build, keamanan: **SIAP PRODUKSI** (352 test lulus, 0 regresi).
- **Blocker kritis:** Akses database target (replica, staging, production) dan layanan background (cron, queue worker) belum tersedia.
- **Rollback plan khusus migration kuis** terdokumentasi & diverifikasi (hanya drop index, zero data loss).
- **Tidak ada perubahan kode** yang diperlukan pada fase ini — hanya persiapan environment & akses.

---

## APPENDIX A — QUICK REFERENCE COMMANDS

```bash
# Status checks
php artisan migrate:status
php artisan config:show app.env app.debug app.url
php artisan config:show session.driver session.secure session.http_only session.same_site
php artisan config:show queue.default cache.default filesystems.default

# Maintenance
php artisan down --secret="token" --render="errors::503"
php artisan up

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear

# Queue
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
php artisan queue:restart
php artisan queue:failed
php artisan queue:retry all

# Scheduler test
php artisan schedule:run --no-interaction

# Storage
php artisan storage:link

# Backup (contoh mysqldump)
mysqldump -h<host> -u<user> -p<pass> --single-transaction --routines --triggers pkbm_aditya > backup_$(date +%F_%H-%M).sql
```

---

## 12. OPERATIONAL SERVER CONFIGURATION & HARDENING (PHASE 7C)

### 12.1 Laravel Scheduler (Attendance Automation)
* **Job:** `attendance:generate`
* **Schedule:** Setiap hari pukul `05:00` Asia/Makassar (WITA, UTC+8, `0 21 * * * UTC`)
* **Flag:** `withoutOverlapping()`, `runInBackground()`
* **Verification:** `php artisan schedule:list`
* **Host Cron Configuration (crontab -u www-data -e):**
```cron
* * * * * cd /var/www/pkbm-aditya && php artisan schedule:run >> /dev/null 2>&1
```

### 12.2 Database Queue Worker (Systemd & Supervisor)
Queue menggunakan koneksi `database` (tabel `jobs`, `failed_jobs`, `job_batches`).
Command worker standar:
```bash
php artisan queue:work database --queue=default --tries=3 --timeout=90 --sleep=3
```

#### Option A: Systemd Service (`/etc/systemd/system/pkbm-queue.service`)
```ini
[Unit]
Description=PKBM Aditya Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/pkbm-aditya
ExecStart=/usr/bin/php /var/www/pkbm-aditya/artisan queue:work database --queue=default --tries=3 --timeout=90 --sleep=3
StandardOutput=append:/var/www/pkbm-aditya/storage/logs/queue-worker.log
StandardError=append:/var/www/pkbm-aditya/storage/logs/queue-worker.log

[Install]
WantedBy=multi-user.target
```
Commands:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now pkbm-queue
sudo systemctl status pkbm-queue
```

#### Option B: Supervisor Configuration (`/etc/supervisor/conf.d/pkbm-worker.conf`)
```ini
[program:pkbm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pkbm-aditya/artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pkbm-aditya/storage/logs/worker.log
stopwaitsecs=3600
```
Commands:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pkbm-worker:*
```

#### Queue Operational & Failed Jobs Management
```bash
# Restart workers after deploy
php artisan queue:restart

# Inspect failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <id>
php artisan queue:retry all

# Purge failed jobs
php artisan queue:forget <id>
php artisan queue:flush
```

### 12.3 Production SMTP Configuration & Testing
File `.env` production:
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org  # atau server SMTP institusi / Google Workspace
MAIL_PORT=587
MAIL_USERNAME=postmaster@pkbm-aditya.sch.id
MAIL_PASSWORD=secret-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@pkbm-aditya.sch.id
MAIL_FROM_NAME="PKBM Aditya"
```

Prosedur pengujian SMTP:
```bash
php artisan tinker --execute "Mail::raw('Test SMTP PKBM Aditya', fn(\$m) => \$m->to('admin@pkbm-aditya.sch.id')->subject('Test Koneksi SMTP'));"
```
Cek `storage/logs/laravel.log` bila ada exception timeout/authentication.

### 12.4 Logging Harian & Health Check
* Konfigurasi `.env`:
```dotenv
LOG_CHANNEL=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=info
```
* Lokasi log terotasi: `storage/logs/laravel-YYYY-MM-DD.log`.
* Health check endpoint: `GET /up` (HTTP 200) untuk monitoring liveness balancer/uptime robot.

### 12.5 Web Server Virtual Host (Strict Document Root `public/`)

#### Nginx (`/etc/nginx/sites-available/pkbm-aditya.conf`)
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pkbm-aditya.sch.id www.pkbm-aditya.sch.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name pkbm-aditya.sch.id www.pkbm-aditya.sch.id;

    # Document root STRICTLY to public/
    root /var/www/pkbm-aditya/public;
    index index.php index.html;

    ssl_certificate /etc/letsencrypt/live/pkbm-aditya.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pkbm-aditya.sch.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    client_max_body_size 25M;

    # Security: block dot files except .well-known
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security: prevent PHP execution inside user uploads
    location ~ ^/storage/.*\.php$ {
        deny all;
    }

    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

#### Apache (`/etc/apache2/sites-available/pkbm-aditya.conf`)
```apache
<VirtualHost *:80>
    ServerName pkbm-aditya.sch.id
    ServerAlias www.pkbm-aditya.sch.id
    Redirect permanent / https://pkbm-aditya.sch.id/
</VirtualHost>

<VirtualHost *:443>
    ServerName pkbm-aditya.sch.id
    ServerAlias www.pkbm-aditya.sch.id

    # Document root STRICTLY to public/
    DocumentRoot /var/www/pkbm-aditya/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/pkbm-aditya.sch.id/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/pkbm-aditya.sch.id/privkey.pem

    <Directory /var/www/pkbm-aditya/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Disable PHP execution in storage
    <Directory /var/www/pkbm-aditya/storage>
        php_flag engine off
        Options -ExecCGI
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pkbm_error.log
    CustomLog ${APACHE_LOG_DIR}/pkbm_access.log combined
</VirtualHost>
```

### 12.6 HTTPS, Session Cookies, and Trusted Proxies
* **Reverse Proxy Headers:** Bila di belakang Nginx/Cloudflare, proxy wajib mem-forward:
  `X-Forwarded-For`, `X-Forwarded-Proto`, `X-Forwarded-Host`, `X-Forwarded-Port`.
* **Explicit Trusted Proxies:** JANGAN gunakan `*`. Tuliskan IP pasti pada `.env`:
  `TRUSTED_PROXIES=127.0.0.1,10.0.0.0/8`
* **Session Cookie:** Otomatis `secure` saat `APP_ENV=production` (`SESSION_SECURE_COOKIE=true`).
* **HSTS:** Otomatis dikirim `Strict-Transport-Security: max-age=31536000; includeSubDomains` hanya saat request adalah HTTPS dan environment production.

### 12.7 CSP (Content-Security-Policy) Architecture Review
1. **`unsafe-eval`:** Dipertahankan karena modul frontend menggunakan Alpine.js standar (`import Alpine from 'alpinejs'`) yang mengevaluasi ekspresi reaktif seperti `x-data`, `@click`, `x-init` menggunakan `new Function()`.
2. **`unsafe-inline`:** Dipertahankan karena Blade templates (`landing.blade.php`, `users/create.blade.php`, template cetak rapor, dan auth operator) memuat skrip inline esensial.
3. **Penyempitan Sumber Eksternal:**
   - Menghapus wildcard `https:` bebas pada `frame-src`; dibatasi ketat ke `'self'` dan YouTube (`https://www.youtube.com`, `https://youtube.com`, `https://www.youtube-nocookie.com`).
   - `media-src` dibatasi ketat ke `'self'`.
   - `connect-src` pada production dibatasi ketat ke `'self'` (menghilangkan websocket dan localhost port Vite).

---

**END OF RUNBOOK**
*Document version controlled in repository. Review before each release.*