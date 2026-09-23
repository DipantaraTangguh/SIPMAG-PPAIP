# Desain siklus magang wajib dan non-wajib
SIPMAG supports magang **wajib** (once per student, full flow to sidang) and **non_wajib** (unlimited; never reaches DPM/logbook/sidang). BOTH non-wajib paths (Form 2 approval AND mitra application) reach confirmation: the Form 2 path lands on `AwaitingConfirmation`, while the mitra path self-reports straight from `HasApplication` → student MUST confirm outcome via `POST /api/student/cycle/confirm` (hasil=diterima requires LoA upload + actual company/period → `ElectiveCompleted` + history written FROM confirmation data incl. `loa_path`; hasil=ditolak → back to `ApprovedForm1` to retry). User explicitly wants LoA required on the mitra path too — no direct-completion shortcut. Type lives in `form1_data['jenisMagang']` (NOT a column; distinct axis from `is_independent`). Branch points: `Form2Controller::approve` (Form 2 path, works from ApprovedForm1 or HasApplication) and `StudentCycleController::confirm` (mitra path). `SupervisorController::store` hard-blocks non_wajib from the DPM stage; GuidancePage hides the bimbingan UI for them. History = append-only `internship_cycles` table (snapshot of student + placement + period + score); written by `InternshipCycleSnapshotService` at both completion points. Wajib-once rule enforced via `Student::getHasCompletedWajibAttribute` (exists-query on cycles; exposed only in Form1Resource to avoid N+1 in list endpoints). Self-service reset: `POST /api/student/cycle/reset` (`InternshipCycleResetService`) soft-deletes current-cycle children, clears students' mutable cycle fields, transitions to `Unverified`; allowed only from `CycleCompleted`/`ElectiveCompleted` (StudentPolicy::resetCycle).

**Kenapa:** PPAIP wants all internship kinds accommodated; user chose "light" architecture (no cycle_id FK on child tables) as fitting a thesis project; per-type reset points confirmed 2026-07-06.

**Cara memakainya:** enum changes must be made in BOTH `2025_01_01_000002_create_students_table.php` (SQLite tests rebuild schema) AND a MySQL-only `DB::statement` alter migration (prod DB already migrated). `sidang_submissions` unique-per-student constraint is safe only because wajib is once-ever — revisit if that rule changes. In feature tests, refresh the acting `User` instance between requests (cached `student` relation goes stale after status transitions). User wants a plan approved via plan mode BEFORE implementation on multi-file features.

## Koreksi 2026-09-23

Catatan ini ditulis Juli 2026 dan sempat usang di dua tempat; keduanya sudah
diperbaiki di atas:

- **Nama state.** `MenungguKonfirmasi`, `SelesaiNonWajib`, dan `SiklusSelesai`
  diseragamkan ke bahasa Inggris menjadi `AwaitingConfirmation`,
  `ElectiveCompleted`, dan `CycleCompleted`.
- **Jalur mitra.** `ApplicationObserver::completeNonWajibCycle` sudah dihapus.
  Kolom `applications.status` ternyata tidak pernah beranjak dari `Applied` --
  tidak ada satu pun jalur kode yang menulis `Accepted`, karena lamaran mitra
  memang tidak menentukan mahasiswa diterima di mana. Penerimaan dicatat
  mahasiswa sendiri lewat `StudentCycleController::confirm`, yang menerima
  laporan langsung dari state `HasApplication`.
