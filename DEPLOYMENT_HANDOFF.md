# Deployment Handoff — Sistem Informasi PKBM Aditya

**Version:** 1.0
**Date:** 2026-10-21
**Prepared by:** Release Engineer (Phase 4.2)
**For:** DBA, DevOps/Infra, Release Owner
**Repository:** `/media/Data D/pkbm aditya`
**Branch:** `main` (tracking `origin/main`)
**Related Documents:** `DEPLOYMENT_RUNBOOK.md`, Phase 2.10–2.11, 3, 4, 4.1 reports

---

## A. EXECUTIVE SUMMARY

| Item | Status |
|------|--------|
| **Automated Regression Tests** | **352 passed, 0 failed, 1.081 assertions** (local execution, `php artisan test --compact`) |
| **Asset Build** | **Success** — Vite 8.3.0, manifest valid, 2 entrypoints (`app.css`, `app.js`) |
| **PHPStan (changed files)** | **0 errors** on files modified in Phase 3–4 |
| **Git Diff Check** | **Clean** — no whitespace/formatting issues |
| **Migration Pending** | **1** — `2026_10_20_174927_add_unique_quiz_attempt_index` (unique index on `quiz_attempts.quiz_id + student_id`) |
| **Production Readiness** | **NOT DECLARED** — Application passes local quality gates but **deployment blocked** by infrastructure & data-access prerequisites |

> **Critical:** This document reflects **local verification only**. The application has **not** been tested on the target database engine (MySQL/MariaDB), staging environment, or production infrastructure. Do **not** treat this as a production-readiness sign-off.

---

## B. BLOCKER & ACTION OWNER MATRIX

| ID | Blocker | Work Required | Why It Matters | Action Owner | Access / Environment Needed | Evidence to Return | Pass Criteria | Current Status |
|----|---------|---------------|----------------|--------------|----------------------------|--------------------|---------------|----------------|
| **B-01** | Read-only source for quiz attempt duplicate preflight | Run `SELECT quiz_id, student_id, COUNT(*) AS total FROM quiz_attempts GROUP BY quiz_id, student_id HAVING COUNT(*) > 1;` on approved replica/snapshot | Migration `2026_10_20_174927_add_unique_quiz_attempt_index` **will fail** if duplicates exist. Guard in migration throws `RuntimeException`. | **DBA** | Production replica (read-only) **or** approved snapshot export | CSV output of query + DBA sign-off | **0 rows returned** (no duplicates) | ❌ **BLOCKED** — no replica access |
| **B-02** | Disposable database matching target engine & version | Provision MySQL/MariaDB instance identical to production (version, charset, collation, sql_mode) | SQLite in-memory tests **do not prove** InnoDB unique-constraint behaviour under concurrency. | **DevOps / Infra** | Fresh DB server/container with production-equivalent config | Connection string + `SELECT VERSION(); SHOW VARIABLES LIKE 'sql_mode';` | Engine/version match confirmed | ❌ **BLOCKED** — not provisioned |
| **B-03** | Migration rehearsal on target engine | Execute `php artisan migrate --force` → verify index exists → `php artisan migrate:rollback --step=1 --force` → verify index gone → re-apply | Confirms migration `up()`/`down()` work on real engine, no data loss, no lock escalation. | **DevOps + DBA** | Disposable DB from B-02 + app code at release tag | Migration logs, `SHOW INDEX` output, rollback logs | Index created, dropped, recreated cleanly; 0 data loss | ❌ **BLOCKED** — needs B-02 |
| **B-04** | Concurrency test on target engine | Simulate N=10 parallel `POST /quiz-attempts/start` for same `(quiz_id, student_id)` with valid auth+CSRF. Verify exactly 1 attempt created, others get 409/422 gracefully, 0 deadlocks, p95 < 2s. | Proves unique constraint + application handling works under real InnoDB row-locking. | **DevOps + QA** | Disposable DB (B-02) + test harness (k6/vegeta/artillery) with auth cookies | Load-test report, DB verification query `COUNT(*) = 1` | All success criteria met (see Runbook §4.3) | ❌ **BLOCKED** — needs B-02 |
| **B-05** | Backup & restore drill | Perform full DB backup (mysqldump/Percona/snapshot) + storage backup → restore to disposable → verify data integrity & app boots | Confirms RPO/RTO, backup tooling, and that rollback procedure (§6.2 Runbook) actually works. | **DBA + DevOps** | Production DB (read access for backup) + disposable restore target | Backup checksums, restore time, post-restore smoke test | Restore completes < RTO, app passes health checks | ⚠️ **WARNING** — procedure documented only, not drilled |
| **B-06** | Production configuration & secret management | Prepare `.env.production` with: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`, `AES_256_GCM_KEY` (32 bytes), SMTP credentials, queue/session/cache drivers. Secrets injected via vault/CI, **never** committed. | Prevents debug leakage, ensures encryption, HTTPS cookies, email delivery, queue processing. | **Release Owner + DevOps** | Secret manager / vault access; production `.env` template | Redacted config audit (no secrets printed) | All required vars set, secrets managed externally | ⚠️ **WARNING** — template only |
| **B-07** | Scheduler & queue worker on deployment host | Install cron entry `* * * * * cd /path/app && php artisan schedule:run` + systemd unit for `php artisan queue:work --sleep=3 --tries=3 --timeout=90`. Verify active after deploy. | Daily attendance generation & async jobs (exports, notifications) require active scheduler & workers. | **DevOps / Infra** | SSH + sudo on production host | `crontab -l`, `systemctl status queue-worker`, scheduler log entries | Both services running, logging to `storage/logs/laravel.log` | ❌ **BLOCKED** — no host access |
| **B-08** | Browser/UI verification decision | Decide if Playwright/Dusk tests are mandatory for release. If yes, add to repo & CI; if no, document risk acceptance. | Current repo has **no browser automation**. Visual regressions, JS errors, mobile layout issues undetected. | **Release Owner + QA Lead** | CI runner with browsers (if required) | Decision record + test report (if implemented) | Explicit go/no-go with rationale | ❌ **BLOCKED** — no decision |
| **B-09** | Release Owner final approval | Sign off all gates G1–G10 in Runbook (§8) including: preflight data, migration rehearsal, concurrency, backup, config, scheduler/queue, acceptance test, rollback plan, maintenance window. | Formal accountability; ensures no gate skipped under pressure. | **Release Owner** | Access to Runbook, test reports, DBA/DevOps evidence | Signed approval (PDF/ticket) | All gates ✅ PASS or accepted ⚠️ WARNING with documented risk | ❌ **BLOCKED** — awaiting evidence |

---

## C. SAFE INSTRUCTIONS FOR DBA

### C.1 Duplicate Detection Queries (READ-ONLY)
```sql
-- RUN ONLY ON APPROVED REPLICA / SNAPSHOT / EXPORT
-- DO NOT RUN ON PRODUCTION PRIMARY
-- DO NOT INCLUDE PII (NIK, name, answers, scores) IN OUTPUT

-- 1. Check students.user_id duplicates (non-NULL only)
SELECT
    `user_id`,
    COUNT(*) AS `total_students`
FROM `students`
WHERE `user_id` IS NOT NULL
GROUP BY `user_id`
HAVING COUNT(*) > 1
ORDER BY `total_students` DESC;

-- 2. Check tutors.user_id duplicates (non-NULL only)
SELECT
    `user_id`,
    COUNT(*) AS `total_tutors`
FROM `tutors`
WHERE `user_id` IS NOT NULL
GROUP BY `user_id`
HAVING COUNT(*) > 1
ORDER BY `total_tutors` DESC;

-- 3. Check quiz_attempts duplicates (composite quiz_id + student_id)
SELECT
    `quiz_id`,
    `student_id`,
    COUNT(*) AS `total_attempts`
FROM `quiz_attempts`
GROUP BY `quiz_id`, `student_id`
HAVING COUNT(*) > 1
ORDER BY `total_attempts` DESC;
```

**Output handling:** Export to CSV. Share **only** `quiz_id`, `student_id`, `total_attempts` with Release Engineer. **Never** include student names, NIK, answers, timestamps, or scores.

### C.2 If Duplicates Found — Mandatory Steps
1. **STOP** — do not proceed with migration.
2. **Do NOT auto-delete** any attempt rows.
3. **Engage Data Owner** (Kepala PKBM / Operator Senior) to decide reconciliation:
   - Which attempt is canonical? (first started? highest score? completed? manual review?)
   - Archive non-canonical attempts: move to `quiz_attempts_archive` (same schema) **or** update `status='archived'`.
   - Preserve **all** answers, scores, timestamps, metadata for audit trail.
4. **Document Decision** per duplicate set:
   ```
   quiz_id | student_id | kept_attempt_id | archived_attempt_ids | decided_by | decided_at | ticket_ref
   ```
5. **Execute Reconciliation on Staging Disposable First** (B-02), re-run query → must return 0 rows.
6. **Only after approval & staging verified** → apply to production replica.
7. **Re-run query on production replica** → confirm 0 rows before Gate 2 PASS.

> **Reminder:** The migration `up()` method **will abort with a clear exception** if duplicates exist. This is a safety guard — not a bug.

---

## D. SAFE INSTRUCTIONS FOR DEVOPS / INFRA

### D.1 Disposable Database Requirements (B-02)
| Requirement | Value |
|-------------|-------|
| Engine | MySQL 8.0+ **or** MariaDB 10.6+ (match production exactly) |
| Charset / Collation | `utf8mb4` / `utf8mb4_unicode_ci` |
| SQL Mode | `STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION` (match production) |
| Isolation | Dedicated instance — **no shared data** |
| Network | Accessible from CI / staging app server |
| Lifecycle | Destroy after rehearsal complete |

### D.2 Migration Rehearsal Steps (READ-WRITE — STAGING ONLY)
```bash
# 1. Deploy release code to staging
git fetch && git checkout <release-tag>
composer install --no-dev --optimize-autoloader

# 2. Point .env to disposable DB
# 3. Run migration
php artisan migrate --force
# VERIFY: exit 0, migration listed as Ran

# 4. Verify index
mysql -e "SHOW INDEX FROM quiz_attempts WHERE Key_name = 'quiz_attempts_quiz_id_student_id_unique';"
# EXPECT: Non_unique = 0, Column_name = quiz_id,student_id

# 5. Test duplicate rejection
mysql -e "INSERT IGNORE INTO quiz_attempts (quiz_id, student_id, ...) VALUES (1, 1, ...);"
mysql -e "INSERT IGNORE INTO quiz_attempts (quiz_id, student_id, ...) VALUES (1, 1, ...);"
# EXPECT: second insert affected rows = 0

# 6. Rollback (drop index only)
php artisan migrate:rollback --step=1 --force
# VERIFY: index gone, data rows unchanged

# 7. Re-apply
php artisan migrate --force
# VERIFY: index back
```

**All commands above are READ-WRITE on the disposable database only.**

### D.3 Concurrency Test (READ-WRITE — STAGING ONLY)
- Use **k6 / vegeta / artillery** with real Laravel session + CSRF.
- Target: `POST /quiz-attempts/start` with valid `quiz_id` + authenticated `student_id`.
- Concurrency: 10 parallel requests, **simultaneous start** (not sequential).
- Capture: HTTP codes, response bodies, DB row count, InnoDB `innodb_status` for deadlocks.
- **Success = exactly 1 row in `quiz_attempts` for that pair.**

### D.4 Backup & Restore Drill (READ-WRITE — APPROVED ENV ONLY)
```bash
# Backup (production primary or replica)
mysqldump -h<prod> -u<user> -p --single-transaction --routines --triggers pkbm_aditya > backup_$(date +%F_%H-%M).sql
rsync -a /path/to/storage/app/ /backup/storage_$(date +%F_%H-%M)/

# Restore (disposable target)
mysql -h<disposable> -u<user> -p pkbm_aditya < backup_*.sql
rsync -a /backup/storage_*/ /path/to/storage/app/

# Verify
php artisan migrate:status
curl -f http://disposable-app/up
```

**Tag each step clearly: [READ-ONLY] or [READ-WRITE — STAGING/DISPOSABLE ONLY].**

### D.5 Production Host Configuration (READ-WRITE — PRODUCTION HOST)
| Service | Command / Config | Verification |
|---------|------------------|--------------|
| Cron scheduler | `* * * * * cd /var/www/pkbm && php artisan schedule:run >> /dev/null 2>&1` | `crontab -l` |
| Queue worker (systemd) | Unit file `queue-worker.service` with `ExecStart=php artisan queue:work --sleep=3 --tries=3 --timeout=90` | `systemctl status queue-worker` |
| Supervisor alternative | `[program:queue-worker] command=php artisan queue:work ... numprocs=2` | `supervisorctl status` |
| HTTPS / Reverse Proxy | Nginx/Apache with TLS 1.2+, HSTS, CSP, X-Frame-Options | `curl -I https://domain` |
| Log rotation | `logrotate` daily, keep 14 days, compress | `ls -la /var/www/pkbm/storage/logs/` |

---

## E. RELEASE OWNER APPROVAL CHECKLIST

| # | Gate | Description | Evidence Required | Owner Decision | Date / Signature |
|---|------|-------------|-------------------|----------------|------------------|
| 1 | **G2** | Historical duplicate preflight complete | Query CSV (0 rows) + DBA sign-off | ☐ APPROVE / ☐ REJECT |  |
| 2 | **G3** | Migration rehearsal passed on target engine | Migration logs, `SHOW INDEX`, rollback logs | ☐ APPROVE / ☐ REJECT |  |
| 3 | **G4** | Concurrency rehearsal passed | Load-test report, DB verification `COUNT(*)=1` | ☐ APPROVE / ☐ REJECT |  |
| 4 | **G5** | Backup & restore drill successful | Backup checksums, restore time, post-restore smoke test | ☐ APPROVE / ☐ REJECT |  |
| 5 | **G6** | Production configuration ready | Redacted `.env` audit, secrets in vault | ☐ APPROVE / ☐ REJECT |  |
| 6 | **G7** | Scheduler & queue verified on host | `crontab -l`, `systemctl status`, log entries | ☐ APPROVE / ☐ REJECT |  |
| 7 | **G8** | Browser/UI verification decision recorded | Decision doc + test report (if required) | ☐ APPROVE / ☐ REJECT |  |
| 8 | **G9** | Acceptance test plan for post-deploy | Smoke test checklist (Runbook §5.4) | ☐ APPROVE / ☐ REJECT |  |
| 9 | **G10** | Rollback plan reviewed & accepted | Runbook §6.2 + §6.3 specific rollback | ☐ APPROVE / ☐ REJECT |  |
| 10 | **Go-Live** | Maintenance window scheduled, stakeholders notified | Calendar invite, communication log | ☐ GO / ☐ NO-GO |  |

> **No gate may be skipped.** If any gate is REJECTED, deployment is halted until the blocker is resolved and evidence re-submitted.

---

## F. CONSISTENCY WITH DEPLOYMENT_RUNBOOK.MD

| Section | Runbook Reference | Handoff Alignment |
|---------|-------------------|-------------------|
| Pre-deployment checks | Runbook §1 | Blockers B-01..B-09 map to Gates G1..G10 |
| Data integrity preflight | Runbook §2 | DBA instructions §C identical query & reconciliation |
| Migration rehearsal | Runbook §3 | DevOps instructions §D.2 identical steps |
| Concurrency rehearsal | Runbook §4 | DevOps instructions §D.3 identical criteria |
| Production deploy | Runbook §5 | Host config §D.5 matches Runbook §5.1–5.5 |
| Rollback plan | Runbook §6 | Specific quiz-index rollback §6.3 mirrored in B-03 |
| Post-deploy ops | Runbook §7 | Scheduler/queue verification §B-07, §D.5 |
| Release gates | Runbook §8 | Approval checklist §E maps 1:1 |

---

## G. FINAL STATE & NEXT ACTIONS

| Artifact | Location | Status |
|----------|----------|--------|
| `DEPLOYMENT_RUNBOOK.md` | Repository root | ✅ Created (Phase 4.1) |
| `DEPLOYMENT_HANDOFF.md` | Repository root | ✅ Created (this document, Phase 4.2) |
| Test suite baseline | `php artisan test --compact` | ✅ 352 passed |
| Asset build baseline | `npm run build` | ✅ Success |
| Git workspace | `git status` | ✅ Clean (1 new untracked handoff file) |

**No code changes made in Phase 4.2.** Only documentation added.

### Immediate Next Steps for Infrastructure Team
1. **DBA** → Provide read-only replica/snapshot access → execute §C.1 query → return CSV + sign-off (unblocks B-01).
2. **DevOps** → Provision disposable MySQL matching production (B-02) → notify Release Engineer.
3. **DevOps + QA** → Run migration rehearsal (B-03) & concurrency test (B-04) on disposable DB.
4. **DBA + DevOps** → Schedule backup/restore drill (B-05).
5. **Release Owner + DevOps** → Prepare production `.env` & secrets (B-06), configure cron + queue worker (B-07).
6. **Release Owner + QA Lead** → Decide on browser automation requirement (B-08).
7. **Release Owner** → Review all evidence → sign Gates G1–G10 (B-09).

---

## H. VERDICT PHASE 4.2

**HANDOFF COMPLETE — DEPLOYMENT STILL BLOCKED ON 9 ITEMS (B-01..B-09)**

- All local quality gates **PASS** (tests, build, lint, static analysis).
- Runbook & Handoff documents **created and consistent**.
- **Zero production-risk actions taken** — no migration, no data access, no secrets exposed.
- **Infrastructure & data-access prerequisites** now explicitly tracked with owners, evidence requirements, and pass criteria.
- Release Owner has a **formal approval checklist** to authorize go-live only when every gate is satisfied.

---

**END OF HANDOFF DOCUMENT**
*Version controlled in repository. Distribute to DBA, DevOps, Release Owner.*