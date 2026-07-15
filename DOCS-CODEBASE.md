# SIPMAG UBakrie — Dokumentasi Codebase

> Sistem Informasi Magang Universitas Bakrie. Dokumen ini merangkum arsitektur,
> alur bisnis, struktur kode, dan isu terbuka — dibaca dari atas ke bawah cukup
> untuk memahami keseluruhan sistem.

---

## 1. Gambaran Umum & Teknologi

Aplikasi web untuk mengelola siklus magang mahasiswa dari pengajuan syarat
akademik (Form 1) sampai sidang dan penilaian, termasuk magang non-wajib yang
alurnya lebih pendek.

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP 8.2), Sanctum (session SPA), Filament 3 (panel admin) |
| Frontend | React 18 + TypeScript (SPA), React Router, Tailwind CSS, Vite, lucide-react |
| Database | MySQL (produksi/dev), SQLite in-memory (test) |
| PDF | Template DOCX asli + LibreOffice headless (`soffice`) — bukan dompdf |
| Excel | maatwebsite/excel (export pelamar mitra) |

Dua "muka" aplikasi:
- **SPA React** (`/` → semua route non-admin) — untuk **mahasiswa**.
- **Filament admin** (`/admin`) — untuk Kaprodi, DPM, PPAIP, Dosen Penguji.

---

## 2. Aktor / Role (`users.role`)

| Role | Tugas | Antarmuka |
|---|---|---|
| `mahasiswa` | Isi Form 1, lamar magang, Form 2, logbook, sidang, reset siklus | SPA React |
| `kaprodi` | Approve/reject Form 1, tunjuk DPM, jadwalkan sidang, selesaikan siklus | Filament |
| `dpm` | (Dosen Pembimbing Magang) review logbook, nilai sidang | Filament |
| `ppaip` | Review Form 2, kelola lowongan mitra, lihat pelamar, overview mahasiswa | Filament |
| `dosen_penguji` | Menilai sidang sebagai penguji 1/2 | Filament |

Kaprodi hanya melihat mahasiswa se-prodi (scope `study_program`); PPAIP lintas prodi.

---

## 3. Alur Bisnis & State Machine

Seluruh progres mahasiswa disimpan di **satu kolom**: `students.access_status`.
Semua perpindahan status WAJIB lewat `App\Services\StudentStateMachine` —
transisi ilegal melempar `InvalidStateTransitionException`.

### 3.1 Diagram status

```
                        Unverified
                            │  mahasiswa submit Form 1
                            ▼
        ┌──────────── PendingReview ────────────┐
        │ kaprodi reject                        │ kaprodi approve
        ▼                                       ▼
  RejectedForm1 ──(resubmit)──► PendingReview   ApprovedForm1
                                                │
                ┌───────────────────────────────┼─────────────────────────┐
                │ (non-wajib: Form 2 approved)  │ (wajib: Form 2 approved │
                │                               │  ATAU apply mitra)      │
                ▼                               ▼                         │
        SelesaiNonWajib ◄──────────────  HasApplication                  │
                │         (non-wajib:           │ kaprodi tunjuk DPM      │
                │          lamaran mitra        ▼                         │
                │          diterima)         HasDPM                       │
                │                               │ 6 logbook di-approve DPM│
                │                               ▼                         │
                │                        LogbookComplete                  │
                │                               │ mahasiswa submit sidang │
                │                               ▼                         │
                │                        MenungguSidang                   │
                │                               │ 3 penilaian lengkap +   │
                │                               │ kaprodi selesaikan      │
                │                               ▼                         │
                │                         SiklusSelesai                   │
                │                               │                         │
                └───────── reset mandiri ───────┴──► Unverified (siklus baru)
```

### 3.2 Dua jenis magang (`form1_data['jenisMagang']`)

| | **Wajib** | **Non-wajib** |
|---|---|---|
| Batas | **Sekali seumur mahasiswa** | Tanpa batas |
| Alur | Penuh sampai sidang + penilaian | Berhenti di Form 2 approved ATAU lamaran mitra diterima |
| Titik selesai | `SiklusSelesai` | `SelesaiNonWajib` |
| Nilai | Ada (skor + grade) | Tidak ada |

Aturan "wajib sekali" ditegakkan oleh accessor `Student::getHasCompletedWajibAttribute`
(cek baris `internship_cycles` ber-jenis `wajib`) — dicek di `Form1Controller::store`.

**Dua axis yang JANGAN dicampur:**
- `jenisMagang` (wajib/non_wajib) = kewajiban kurikulum → di `form1_data` (JSON).
- `is_independent` (boolean kolom) = cara dapat tempat (jalur mandiri Form 2 vs jalur mitra PPAIP).

### 3.3 Multi-siklus & riwayat

- Row `students` = **siklus aktif** (mutable).
- Tabel `internship_cycles` = **riwayat append-only** — satu baris per siklus selesai,
  berisi snapshot identitas, data Form 1, tempat/periode magang, skor. Tidak pernah
  dihapus, selamat dari reset.
- **Reset mandiri** (`POST /api/student/cycle/reset`): hanya dari `SiklusSelesai` /
  `SelesaiNonWajib`. Soft-delete semua child record siklus berjalan (form2, logbook,
  sidang, lamaran, pengajuan DPM), kosongkan field siklus di `students`, kembali
  `Unverified`. Riwayat tetap.

### 3.4 Penilaian sidang (wajib)

3 penilai: DPM + Penguji 1 + Penguji 2. Masing-masing memberi 3 skor (0–100):
kinerja magang (bobot 50%), laporan akhir (30%), presentasi (20%).

```
Skor akhir = (DPM_tertimbang + rata2(P1,P2)_tertimbang × 2) / 3
Grade: ≥85 A · ≥80 A- · ≥75 B+ · ≥70 B · ≥65 C+ · ≥60 C · ≥50 D · <50 E
```

Siklus wajib hanya bisa diselesaikan bila: status `MenungguSidang` + sidang
`Scheduled` + 3 penilaian lengkap (`InternshipCycleCompletionService::canComplete`).

---

## 4. Skema Database (28 migration)

### Tabel inti

**`users`** — akun semua role. `role` enum, lockout login (`failed_login_attempts`,
`locked_until`), soft delete.

**`students`** — profil + SIKLUS AKTIF mahasiswa:
- Identitas: `nim` (unique), `name`, `study_program`, `email`
- Akademik (diisi admin, otoritatif): `semester`, `jumlah_sks`, `ipk`
  (`tahun_akademik` = accessor dihitung dari tanggal, bukan kolom!)
- Siklus: `access_status` (enum 10 nilai), `form1_data` (JSON), `form1_pdf_path`,
  `form1_rejection_reason`, `form1_approved_by/at`, `dpm_id`, `is_independent`
- Soft delete.

**`internship_cycles`** — RIWAYAT permanen (append-only, TANPA soft delete):
`student_id`, `cycle_number` (unique per mahasiswa), `jenis_magang`, `outcome_status`,
snapshot mahasiswa (`nim`,`nama`,`study_program`,`semester`,`ipk`), snapshot Form 1
(`skema_magang`,`topik_magang`,`output_target`), snapshot tempat (`company_name`,
`alamat_perusahaan`,`nama_pimpinan`,`tanggal_mulai/selesai`), snapshot nilai
(`final_score`,`letter_grade`), `started_at`,`completed_at`.

**`lecturers`** — dosen (`nidn` unique, `lecturer_name`, `study_program`,
`signature_path`); dipakai kaprodi, dpm, penguji.

### Tabel proses (semua ber-`student_id`, semua soft delete kecuali disebut)

| Tabel | Isi | Kardinalitas |
|---|---|---|
| `internships` | Lowongan mitra PPAIP (no soft delete) | — |
| `applications` | Lamaran CV ke lowongan. Status: Applied/Accepted/RejectedByCompany/Canceled. Unique (student, internship) | 1 mhs : N |
| `form2_submissions` | Pengajuan surat pengantar mandiri. Status: PendingReview/ApprovedForm2/RejectedForm2 | 1 mhs : N |
| `supervisor_applications` | Pengajuan DPM + upload LoA + data praktisi + periode | 1 mhs : 1 (unique) |
| `logbooks` | Log mingguan. Unique (student, tanggal). Status review DPM | 1 mhs : N (butuh 6 approved) |
| `sidang_submissions` | Dokumen sidang + jadwal + 2 penguji. **Unique per student** | 1 mhs : 1 |
| `defense_assessments` | Nilai per penilai. Unique (submission, assessor_role). No soft delete | 1 sidang : 3 |

> Catatan arsitektur: child table TIDAK punya `cycle_id` (keputusan "arsitektur
> ringan"). Riwayat lintas siklus diwakili snapshot `internship_cycles`; child
> siklus lama diarsipkan via soft delete saat reset.

---

## 5. Backend — Peta Kode

### 5.1 Controllers API (`app/Http/Controllers/Api/`)

| File | Endpoint kunci | Peran |
|---|---|---|
| `AuthController` | POST /login (throttle), /logout, GET /me | Auth session Sanctum |
| `Form1Controller` | GET/POST /form1; kaprodi: index, approve, reject; GET /form1/surat-keterangan; transkrip | Form 1 + PDF surat keterangan. Data akademik diambil server-side, BUKAN dari input. Guard wajib-sekali di sini |
| `ApplicationController` | GET/POST /applications | Lamar lowongan mitra (upload CV, lock ganda, transisi → HasApplication) |
| `Form2Controller` | GET/POST /form2; ppaip: index, approve, reject; surat-pengantar | Form 2. **Approve bercabang**: non-wajib → SelesaiNonWajib + snapshot; wajib → HasApplication |
| `SupervisorController` | GET/POST /supervisor-application; kaprodi: index, assign-dpm | Pengajuan DPM. **Blok non-wajib (403)** |
| `LogbookController` | CRUD /logbooks; dpm: approve/reject | Logbook mingguan; 6 approved → LogbookComplete |
| `DefenseController` | GET/POST /defense; kaprodi: schedule, complete | Sidang: submit dokumen, jadwal, selesaikan siklus |
| `StudentCycleController` | GET /student/cycle/history; POST /student/cycle/reset | Riwayat + reset mandiri |
| `InternshipController` | GET /internships; ppaip: CRUD | Lowongan |
| `StudentController` | index per role | Listing dashboard admin |

`routes/web.php` — route non-SPA: export Excel pelamar, preview/download CV,
dokumen sidang, transkrip; catch-all `/{any}` → SPA.

### 5.2 Services (`app/Services/`) — logika domain

| Service | Tanggung jawab |
|---|---|
| `StudentStateMachine` | SATU-SATUNYA pintu ubah `access_status`. Konstanta `TRANSITIONS` = sumber kebenaran alur |
| `InternshipCycleCompletionService` | Selesaikan siklus wajib: validasi → snapshot riwayat → clear dpm → `SiklusSelesai`. Atomik (DB transaction + lock) |
| `InternshipCycleSnapshotService` | Tulis baris riwayat. Tempat magang: Form2 approved → pengajuan DPM → lamaran mitra diterima (urutan fallback) |
| `InternshipCycleResetService` | Reset mandiri: guard status → soft-delete children → clear field → `Unverified`. Transaction + lock |
| `DefenseAssessmentService` | Simpan nilai, hitung skor tertimbang/akhir, grade huruf |
| `DpmAssignmentService` | Penunjukan DPM oleh kaprodi |
| `LogbookReviewService` | Review logbook + deteksi 6 approved → transisi |
| `PdfService` | Map data → placeholder `<<...>>` untuk Form 1 & Form 2 |
| `DocxToPdfRenderer` | Isi placeholder di DOCX (ZipArchive+strtr, XML-escaped) → konversi LibreOffice headless. `LIBREOFFICE_PATH` override. Path berspasi wajib rawurlencode |

### 5.3 Lapisan pendukung

- **FormRequests** (`app/Http/Requests/`) — semua validasi input. `StoreForm1Request`
  memvalidasi `jenisMagang|skemaMagang|topikMagang|outputTarget`.
- **Policies** (`app/Policies/`) — otorisasi via `Gate::authorize`. `StudentPolicy::resetCycle`
  = pemilik + status selesai. Kaprodi dibatasi se-prodi.
- **Resources** (`app/Http/Resources/`) — bentuk JSON respons. `Form1Resource`
  mengekspos `has_completed_wajib` (sengaja TIDAK di `StudentResource` — hindari N+1 di listing).
- **Observer**: `ApplicationObserver` — saat lamaran → `Accepted`: batalkan lamaran
  lain + **selesaikan siklus non-wajib** (transisi + snapshot).
- **Enum**: `app/Enums/AssessorRole.php` (dpm/penguji_1/penguji_2). Status akses
  masih string literal (lihat §9).

### 5.4 Filament (`app/Filament/Resources/<Role>/`)

| Resource | Role | Fungsi |
|---|---|---|
| `Kaprodi/KaprodiStudentResource` | kaprodi | Approve/reject Form 1, preview transkrip, tunjuk DPM, jadwal sidang |
| `Ppaip/PpaipForm1Resource` | ppaip | Lihat Form 1 (read-only) |
| `Ppaip/PpaipForm2Resource` | ppaip | Approve/reject Form 2 (titik cabang non-wajib!) |
| `Ppaip/PpaipMitraApplicantResource` | ppaip | Lihat pelamar + CV (**belum ada aksi Terima/Tolak** — lihat §9) |
| `Ppaip/PpaipInternshipResource` | ppaip | CRUD lowongan |
| `Ppaip/PpaipStudentResource` | ppaip | Overview + tombol "Selesaikan Siklus" |
| `Ppaip/PpaipLecturerResource` | ppaip | Kelola dosen |
| `Dpm/DpmLogbookResource` | dpm | Review logbook + nilai sidang |
| `Penguji/ExaminedSessionResource` | dosen_penguji | Nilai sidang sebagai penguji |

---

## 6. Frontend — Peta Kode (`resources/ts/`)

Struktur atomic design:

```
resources/ts/
├── App.tsx                  # Routing (React Router) + guard role
├── context/
│   ├── AppContext.tsx       # Auth: user, student (camelCase), login/logout, refreshProfile
│   └── StudentWorkflowContext.tsx  # ⚠️ 632 baris: fetch+mutasi SEMUA domain workflow
│                            #   (form1, applications, form2, logbook, sidang, DPM,
│                            #    notifikasi turunan, resetCycle). Di-split jadi
│                            #    sub-context per domain: useForm1Workflow, dst.
├── hooks/useForm1.js        # State form + validasi + fetch has_completed_wajib
├── lib/api.js               # fetch wrapper: CSRF cookie, get/post/upload/download
├── utils/accessUtils.js     # Gate status frontend (mirror manual konstanta backend!)
├── Components/
│   ├── Elements/            # Atom: spinner, dsb.
│   ├── Fragments/<domain>/  # Molekul/organisme per domain:
│   │   ├── form1/           #   Form1Card (form+selector jenis), Form1StatusPanel
│   │   │                    #   (router panel per status), panel Approved/Rejected/
│   │   │                    #   Pending/Completed/NonWajibDone, CycleResetButton
│   │   ├── dashboard/       #   WelcomeBanner, CycleStepper, DpmCard, dsb.
│   │   ├── guidance/        #   SupervisorRequestForm, LogbookTabContent, dsb.
│   │   ├── portal/ vacancy/ independent/ defense/
│   └── Layouts/             # DashboardLayout, Sidebar (menu per halaman), guards
└── Pages/                   # Satu folder per halaman route
    ├── Dashboard/ Form1/ Portal/ Form2/ Guidance/ Defense/
    ├── History/MagangHistoryPage.tsx   # /history: list riwayat + panel detail
    └── Auth/LoginPage.tsx
```

Route SPA: `/dashboard`, `/form1`, `/form1/status`, `/portal`, `/portal/vacancy/:id`,
`/portal/independent/form2/new`, `/guidance`, `/defense`, `/history`, `/login`.

Pola data: response API snake_case → di-map manual ke camelCase di context.
Status mahasiswa menentukan halaman/panel yang tampil (lihat `accessUtils.js` +
`Form1StatusPanel.tsx` + guard di tiap Page).

---

## 7. Pipeline PDF (Form 1 & Form 2)

1. Template DOCX asli di `public/assets/template-form-1.docx` / `template-form-2.docx`
   berisi placeholder `<<Nama Mahasiswa>>` dkk (tersimpan XML-escaped: `&lt;&lt;...&gt;&gt;`).
2. `PdfService` memetakan data → placeholder; `DocxToPdfRenderer` mengganti string
   di `word/document.xml` lalu konversi via `soffice --headless --convert-to pdf`.
3. Hasil PDF identik template resmi. Tanda tangan basah DILARANG → diganti badge
   centang hijau (gambar statis Lucide `check` yang dibake ke template).
4. Tanpa LibreOffice terpasang → endpoint balas **503** (tidak ada fallback HTML, by design).
5. ⚠️ Konversi sinkron per-request, ~1–3 dtk CPU-berat — lihat §9 performa.
6. ⚠️ Template rapuh: re-save di Word bisa memecah placeholder antar run XML —
   setelah edit template, generate ulang dan pastikan tak ada `<<...>>` tersisa.

---

## 8. Testing & Cara Menjalankan

**Test**: `php artisan test` — 109 test / 424 assertion (saat dokumen ini ditulis).
SQLite in-memory (`phpunit.xml`), jadi perubahan enum WAJIB juga di migration
create awal, bukan hanya ALTER MySQL. Feature test utama:
`Form1SubmissionTest`, `NonWajibFlowTest`, `CycleResetTest`,
`CycleCompletionAssessmentTest`, `StudentStateMachineTest` (unit).

> Gotcha test: instance `User` di feature test meng-cache relasi `student`;
> setelah transisi status antar request, panggil `$user->refresh()`.

**Dev**:
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate   # set DB mysql: sipmag
php artisan migrate --seed
npm run dev          # vite (atau: npm run build)
php artisan serve    # http://127.0.0.1:8000
```
LibreOffice wajib terpasang untuk fitur PDF (brew install --cask libreoffice).

---

## 9. Isu Terbuka / Known Issues (hasil audit)

### Kritis
1. **Tidak ada UI PPAIP untuk menerima lamaran mitra** (`Accepted`) → jalur
   non-wajib via mitra & `ApplicationObserver` efektif mati di produksi.
2. **Backfill riwayat belum ada** — mahasiswa lama ber-`SiklusSelesai` tanpa baris
   riwayat: kunci wajib-sekali bolong + riwayat hilang saat reset.
3. **Penyelesaian non-wajib tidak atomik** (Form2 approve & observer: 3 write tanpa
   transaction) dan **reset tidak memastikan snapshot ada**.
4. **Celah bisnis**: jalur Form 2 mencatat tempat yang *dilamar*, bukan tempat
   *diterima* (tidak ada langkah konfirmasi LoA untuk non-wajib). Menunggu
   keputusan (opsi: langkah "Konfirmasi Diterima" ringan).

### Sedang
5. Approver tidak melihat `jenisMagang` di Filament saat review Form 1/Form 2.
6. PPAIP/Kaprodi tidak punya tampilan tabel riwayat (`internship_cycles`).
7. Tidak ada fitur batalkan siklus di tengah jalan (mahasiswa bisa menggantung).
8. Dobel jalur non-wajib (Form 2 + mitra paralel) bisa hasilkan riwayat tak akurat
   / dua surat pengantar.
9. Snapshot campur sumber (semester/ipk dari row sekarang, bukan `form1_data`).

### Teknis / kualitas kode
10. Status akses = string literal di 12+ file (belum ada enum `AccessStatus`);
    `SECURED_INTERNSHIP_STATUSES` duplikat 3 tempat (2 backend + frontend).
11. TypeScript kosmetik: 77 `.tsx` hanya 3 yang bertipe; jalur inti masih `.js`.
12. `StudentWorkflowContext` 632 baris (god object).
13. Tidak ada `StudentFactory` → setup test verbose.
14. PDF sinkron memblokir worker PHP-FPM — mitigasi: pre-generate saat approve.
15. Reset tidak menghapus file fisik (PDF/CV/LoA orphan di storage).
16. Belum production-ready: kerja belum di-commit, `.env` masih local/debug,
    LibreOffice harus dipasang di server. (Checklist deploy: lihat diskusi 2026-07-07.)

---

## 10. File Paling Penting (baca berurutan untuk memahami sistem)

1. `app/Services/StudentStateMachine.php` — alur bisnis dalam 1 konstanta
2. `database/migrations/2025_01_01_000002_create_students_table.php` — jantung data
3. `app/Http/Controllers/Api/Form1Controller.php` — pintu masuk siklus
4. `app/Http/Controllers/Api/Form2Controller.php` — titik cabang wajib/non-wajib
5. `app/Services/InternshipCycleCompletionService.php` + `SnapshotService` + `ResetService` — akhir & pergantian siklus
6. `app/Observers/ApplicationObserver.php` — efek samping penerimaan mitra
7. `resources/ts/context/StudentWorkflowContext.tsx` — seluruh state frontend
8. `resources/ts/utils/accessUtils.js` + `Components/Fragments/form1/Form1StatusPanel.tsx` — gating UI per status
9. `app/Services/DocxToPdfRenderer.php` — pipeline PDF
10. `tests/Feature/NonWajibFlowTest.php` + `CycleResetTest.php` — spesifikasi hidup alur baru
