
# AUDIT

## Existing Files
- Model: `app/Models/Tutor.php` (Has `EncryptedAttribute` on nik, alamat, no_hp, email, bidang_keahlian).
- Migration: `database/migrations/2026_09_16_191452_create_tutors_table.php` (Uses `nomor_induk` (string), `nik` (text, encrypted), etc).
- Controller: `app/Http/Controllers/TutorController.php` (Exists, has standard CRUD).
- Requests: `StoreTutorRequest`, `UpdateTutorRequest` (Exist).
- Views: `resources/views/dashboards/tutor/` (index, create, edit, show exist).
- Routes: `routes/web.php` (Tutor routes registered as resource).

## Plan
1. Keep `TutorController` as is for CRUD.
2. Add `ImportController` or add to `OperatorController`. Given `OperatorController` handles `import-data` (BulkImport), it's probably best to follow that pattern.
3. Update `resources/views/components/sidebar.blade.php` to include "Impor Data Tutor".
4. Create `resources/views/operator/import-tutor/index.blade.php` (Follow Operator UI layout).
5. Add routes to `routes/web.php`.
6. Run `npm run build` and test.

## Notes
- Encryption uses `EncryptedAttribute` (AES-256-GCM). Must use Eloquent.
- Migration `alter_students_and_tutors_for_encryption` confirmed `text` type for encrypted fields.
- `Nomor Induk` generation: Prompt says "jika existing implementation membuat nomor otomatis, pertahankan".
- In `BulkImport.php`: `nomor_induk` is generated as `'TUTOR-'.strtoupper(uniqid())`. I will mimic this logic or create a similar helper if not already exists (there doesn't seem to be an explicit helper).
- Permission: `operator.import` already exists.

# PROGRESS
- Audited structure.
- Prepared plan.
