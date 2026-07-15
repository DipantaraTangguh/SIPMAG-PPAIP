# SIPMAG UBakrie — Dokumentasi Database

> Skema di dokumen ini diambil langsung dari `SHOW CREATE TABLE` database MySQL/MariaDB
> `sipmag` (2026-07-07) — bukan dari migration saja, jadi ini kondisi aktual.
> Baca bersama [DOCS-CODEBASE.md](DOCS-CODEBASE.md) §3 (alur bisnis) untuk konteks.

---

## 1. Ringkasan & Konvensi

- **Engine:** InnoDB, charset `utf8mb4_unicode_ci`. Dev/prod: MariaDB; test: SQLite in-memory.
- **Primary key:** semua tabel domain memakai `id BIGINT UNSIGNED AUTO_INCREMENT` (konvensi Laravel).
- **Foreign key tanpa klausa ON DELETE = RESTRICT** (default InnoDB): baris induk tidak bisa dihapus permanen selama punya anak. Ini disengaja — penghapusan "logis" memakai **soft delete** (`deleted_at`), bukan DELETE fisik.
- **Timestamps:** `created_at`/`updated_at` di semua tabel domain; `submitted_at` terpisah di tabel pengajuan (default `CURRENT_TIMESTAMP`).
- 16 tabel total = **10 tabel domain** + 6 tabel bawaan framework (`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `migrations`, `password_reset_tokens`, `personal_access_tokens` — sesi login, antrean, cache; tidak dibahas detail).

### Tabel domain

| Tabel | Peran satu kalimat |
|---|---|
| `users` | Akun login semua role |
| `students` | Profil mahasiswa + **state siklus magang aktif** |
| `lecturers` | Data dosen (kaprodi/DPM/penguji) |
| `internships` | Lowongan magang mitra PPAIP |
| `applications` | Lamaran CV mahasiswa ke lowongan |
| `form2_submissions` | Pengajuan surat pengantar (jalur mandiri) |
| `supervisor_applications` | Pengajuan dosen pembimbing + bukti LoA |
| `logbooks` | Log aktivitas mingguan |
| `sidang_submissions` | Dokumen & jadwal sidang |
| `defense_assessments` | Nilai sidang per penilai |
| `internship_cycles` | **Riwayat permanen** siklus magang yang selesai |

---

## 2. Diagram Relasi (ERD)

```
                            ┌──────────┐
                            │  users   │  PK: id
                            │ (role)   │  UQ: email
                            └────┬─────┘
              1:1 (nullable) ┌───┴────────────┐ 1:1
             SET NULL        │                │ RESTRICT
                      ┌──────▼─────┐    ┌─────▼──────┐
                      │ lecturers  │    │  students  │ PK: id
                      │ UQ: nidn   │    │ UQ: nim    │
                      └──┬───┬───┬─┘    └─┬──┬──┬──┬─┘
                         │   │   │        │  │  │  │
      students.dpm_id ◄──┘   │   │        │  │  │  │   (semua FK anak → students.id
students.form1_approved_by ◄─┘   │        │  │  │  │    = RESTRICT, tanpa cascade)
                                 │        │  │  │  │
   sidang_submissions.penguji1/ ◄┘   1:N  │  │  │  │ 1:N
   penguji2/scheduled_by (SET NULL)       │  │  │  │
                                          │  │  │  │
        ┌─────────────────┬───────────────┘  │  │  └──────────────────┐
        │                 │                   │  │                     │
  ┌─────▼──────┐   ┌──────▼───────────┐  ┌───▼──▼────┐        ┌───────▼─────────┐
  │applications│   │form2_submissions │  │ logbooks  │        │internship_cycles│
  │UQ:(student,│   │  (status enum)   │  │UQ:(student│        │ UQ:(student,    │
  │ internship)│   └──────────────────┘  │  ,tanggal)│        │  cycle_number)  │
  └─────┬──────┘                         └───────────┘        │ ← APPEND-ONLY   │
        │ N:1 (nullable, SET NULL)                            └─────────────────┘
  ┌─────▼──────┐        ┌────────────────────────┐   ┌─────────────────────┐
  │internships │        │supervisor_applications │   │ sidang_submissions  │
  └────────────┘        │ UQ: student_id (1:1)   │   │ UQ: student_id (1:1)│
                        └────────────────────────┘   └─────────┬───────────┘
                                                               │ 1:3
                                                     ┌─────────▼───────────┐
                                                     │ defense_assessments │
                                                     │ UQ:(submission,role)│
                                                     │ FK lecturer_id      │
                                                     └─────────────────────┘
```

Kardinalitas ringkas dari sudut `students`:

| Relasi | Kardinalitas | Ditegakkan oleh |
|---|---|---|
| users → students | 1 : 0..1 | FK `user_id` (aplikasi menjaga 1:1; tak ada unique di DB) |
| users → lecturers | 1 : 0..1 | FK `user_id` nullable |
| students → applications | 1 : N | FK; unique (student, internship) cegah lamar ganda ke lowongan sama |
| students → form2_submissions | 1 : N | FK (boleh berkali-kali, mis. setelah ditolak) |
| students → supervisor_applications | 1 : **1** | UNIQUE `student_id` |
| students → logbooks | 1 : N | FK; unique (student, tanggal) = satu entri per hari |
| students → sidang_submissions | 1 : **1** | UNIQUE `student_id` |
| sidang_submissions → defense_assessments | 1 : ≤3 | UNIQUE (submission, assessor_role); role enum 3 nilai |
| students → internship_cycles | 1 : N | FK; unique (student, cycle_number) |
| internships → applications | 1 : N | FK nullable, ON DELETE SET NULL |
| lecturers → students (sebagai DPM) | 1 : N | FK `dpm_id` SET NULL |

---

## 3. Detail per Tabel

> Format: kolom penting saja; semua tabel punya `id` PK + timestamps kecuali disebut.
> 🗑 = punya soft delete (`deleted_at`).

### 3.1 `users` 🗑
| Kolom | Tipe | Catatan |
|---|---|---|
| `name`, `email` | varchar | `email` **UNIQUE** |
| `password` | varchar | NOT NULL (hashed bcrypt) |
| `role` | enum(`mahasiswa`,`kaprodi`,`dpm`,`ppaip`,`dosen_penguji`) | default mahasiswa |
| `failed_login_attempts`, `locked_until`, `last_failed_login_at`, `last_login_at` | — | lockout brute-force; `locked_until` ber-INDEX |

### 3.2 `students` 🗑 — tabel paling penting
| Kolom | Tipe | Catatan |
|---|---|---|
| `user_id` | FK → users **RESTRICT** | pemilik akun |
| `dpm_id` | FK → lecturers, nullable, **SET NULL** | pembimbing siklus aktif; di-null saat siklus selesai/reset |
| `nim` | varchar **UNIQUE** | identitas natural mahasiswa — kunci pemetaan paling stabil |
| `name`, `study_program`, `email` | varchar | ⚠️ duplikat dari `users` (lihat §6 normalisasi) |
| `semester` | tinyint unsigned, nullable | data akademik otoritatif (diisi admin) |
| `tahun_akademik` | varchar, nullable | ⚠️ **kolom mati** — model menimpanya dengan accessor yang menghitung dari tanggal sekarang |
| `jumlah_sks` | smallint unsigned, nullable | |
| `ipk` | decimal(3,2), nullable | maks 9.99 |
| `access_status` | enum **10 nilai** (Unverified … SiklusSelesai, SelesaiNonWajib) | **state machine siklus aktif**; hanya diubah via `StudentStateMachine` |
| `is_independent` | tinyint(1) default 0 | penanda jalur mandiri (Form 2), BUKAN wajib/non-wajib |
| `form1_data` | longtext + CHECK `json_valid` | JSON snapshot isian Form 1: `{semester, jumlahSKS, ipk, jenisMagang, skemaMagang, topikMagang, outputTarget}` |
| `form1_pdf_path`, `form1_rejection_reason` | varchar, nullable | |
| `form1_approved_by` | FK → lecturers, nullable, **SET NULL** | kaprodi yang menyetujui |
| `form1_approved_at` | timestamp, nullable | |

Index tambahan: **(study_program, access_status)** — komposit untuk dashboard Kaprodi
(filter prodi + status sekaligus; prefix `study_program` juga melayani filter prodi saja).

> Kolom `approved_logbook_count` pernah ada di migration awal, **di-drop** oleh
> `2026_06_07_000003_enforce_logbook_date_integrity` — diganti hitungan agregat
> real-time dari `logbooks` (accessor model + `withCount` di Filament).

### 3.3 `lecturers` (tanpa soft delete)
| Kolom | Catatan |
|---|---|
| `user_id` | FK → users, nullable, **SET NULL** — dosen bisa ada tanpa akun login |
| `nidn` | **UNIQUE** — identitas natural dosen |
| `lecturer_name`, `contact`, `study_program`, `signature_path` | `study_program` = scope kaprodi |

Satu baris `lecturers` bisa berperan banyak: kaprodi (via `students.form1_approved_by`),
DPM (via `students.dpm_id` + `defense_assessments`), penguji (via `sidang_submissions.dosen_penguji_1/2_id`).
Perannya ditentukan `users.role`, bukan kolom di tabel ini.

### 3.4 `internships` (tanpa soft delete, tanpa FK keluar)
Lowongan mitra: `company_name`, `position`, `description` (NOT NULL); `capacity`,
`duration`, `bidang`, `start_date`, `minimum_education`, `sistem_kerja`, `location`
nullable; `deadline` date NOT NULL; `is_active` bool. Tiga kolom JSON ber-CHECK
`json_valid`: `job_description`, `skills`, `requirements` (array string untuk UI).
⚠️ Tidak ada tanggal selesai — hanya `duration` string bebas.

### 3.5 `applications` 🗑
| Kolom | Catatan |
|---|---|
| `student_id` | FK → students **RESTRICT** |
| `internship_id` | FK → internships, nullable, **SET NULL** — lamaran tetap hidup walau lowongan dihapus |
| `cv_file_path`, `loa_path` | nullable |
| `status` | enum(`Applied`,`Accepted`,`RejectedByCompany`,`Canceled`) |

Unique **(student_id, internship_id)** — tak bisa melamar lowongan sama dua kali.
Index **(student_id, status)** — query "lamaran aktif mahasiswa X".
Trigger aplikasi: saat satu lamaran → `Accepted`, `ApplicationObserver` membatalkan
lamaran `Applied` lainnya + menyelesaikan siklus non-wajib.

### 3.6 `form2_submissions` 🗑
`student_id` FK RESTRICT; `company_name` NOT NULL, `nama_pimpinan`/`jabatan_pimpinan`
nullable, `alamat_perusahaan`/`lingkup_magang` text NOT NULL, `tanggal_mulai/selesai`
date NOT NULL (input UI granularitas bulan, disimpan tanggal 1), `status`
enum(`PendingReview`,`ApprovedForm2`,`RejectedForm2`), `rejection_reason`, `pdf_path`,
`submitted_at` default now. Tanpa unique per student → mahasiswa boleh mengajukan ulang.

### 3.7 `supervisor_applications` 🗑 — 1:1 per mahasiswa
`student_id` FK RESTRICT + **UNIQUE**. `company_name`, `company_contact`, `loa_path`
NOT NULL (bukti diterima!); `nama_praktisi`, `jabatan_praktisi`, `no_telepon`, `email`,
`mulai_magang`, `selesai_magang` nullable (ditambah belakangan via ALTER).

### 3.8 `logbooks` 🗑
`student_id` FK RESTRICT; `tanggal` date; `kegiatan_harian`, `hasil` text;
`status` enum(`PendingReview`,`Approved`,`Rejected`); `dpm_note` varchar.
Unique **(student_id, tanggal)** — satu entri per tanggal.
Index **(student_id, status)** — hitung cepat "6 logbook approved".

### 3.9 `sidang_submissions` 🗑 — 1:1 per mahasiswa
| Kolom | Catatan |
|---|---|
| `student_id` | FK RESTRICT + **UNIQUE** ⚠️ unique ini mencakup baris soft-deleted — aman hanya karena aturan "wajib maksimal 1×" |
| `laporan_path`, `poster_path` | NOT NULL; `foto_kegiatan_1/2_path` nullable |
| `status` | enum(`Pending`,`Scheduled`) |
| `scheduled_date/time`, `room` | nullable, diisi kaprodi |
| `dosen_penguji_1_id`, `dosen_penguji_2_id`, `scheduled_by` | 3× FK → lecturers, nullable, **SET NULL** |

### 3.10 `defense_assessments` (TANPA soft delete)
`defense_submission_id` FK → sidang_submissions RESTRICT; `lecturer_id` FK → lecturers
RESTRICT; `assessor_role` enum(`dpm`,`penguji_1`,`penguji_2`); tiga skor decimal(5,2)
NOT NULL. Unique **(defense_submission_id, assessor_role)** = maksimal 3 nilai per
sidang, satu per peran — upsert idempoten. Index (lecturer_id, submission) untuk
lookup "nilai yang sudah kuberikan".

### 3.11 `internship_cycles` — riwayat permanen (TANPA soft delete, append-only)
| Kelompok | Kolom |
|---|---|
| Kunci | `student_id` FK RESTRICT; `cycle_number` int; UNIQUE **(student_id, cycle_number)** |
| Klasifikasi | `jenis_magang` enum(`wajib`,`non_wajib`); `outcome_status` enum(`SiklusSelesai`,`SelesaiNonWajib`) |
| Snapshot mahasiswa | `nim`, `nama`, `study_program` (NOT NULL); `semester`, `ipk` nullable |
| Snapshot Form 1 | `skema_magang`, `topik_magang`, `output_target` |
| Snapshot tempat | `company_name`, `alamat_perusahaan`, `nama_pimpinan`, `tanggal_mulai`, `tanggal_selesai` |
| Snapshot nilai | `final_score` decimal(5,2), `letter_grade` (hanya wajib) |
| Waktu | `started_at` (= form1_approved_at), `completed_at` |

Satu-satunya tabel yang **tidak pernah** di-update/delete oleh alur normal. Kueri
penegak aturan "wajib sekali": `WHERE student_id = ? AND jenis_magang = 'wajib' LIMIT 1`
— dilayani prefix `student_id` dari unique index.

---

## 4. Perilaku Penghapusan & Strategi Arsip

Dua lapis, jangan tertukar:

1. **Soft delete (`deleted_at`)** di `users`, `students`, `applications`,
   `form2_submissions`, `supervisor_applications`, `logbooks`, `sidang_submissions`.
   Query Eloquent otomatis mengecualikan baris terhapus (`withTrashed()` untuk melihatnya).
   Dipakai reset siklus: child record siklus lama di-soft-delete = "arsip".
2. **FK fisik**: RESTRICT hampir di semua relasi anak→induk (tak bisa hapus fisik
   induk yang punya anak); SET NULL untuk relasi "penunjukan" (`dpm_id`,
   `form1_approved_by`, penguji, `internship_id`) — kalau penunjuknya hilang,
   catatan anak tetap hidup tanpa referensi.

Konsekuensi penting:
- **Unique index tetap menghitung baris soft-deleted** (MySQL tidak kenal partial
  index di sini). `sidang_submissions.student_id` unique → mahasiswa tak bisa punya
  sidang kedua bahkan setelah reset — tidak masalah selama wajib hanya 1×.
- `defense_assessments` & `internship_cycles` sengaja tanpa soft delete: nilai dan
  riwayat tidak boleh "dihapus logis".

---

## 5. Join yang Benar-Benar Dipakai Aplikasi

Tidak ada raw JOIN SQL; semua lewat relasi Eloquent (di balik layar tetap
JOIN/subquery). Pola nyatanya:

| Kebutuhan | Kode | SQL efektif |
|---|---|---|
| PPAIP lihat daftar Form 2 + identitas mahasiswa | `Form2Submission::with('student:id,nim,name,study_program')` | 2 query: form2 + `students WHERE id IN (...)` (eager load, anti N+1) |
| Panel status Form 1 + nama kaprodi penyetuju | `$student->load('form1Approver')` | `lecturers WHERE id = form1_approved_by` |
| Validasi selesaikan siklus | `$student->load('sidangSubmission.assessments')` | nested eager: sidang → assessments |
| Hitung logbook approved di dashboard admin | `->withCount(['logbooks as approved_logbook_count' => fn($q) => $q->where('status','Approved')])` | subquery `COUNT(*)` ter-embed di SELECT |
| Filter pelamar mitra per prodi (Filament) | `whereHas('student', fn($q) => $q->where('study_program', $v))` | `EXISTS (SELECT ... FROM students ...)` |
| Kunci wajib-sekali | `$student->internshipCycles()->where('jenis_magang','wajib')->exists()` | `SELECT EXISTS(...)` — sangat murah |
| Export Excel pelamar | `Application::with('student:...','internship:...')` | eager load 3 tabel |

Semua listing memakai `paginate()`; kolom SELECT sering dibatasi (`student:id,nim,...`)
untuk mengecilkan payload.

---

## 6. Analisis Normalisasi

**Baseline: skema ini 3NF** untuk tabel proses — setiap non-key attribute bergantung
penuh pada PK, tidak ada dependensi transitif di `applications`, `logbooks`,
`form2_submissions`, `defense_assessments`, dll.

Ada **4 penyimpangan yang disengaja** (denormalisasi by design) + 1 kecelakaan:

1. **`internship_cycles` = tabel snapshot (denormalisasi historis — pola yang benar).**
   `nim`, `nama`, `ipk`, `company_name` dll. redundan terhadap tabel sumber — sengaja.
   Riwayat harus merekam **nilai saat kejadian**, bukan referensi live: kalau nanti
   nama/IPK mahasiswa berubah, riwayat lama tidak boleh ikut berubah. Ini pola standar
   audit/event snapshot, sama seperti tabel `order_items` menyimpan harga saat beli.

2. **`students.form1_data` JSON = pelanggaran 1NF yang disengaja.** Tujuh atribut
   dalam satu kolom. Trade-off: (+) snapshot atomik isian Form 1, fleksibel tambah
   field (kita menambah `jenisMagang` tanpa ALTER TABLE); (−) tidak bisa di-WHERE/
   JOIN langsung dengan index, integritas nilai dijaga aplikasi (FormRequest), bukan DB.
   Aturan main: field yang perlu di-query lintas mahasiswa → kolom; yang hanya
   ditampilkan ulang → boleh JSON.

3. **`students.name` & `students.email` duplikat `users.name`/`users.email`
   (dependensi transitif → melanggar 3NF).** Kenyamanan query (profil mahasiswa tanpa
   join `users`) dibayar risiko drift — tidak ada mekanisme sinkron. Kalau integrasi
   portal universitas jadi (lihat diskusi), tetapkan satu sumber dan jadikan yang
   lain turunan.

4. **`students.access_status` menyimpan *state* turunan proses.** Secara teori status
   bisa diderivasi dari keberadaan child records; menyimpannya = denormalisasi state
   yang lazim dan benar (cepat, dan justru jadi satu sumber kebenaran alur karena
   dijaga state machine).

5. **`students.tahun_akademik` = kolom mati (kecelakaan kecil).** Ada di DB tapi model
   menimpanya dengan accessor terhitung. Kandidat drop, atau hidupkan lagi bila
   integrasi portal membawa kalender akademik resmi.

**Kenapa tidak ada `cycle_id` di child tables?** Keputusan "arsitektur ringan":
riwayat lintas siklus diwakili snapshot agregat (`internship_cycles`), child siklus
lama cukup di-soft-delete. Bentuk penuh (tiap child ber-FK ke siklus) lebih normal
secara relasional tapi menuntut refactor semua query — sengaja ditunda.

---

## 7. Di Mana Integritas Ditegakkan?

| Aturan | Penegak | Lapisan |
|---|---|---|
| Satu lamaran per lowongan; satu logbook per hari; satu sidang/DPM per mahasiswa; satu nilai per peran; satu cycle_number per mahasiswa | UNIQUE index | **DB** |
| Referensi antar tabel + perilaku hapus | FK RESTRICT / SET NULL | **DB** |
| Nilai enum status | kolom ENUM | **DB** (⚠️ SQLite test tidak memvalidasi enum!) |
| Urutan alur (tak bisa lompat status) | `StudentStateMachine::TRANSITIONS` | **Aplikasi** |
| Wajib maksimal 1× | cek `internship_cycles` di Form1Controller | **Aplikasi** |
| Non-wajib tak boleh ke tahap DPM | guard SupervisorController | **Aplikasi** |
| 6 logbook sebelum sidang; 3 nilai sebelum selesai | service terkait | **Aplikasi** |
| Data akademik Form 1 tidak dari input user | Form1Controller (server-side snapshot) | **Aplikasi** |
| Anti race-condition (approve/complete/reset ganda) | `DB::transaction` + `lockForUpdate()` | **Aplikasi + DB lock** |

Prinsipnya: **kardinalitas & referensi = DB; alur & aturan bisnis = state machine/service.**

---

## 8. Index & Performa

Semua FK otomatis ber-index (InnoDB). Index tambahan yang disengaja
(migration `2026_06_08_000001` + unique bawaan):

| Index | Melayani |
|---|---|
| `students(study_program, access_status)` | dashboard Kaprodi (filter prodi+status), daftar review Form 1 |
| `applications(student_id, status)` | lamaran aktif mahasiswa; auto-cancel observer |
| `logbooks(student_id, status)` | hitung logbook approved (gerbang sidang) |
| `defense_assessments(lecturer_id, defense_submission_id)` | "nilai yang sudah saya isi" per dosen |
| `users(locked_until)` | pengecekan lockout saat login |
| Semua UNIQUE di §3 | sekaligus index pencarian |

Skala data kampus (ribuan mahasiswa, puluhan ribu logbook) — semua query utama
terlayani index; tidak ada full scan di jalur panas.

---

## 9. Gotcha & Catatan Operasional

1. **Perubahan enum = 2 tempat.** Test memakai SQLite yang membangun ulang skema dari
   migration awal → nilai enum baru wajib ditambah di
   `2025_01_01_000002_create_students_table.php` **DAN** migration ALTER khusus MySQL
   (`DB::statement("ALTER TABLE ... MODIFY ...")`, di-guard `driver === 'mysql'`).
   Contoh nyata: `2026_07_06_000001_add_selesai_non_wajib...`.
2. **Rollback migration enum berbahaya** bila sudah ada baris bernilai enum baru —
   `down()` akan gagal/memotong data.
3. **SQLite tidak menegakkan enum** → test bisa menyimpan status ngawur yang MySQL
   tolak. Jangan andalkan test untuk validasi nilai enum.
4. **`form1_data` dibaca banyak pihak**: Filament PPAIP menampilkan
   `form1_data['skemaMagang']` dkk via accessor — ubah struktur JSON = cek semua
   pembacanya (grep `form1_data[`).
5. **Path file** (`*_path`) menunjuk disk `local`/`public` — DB tidak menjamin
   filenya ada; reset siklus tidak menghapus file fisik (orphan).
6. **`AUTO_INCREMENT` kecil di dump** (data dev sedikit) — jangan asumsikan id
   berurutan di logika apa pun.
7. **Seeder & FK RESTRICT**: hapus data uji harus urut anak→induk, atau pakai
   `migrate:fresh` sekalian.

---

## 10. Urutan Migration (28 file, kronologis)

```
0001_01_01_000000  users + password_resets + sessions
0001_01_01_000001  cache            0001_01_01_000002  jobs
2025_01_01_000001  lecturers
2025_01_01_000002  students   (jantung skema; enum access_status)
2025_01_01_000003  internships
2025_01_01_000004  applications
2025_01_01_000005  form2_submissions
2025_01_01_000006  supervisor_applications
2025_01_01_000007  logbooks
2025_01_01_000008  sidang_submissions
2025_01_01_000009  + kolom jadwal sidang
2026_06_01_000001  + kolom praktisi/periode di supervisor_applications
2026_06_07_000001  + role dosen_penguji & lockout users
2026_06_07_000002  + penguji di sidang_submissions
2026_06_07_000003  logbook date integrity (drop approved_logbook_count, unique tanggal)
2026_06_07_000004  soft deletes + restrict FKs (7 tabel)
2026_06_08_000001  index performa komposit
2026_06_08_000003  unique (student, internship) di applications
2026_06_08_000004  penyesuaian sidang
2026_06_20_000001  (users)          2026_06_26_000001  + nama/jabatan pimpinan form2
2026_06_29_000001  (sidang)         2026_06_29_000002  defense_assessments
2026_07_06_000001  + enum SelesaiNonWajib (MySQL-only ALTER)
2026_07_06_000002  internship_cycles (riwayat)
```

---

## 11. Kueri Contoh (untuk memahami data)

```sql
-- Posisi semua mahasiswa dalam alur
SELECT access_status, COUNT(*) FROM students WHERE deleted_at IS NULL GROUP BY access_status;

-- Riwayat lengkap seorang mahasiswa
SELECT cycle_number, jenis_magang, outcome_status, company_name,
       tanggal_mulai, tanggal_selesai, final_score, letter_grade
FROM internship_cycles WHERE student_id = ? ORDER BY cycle_number;

-- Mahasiswa yang terkunci opsi wajib
SELECT DISTINCT s.nim, s.name FROM students s
JOIN internship_cycles ic ON ic.student_id = s.id AND ic.jenis_magang = 'wajib';

-- Rekonstruksi nilai akhir sidang (rumus aplikasi)
SELECT ds.student_id,
  SUM(CASE WHEN assessor_role = 'dpm'
       THEN internship_performance_score*0.5 + final_report_score*0.3 + presentation_score*0.2 END) AS dpm,
  AVG(CASE WHEN assessor_role != 'dpm'
       THEN internship_performance_score*0.5 + final_report_score*0.3 + presentation_score*0.2 END) AS penguji_avg
FROM defense_assessments da JOIN sidang_submissions ds ON ds.id = da.defense_submission_id
GROUP BY ds.student_id;
-- skor akhir = (dpm + penguji_avg*2) / 3

-- Arsip siklus lama seorang mahasiswa (yang sudah di-soft-delete saat reset)
SELECT * FROM form2_submissions WHERE student_id = ? AND deleted_at IS NOT NULL;
```
