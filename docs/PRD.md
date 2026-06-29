# Product Requirements Document (PRD)

## 1. Ringkasan Produk

| Item | Deskripsi |
| --- | --- |
| Nama aplikasi | SIPMAG - Sistem Informasi Portal Magang Universitas Bakrie |
| Jenis aplikasi | Aplikasi web administrasi dan portal magang |
| Stack implementasi | Laravel 12, Laravel Sanctum/session auth, Filament 3, React 19, TypeScript, Vite, Tailwind CSS |
| Target institusi | Universitas Bakrie, khususnya proses magang mahasiswa dan koordinasi PPAIP, Kaprodi, DPM, dan dosen penguji |

SIPMAG adalah aplikasi web untuk mengelola siklus magang mahasiswa dari pengajuan kelayakan akademik, pencarian atau pengajuan tempat magang, penugasan dosen pembimbing magang, pengisian logbook, sampai pengajuan dan penjadwalan sidang magang.

Tujuan aplikasi adalah menyatukan proses administrasi magang yang sebelumnya berpotensi tersebar di dokumen, email, dan komunikasi manual menjadi satu alur digital yang memiliki status, validasi, role, dan riwayat data yang jelas.

Masalah yang diselesaikan:

- Mahasiswa membutuhkan jalur terpandu untuk mengetahui tahap magang yang sedang berjalan.
- Kaprodi membutuhkan daftar mahasiswa per program studi untuk review Form 1, melihat transkrip, menugaskan DPM, dan menjadwalkan sidang.
- DPM membutuhkan daftar mahasiswa bimbingan dan logbook yang perlu direview.
- Dosen penguji membutuhkan akses terbatas untuk melihat jadwal sidang dan dokumen sidang yang ditugaskan kepadanya.
- PPAIP membutuhkan pengelolaan lowongan magang, data mahasiswa lintas prodi, dosen, serta review Form 2 magang mandiri.
- File administratif seperti transkrip, LoA, CV, dan dokumen sidang perlu diunggah, disimpan, dan diakses sesuai hak akses.

Target pengguna:

- Mahasiswa.
- Kepala Program Studi (Kaprodi).
- Dosen Pembimbing Magang (DPM).
- Dosen Penguji.
- PPAIP.

## 2. Latar Belakang

Aplikasi ini dibutuhkan karena proses magang memiliki banyak tahapan yang saling bergantung: mahasiswa harus memenuhi syarat akademik, mendapatkan tempat magang, memiliki pembimbing, mencatat aktivitas, dan menyelesaikan sidang. Tanpa sistem terpusat, proses tersebut rentan terhadap keterlambatan, data ganda, kesalahan status, dan kurangnya visibilitas bagi stakeholder akademik.

Konteks penggunaan aplikasi:

- Mahasiswa menggunakan portal utama React untuk login, mengisi Form 1, melihat lowongan, mengajukan Form 2, mengunggah LoA, mengisi logbook, dan mendaftar sidang.
- Kaprodi, DPM, Dosen Penguji, dan PPAIP menggunakan panel Filament di `/admin` untuk menjalankan fungsi administratif sesuai role.
- Backend API di `/api` digunakan oleh frontend mahasiswa dan juga menyediakan endpoint administratif untuk role tertentu.

Nilai utama aplikasi:

- Alur magang berbasis status yang konsisten melalui `access_status` mahasiswa.
- Pembatasan akses berdasarkan role dan program studi.
- Pengurangan proses manual melalui unggah file dan review digital.
- Dashboard mahasiswa yang menampilkan tahap, notifikasi, DPM, dan progres logbook.
- Panel admin berbasis Filament untuk operasional PPAIP, Kaprodi, DPM, dan Dosen Penguji.

## 3. Scope Produk

### 3.1 Fitur yang Sudah Tersedia

- Login pengguna menggunakan NIM mahasiswa atau email.
- Logout dan pengecekan profil pengguna saat ini.
- Role pengguna: `mahasiswa`, `kaprodi`, `dpm`, `ppaip`, `dosen_penguji`.
- Dashboard mahasiswa dengan stepper siklus magang, notifikasi, kartu DPM, progres logbook, dan quick action.
- Form Magang-01/Form 1 untuk pengajuan surat keterangan memenuhi syarat akademik.
- Review Form 1 oleh Kaprodi, termasuk approve/reject dan pratinjau/download transkrip.
- Unduh surat keterangan Form 1 setelah disetujui.
- Portal lowongan magang mitra untuk mahasiswa.
- Pencarian dan sorting lowongan di UI mahasiswa.
- Detail lowongan magang dan upload CV untuk melamar.
- CRUD lowongan magang oleh PPAIP.
- Form 2 untuk pengajuan magang mandiri.
- Review Form 2 oleh PPAIP.
- Pengajuan pembimbing magang dengan upload LoA.
- Penugasan DPM oleh Kaprodi.
- Pengisian, pembaruan, dan daftar logbook mahasiswa.
- Review logbook oleh DPM dengan approve/reject.
- Status logbook lengkap setelah minimal 6 logbook disetujui.
- Pengajuan sidang magang dengan upload dokumen.
- Penjadwalan sidang oleh Kaprodi.
- Akses DPM dan Dosen Penguji untuk melihat sidang terkait, membuka dokumen, dan mengisi penilaian.
- Penilaian sidang tiga penilai dengan komponen 50/30/20, input cepat nilai akhir, dan konversi nilai huruf.
- Monitoring progres penilaian `0/3` sampai `3/3` serta nilai akhir oleh PPAIP.
- Penyelesaian siklus magang melalui endpoint Kaprodi dan juga aksi Filament PPAIP.
- Panel profil dosen untuk upload tanda tangan digital.
- Data mahasiswa lintas prodi untuk PPAIP dan data mahasiswa per prodi untuk Kaprodi.
- Data dosen untuk PPAIP.
- Proteksi path file tersimpan agar tidak terjadi path traversal.
- Rate limiting dan lockout login.

### 3.2 Fitur yang Belum Tersedia / Out of Scope Saat Ini

- Registrasi mandiri pengguna tidak tersedia.
- Reset password/lupa sandi belum terlihat terimplementasi, meskipun tombol "Lupa Sandi?" ada di halaman login.
- Manajemen user lengkap belum terlihat sebagai resource Filament khusus.
- Perubahan status lamaran dari perusahaan melalui UI belum terlihat, meskipun model mendukung status `Accepted`, `RejectedByCompany`, dan `Canceled`.
- Unduh Form 2 resmi belum terhubung ke endpoint nyata di UI mahasiswa; tombol unduh pada kartu Form 2 approved masih membuka `#`.
- Pembuatan PDF Form 2 belum terlihat dari controller/service saat approval.
- Repository publik dokumentasi foto magang belum terlihat, meskipun ada persetujuan publikasi di form sidang.
- Notifikasi realtime, email, atau push notification belum terlihat.
- Validasi "minimal 100 hari kerja magang" pada sidang hanya muncul sebagai teks UI; backend membuka sidang berdasarkan status `LogbookComplete`.
- Berita acara, catatan revisi, approval hasil sidang, dan publikasi nilai kepada mahasiswa belum tersedia.
- Fitur edit ulang pengajuan pembimbing setelah terkirim belum tersedia.
- UI administratif non-mahasiswa sebagian besar menggunakan Filament, bukan React custom.

### 3.3 Batasan Aplikasi Saat Ini

- Akses frontend React utama hanya mengizinkan role `mahasiswa`; role lain diarahkan ke `/admin`.
- Siklus mahasiswa dikendalikan oleh `access_status`; tahapan tidak dapat dilewati melalui service `StudentStateMachine`.
- Lowongan yang tampil ke mahasiswa hanya lowongan aktif dengan `deadline >= today`.
- Mahasiswa hanya bisa mengajukan Form 1 saat status `Unverified` atau `RejectedForm1`.
- Mahasiswa hanya bisa mengajukan pembimbing setelah status `HasApplication`.
- Mahasiswa hanya bisa mengisi logbook setelah memiliki DPM.
- Mahasiswa hanya bisa mengajukan sidang setelah status `LogbookComplete`.
- Sidang hanya memiliki status `Pending` dan `Scheduled` pada tabel `sidang_submissions`.
- Dokumen diunggah ke disk lokal/private dan tidak disajikan secara publik kecuali lewat route terproteksi.

## 4. User Persona

| Persona | Kebutuhan | Hak akses/role |
| --- | --- | --- |
| Mahasiswa | Mengikuti alur magang, mengisi Form 1, mencari lowongan, mengajukan Form 2, upload LoA, mengisi logbook, daftar sidang, melihat status. | Role `mahasiswa`; akses React routes `/dashboard`, `/form1`, `/portal`, `/guidance`, `/defense`; akses API mahasiswa. |
| Kaprodi | Mereview Form 1 mahasiswa prodinya, melihat transkrip, assign DPM, menjadwalkan sidang, melihat mahasiswa per prodi. | Role `kaprodi`; akses Filament resource Kaprodi dan API `/api/kaprodi/*`; data dibatasi `study_program`. |
| DPM | Melihat mahasiswa bimbingan, mereview logbook, dan memberi nilai sidang mahasiswa bimbingannya. | Role `dpm`; akses Filament resource DPM, `/admin/penguji/defenses`, dan API `/api/dpm/*`; hanya mahasiswa dengan `dpm_id` dosen terkait. |
| Dosen Penguji | Melihat jadwal dan dokumen sidang yang ditugaskan serta memberi nilai sidang. | Role `dosen_penguji` atau lecturer yang sedang ditugaskan sebagai penguji; akses Filament `/admin/penguji/defenses`; data dibatasi ke `dosen_penguji_1_id`/`dosen_penguji_2_id` miliknya. |
| PPAIP | Mengelola lowongan, data dosen, melihat mahasiswa semua prodi, mereview Form 2. | Role `ppaip`; akses Filament resource PPAIP dan API `/api/ppaip/*`; lintas prodi. |

## 5. User Flow

### 5.1 Alur Login

1. Pengguna membuka `/login`.
2. Pengguna memasukkan NIM atau email pada field login dan kata sandi.
3. Backend mencari user berdasarkan email, atau berdasarkan NIM melalui relasi `students`.
4. Jika login berhasil:
   - Session web dibuat.
   - Profil user dikembalikan melalui `UserResource`.
   - Mahasiswa diarahkan ke `/dashboard`.
   - Role selain mahasiswa yang masuk ke route mahasiswa akan diarahkan ke `/admin`.
5. Jika login gagal:
   - Pesan error kredensial ditampilkan.
   - Percobaan gagal dicatat.
   - Setelah threshold tertentu akun dapat terkunci sementara.
6. Logout menghapus session dan CSRF token session.

### 5.2 Alur Utama Mahasiswa

1. Mahasiswa login dan masuk dashboard.
2. Jika status `Unverified` atau `RejectedForm1`, mahasiswa mengisi Form 1.
3. Form 1 masuk status `PendingReview`.
4. Kaprodi mereview Form 1:
   - Approve: status menjadi `ApprovedForm1`.
   - Reject: status menjadi `RejectedForm1` dengan alasan.
5. Setelah Form 1 disetujui, mahasiswa memilih jalur:
   - Jalur mitra: melihat lowongan, mengunggah CV, dan melamar.
   - Jalur mandiri: mengajukan Form 2 dan menunggu review PPAIP.
6. Setelah memiliki aplikasi/kelayakan magang (`HasApplication`), mahasiswa mengajukan pembimbing dengan LoA.
7. Kaprodi menugaskan DPM sesuai program studi.
8. Mahasiswa mengisi logbook selama periode magang.
9. DPM menyetujui atau menolak logbook.
10. Setelah minimal 6 logbook disetujui, status menjadi `LogbookComplete`.
11. Mahasiswa mengunggah dokumen sidang.
12. Kaprodi menjadwalkan sidang.
13. Dosen Penguji melihat jadwal dan dokumen sidang yang ditugaskan kepadanya secara read-only.
14. Siklus magang diselesaikan menjadi `SiklusSelesai`.

### 5.3 Alur CRUD Data

| Data | Create | Read | Update | Delete |
| --- | --- | --- | --- | --- |
| Lowongan magang | PPAIP via API/Filament | Semua role login, mahasiswa via portal | PPAIP via API/Filament | PPAIP via API/Filament |
| Form 1 | Mahasiswa | Mahasiswa, Kaprodi prodi terkait | Kaprodi approve/reject; mahasiswa submit ulang jika rejected | Tidak tersedia |
| Lamaran portal | Mahasiswa | Mahasiswa pemilik | Perubahan status didukung model/observer, UI admin khusus belum terlihat | Soft delete tersedia di model, endpoint delete tidak tersedia |
| Form 2 | Mahasiswa | Mahasiswa dan PPAIP | PPAIP approve/reject | Tidak tersedia |
| Pengajuan pembimbing | Mahasiswa | Mahasiswa dan Kaprodi prodi terkait | Kaprodi assign DPM ke mahasiswa | Tidak tersedia |
| Logbook | Mahasiswa | Mahasiswa pemilik, DPM terkait | Mahasiswa edit saat pending/rejected; DPM approve/reject | Tidak tersedia |
| Sidang | Mahasiswa | Mahasiswa, Kaprodi prodi terkait, Dosen Penguji yang ditugaskan | Kaprodi jadwalkan; Kaprodi/PPAIP menyelesaikan siklus sesuai permukaan implementasi; Dosen Penguji read-only | Tidak tersedia |
| Dosen | PPAIP via Filament | PPAIP | PPAIP edit; Kaprodi/DPM update tanda tangan sendiri | Tidak terlihat delete |

### 5.4 Alur Approval/Status

Status utama mahasiswa disimpan pada `students.access_status`.

| Dari | Ke | Pemicu |
| --- | --- | --- |
| `Unverified` | `PendingReview` | Mahasiswa submit Form 1. |
| `PendingReview` | `ApprovedForm1` | Kaprodi approve Form 1. |
| `PendingReview` | `RejectedForm1` | Kaprodi reject Form 1. |
| `RejectedForm1` | `PendingReview` | Mahasiswa submit ulang Form 1. |
| `ApprovedForm1` | `HasApplication` | Mahasiswa melamar lowongan atau Form 2 disetujui PPAIP. |
| `HasApplication` | `HasDPM` | Kaprodi assign DPM setelah pengajuan pembimbing. |
| `HasDPM` | `LogbookComplete` | Minimal 6 logbook disetujui DPM. |
| `LogbookComplete` | `MenungguSidang` | Mahasiswa submit dokumen sidang. |
| `MenungguSidang` | `SiklusSelesai` | Siklus diselesaikan setelah sidang terjadwal dan tiga penilaian lengkap. |

Status lainnya:

- Lamaran: `Applied`, `Accepted`, `RejectedByCompany`, `Canceled`.
- Form 2: `PendingReview`, `ApprovedForm2`, `RejectedForm2`.
- Logbook: `PendingReview`, `Approved`, `Rejected`.
- Sidang: `Pending`, `Scheduled`.

## 6. Daftar Fitur

### 6.1 Autentikasi

| Item | Detail |
| --- | --- |
| Nama fitur | Login dan logout |
| Deskripsi | Pengguna masuk memakai NIM/email dan password. |
| Lokasi halaman/route | `/login`, API `/api/login`, `/api/logout`, `/api/me` |
| Aktor | Semua role |
| Input | Login identifier, password |
| Output | Session login, data user, redirect ke dashboard/admin |
| Validasi/aturan bisnis | Login wajib diisi; password wajib; throttle login; lockout berdasarkan percobaan gagal; role non-mahasiswa diarahkan ke `/admin` jika membuka route mahasiswa. |
| Acceptance criteria | Pengguna valid dapat masuk; pengguna invalid menerima pesan error; session expired diarahkan ke login; logout mengakhiri session. |

### 6.2 Dashboard Mahasiswa

| Item | Detail |
| --- | --- |
| Nama fitur | Dashboard siklus magang |
| Deskripsi | Menampilkan ringkasan status mahasiswa, stepper, DPM, notifikasi, progres logbook, dan quick action. |
| Lokasi halaman/route | `/dashboard` |
| Aktor | Mahasiswa |
| Input | Data profil dan data workflow dari API |
| Output | Status tahap magang, notifikasi, tombol aksi berikutnya |
| Validasi/aturan bisnis | Step ditentukan dari `access_status`; quick action mengarahkan ke halaman sesuai status. |
| Acceptance criteria | Mahasiswa melihat tahap terkini; jika status berubah, quick action menuju halaman yang relevan. |

### 6.3 Form Magang-01/Form 1

| Item | Detail |
| --- | --- |
| Nama fitur | Pengajuan Form 1 |
| Deskripsi | Mahasiswa mengajukan surat keterangan memenuhi syarat akademik. |
| Lokasi halaman/route | `/form1`, `/form1/status`, API `/api/form1`, `/api/form1/surat-keterangan` |
| Aktor | Mahasiswa, Kaprodi |
| Input | Skema magang, topik/tempat magang, output target, file transkrip. Data nama, NIM, prodi, semester, SKS, IPK berasal dari data mahasiswa. |
| Output | Status pengajuan, file transkrip tersimpan, surat keterangan PDF saat approved. |
| Validasi/aturan bisnis | Hanya status `Unverified` atau `RejectedForm1`; skema harus `Mitra`, `Mandiri`, atau `Kewirausahaan`; output harus `Produk`, `Prototype`, atau `Laporan`; transkrip PDF/JPG/PNG maksimal 5 MB; data akademik mahasiswa wajib lengkap; approve membutuhkan tanda tangan digital Kaprodi. |
| Acceptance criteria | Mahasiswa bisa submit Form 1 yang valid; status menjadi `PendingReview`; Kaprodi prodi terkait bisa approve/reject; mahasiswa approved bisa unduh surat keterangan. |

### 6.4 Review Form 1 Kaprodi

| Item | Detail |
| --- | --- |
| Nama fitur | Review Form 1 |
| Deskripsi | Kaprodi melihat pengajuan Form 1 mahasiswa sesuai program studi, memeriksa transkrip, lalu approve/reject. |
| Lokasi halaman/route | Filament `/admin/kaprodi/students`, API `/api/kaprodi/form1` |
| Aktor | Kaprodi |
| Input | Aksi approve atau reject dengan alasan |
| Output | Status `ApprovedForm1` atau `RejectedForm1`, alasan penolakan jika reject |
| Validasi/aturan bisnis | Kaprodi hanya melihat mahasiswa dengan `study_program` sama; approve hanya saat `PendingReview`; tanda tangan digital harus tersedia. |
| Acceptance criteria | Kaprodi tidak bisa memproses mahasiswa prodi lain; alasan wajib saat reject; transkrip dapat dipreview/download jika tersedia. |

### 6.5 Portal Lowongan Mitra

| Item | Detail |
| --- | --- |
| Nama fitur | Portal lowongan magang |
| Deskripsi | Mahasiswa melihat daftar lowongan aktif, mencari, mengurutkan, melihat detail, dan melamar dengan CV. |
| Lokasi halaman/route | `/portal`, `/portal/vacancy/:id`, API `/api/internships`, `/api/applications` |
| Aktor | Mahasiswa |
| Input | Search query, sort, file CV PDF |
| Output | Daftar lowongan, detail lowongan, lamaran mahasiswa |
| Validasi/aturan bisnis | Lowongan harus aktif dan deadline belum lewat; melamar butuh status `ApprovedForm1` atau `HasApplication`; CV PDF maksimal 5 MB; maksimal 5 lamaran aktif di backend; tidak boleh melamar lowongan yang sama dua kali. |
| Acceptance criteria | Mahasiswa belum approved Form 1 hanya bisa melihat lowongan; mahasiswa eligible dapat upload CV dan melamar; lamaran tercatat dan tampil di sidebar aplikasi aktif. |

### 6.6 Manajemen Lowongan PPAIP

| Item | Detail |
| --- | --- |
| Nama fitur | CRUD lowongan magang |
| Deskripsi | PPAIP membuat, mengedit, mengaktifkan/nonaktifkan, dan menghapus lowongan. |
| Lokasi halaman/route | Filament `/admin/ppaip/internships`, API `/api/ppaip/internships` |
| Aktor | PPAIP |
| Input | Nama perusahaan, posisi, lokasi, sistem kerja, deskripsi, kapasitas, durasi, bidang, tanggal mulai, deadline, job description, skills, requirements, minimum education, status aktif. |
| Output | Lowongan tersedia di portal mahasiswa jika aktif dan deadline belum lewat. |
| Validasi/aturan bisnis | Field utama wajib; `is_active` mengontrol visibilitas portal; hanya PPAIP bisa create/update/delete. |
| Acceptance criteria | Lowongan yang aktif dan belum deadline muncul di portal; lowongan nonaktif atau expired tidak muncul. |

### 6.7 Form 2 Magang Mandiri

| Item | Detail |
| --- | --- |
| Nama fitur | Pengajuan Form 2 |
| Deskripsi | Mahasiswa mengajukan surat pengantar magang mandiri untuk perusahaan yang dituju. |
| Lokasi halaman/route | `/portal` tab Mandiri, `/portal/independent/form2/new`, API `/api/form2` |
| Aktor | Mahasiswa, PPAIP |
| Input | Nama perusahaan, alamat perusahaan, lingkup magang, tanggal mulai, tanggal selesai |
| Output | Submission Form 2 dengan status review |
| Validasi/aturan bisnis | Mahasiswa harus sudah Form 1 approved atau berada pada status setelahnya; tanggal selesai harus setelah tanggal mulai; PPAIP dapat approve/reject; jika approved dan mahasiswa masih `ApprovedForm1`, status naik ke `HasApplication`; mahasiswa ditandai `is_independent = true`. |
| Acceptance criteria | Mahasiswa eligible bisa membuat Form 2; PPAIP bisa menyetujui/menolak; status tampil di tab Mandiri; alasan penolakan tampil jika ada. |

### 6.8 Pengajuan Pembimbing dan LoA

| Item | Detail |
| --- | --- |
| Nama fitur | Pengajuan pembimbing magang |
| Deskripsi | Mahasiswa mengunggah data perusahaan, praktisi, periode magang, dan LoA untuk memulai proses penugasan DPM. |
| Lokasi halaman/route | `/guidance`, API `/api/supervisor-application` |
| Aktor | Mahasiswa, Kaprodi |
| Input | Nama perusahaan, nama praktisi, jabatan, nomor telepon, email, tanggal mulai/selesai magang, file LoA |
| Output | Pengajuan pembimbing, file LoA, status menunggu DPM atau DPM assigned |
| Validasi/aturan bisnis | Status mahasiswa harus `HasApplication`; satu mahasiswa hanya boleh punya satu pengajuan pembimbing; LoA PDF/JPG/PNG maksimal 5 MB; nomor telepon divalidasi regex; tanggal selesai setelah tanggal mulai. |
| Acceptance criteria | Mahasiswa bisa submit pengajuan yang lengkap; pengajuan tampil ke Kaprodi prodi terkait; LoA dapat diakses sesuai role. |

### 6.9 Penugasan DPM

| Item | Detail |
| --- | --- |
| Nama fitur | Assign Dosen Pembimbing Magang |
| Deskripsi | Kaprodi memilih dosen pembimbing untuk mahasiswa yang sudah mengajukan pembimbing. |
| Lokasi halaman/route | Filament `/admin/kaprodi/students`, API `/api/kaprodi/assign-dpm` |
| Aktor | Kaprodi |
| Input | `student_id`, `lecturer_id` |
| Output | `students.dpm_id` terisi dan status `HasDPM` |
| Validasi/aturan bisnis | Mahasiswa harus `HasApplication`; harus sudah punya pengajuan pembimbing; belum boleh punya DPM; DPM harus memiliki role `dpm` dan program studi sama dengan mahasiswa. |
| Acceptance criteria | Kaprodi hanya dapat memilih DPM eligible dari prodi terkait; setelah assign, mahasiswa melihat DPM di dashboard dan dapat mengisi logbook. |

### 6.10 Logbook Mahasiswa

| Item | Detail |
| --- | --- |
| Nama fitur | Pengisian logbook |
| Deskripsi | Mahasiswa mencatat kegiatan dan hasil magang harian. |
| Lokasi halaman/route | `/guidance` tab Logbook, API `/api/logbooks` |
| Aktor | Mahasiswa, DPM |
| Input | Tanggal, kegiatan harian, hasil |
| Output | Entri logbook dengan status `PendingReview` |
| Validasi/aturan bisnis | Mahasiswa harus `HasDPM` atau `LogbookComplete`; periode logbook mengikuti tanggal mulai/selesai pada pengajuan pembimbing; tanggal tidak boleh sebelum mulai, tidak boleh melewati tanggal selesai atau hari ini; kombinasi mahasiswa dan tanggal unik; edit hanya untuk status `PendingReview` atau `Rejected`; edit mengembalikan status ke `PendingReview`. |
| Acceptance criteria | Mahasiswa bisa tambah logbook dalam periode valid; duplikasi tanggal ditolak; status dan progres 6/6 tampil di UI. |

### 6.11 Review Logbook DPM

| Item | Detail |
| --- | --- |
| Nama fitur | Review logbook |
| Deskripsi | DPM menyetujui atau menolak logbook mahasiswa bimbingannya. |
| Lokasi halaman/route | Filament `/admin/dpm/logbooks`, API `/api/dpm/logbooks` |
| Aktor | DPM |
| Input | Approve atau reject dengan catatan opsional |
| Output | Status logbook `Approved` atau `Rejected`; catatan DPM jika reject |
| Validasi/aturan bisnis | DPM hanya dapat mereview logbook mahasiswa dengan `dpm_id` dirinya; hanya logbook `PendingReview` yang dapat direview; setelah minimal 6 approved, status mahasiswa naik ke `LogbookComplete`. |
| Acceptance criteria | DPM melihat daftar mahasiswa bimbingan; DPM dapat approve/reject pending logbook; mahasiswa dapat lanjut sidang setelah 6 logbook disetujui. |

### 6.12 Pengajuan Sidang

| Item | Detail |
| --- | --- |
| Nama fitur | Pendaftaran sidang magang |
| Deskripsi | Mahasiswa mengunggah dokumen akhir untuk mendaftar sidang. |
| Lokasi halaman/route | `/defense`, API `/api/defense` |
| Aktor | Mahasiswa, Kaprodi |
| Input | Laporan akhir PDF, poster PDF, foto kegiatan 1, foto kegiatan 2, KRS PDF, dua checkbox deklarasi |
| Output | Submission sidang status `Pending`, status mahasiswa `MenungguSidang` |
| Validasi/aturan bisnis | Mahasiswa harus `LogbookComplete`; satu mahasiswa hanya boleh satu submission sidang; laporan maksimal 10 MB; poster dan KRS maksimal 5 MB; backend menerima foto kegiatan JPG/JPEG/PNG/PDF maksimal 5 MB, namun UI saat ini hanya menerima PDF untuk semua upload sidang. |
| Acceptance criteria | Mahasiswa dengan 6 logbook approved dapat submit dokumen; submission tampil sebagai menunggu jadwal; mahasiswa tidak dapat submit ulang. |

### 6.13 Penjadwalan dan Penyelesaian Sidang

| Item | Detail |
| --- | --- |
| Nama fitur | Jadwal sidang dan closing cycle |
| Deskripsi | Kaprodi menjadwalkan sidang dan menandai siklus magang selesai. |
| Lokasi halaman/route | Filament `/admin/kaprodi/students`, API `/api/kaprodi/defense/*` |
| Aktor | Kaprodi; PPAIP juga memiliki aksi selesai siklus pada resource mahasiswa semua prodi |
| Input | Tanggal sidang, waktu, ruangan/link, dosen penguji 1 dan 2 |
| Output | Status sidang `Scheduled`, jadwal tampil ke mahasiswa, status akhir `SiklusSelesai` saat complete |
| Validasi/aturan bisnis | Tanggal sidang API harus setelah hari ini; dosen penguji 1 dan 2 wajib berbeda; Kaprodi hanya prodi terkait; complete memerlukan status sidang `Scheduled` dan tiga penilaian lengkap. |
| Acceptance criteria | Mahasiswa melihat jadwal sidang setelah dijadwalkan; PPAIP melihat progres dan nilai akhir; siklus hanya dapat ditutup setelah DPM serta kedua penguji selesai menilai. |

### 6.14 Akses dan Penilaian Sidang oleh Dosen

| Item | Detail |
| --- | --- |
| Nama fitur | Examined Sessions |
| Deskripsi | DPM dan Dosen Penguji melihat sidang terkait, membuka dokumen, lalu mengisi atau mengubah nilai. |
| Lokasi halaman/route | Filament `/admin/penguji/defenses`; dokumen sidang via route terproteksi `/admin/defense-documents/{submission}/{document}/*` |
| Aktor | DPM mahasiswa, Penguji 1, dan Penguji 2 yang ditugaskan pada sidang. |
| Input | Kinerja Magang 0-100, Laporan Akhir 0-100, Ujian Presentasi Hasil Magang 0-100; atau satu nilai cepat 0-100 yang diterapkan ke seluruh komponen. |
| Output | Nilai berbobot tiap dosen, progres kelengkapan penilai, rata-rata akhir tiga dosen, dan nilai huruf. |
| Validasi/aturan bisnis | DPM hanya mengakses mahasiswa dengan `students.dpm_id` miliknya; penguji hanya mengakses sidang dengan ID dosennya pada slot penguji; penilaian hanya untuk sidang `Scheduled`; submission/jadwal tetap read-only; nilai dapat diedit; mahasiswa tidak menerima nilai melalui API. |
| Acceptance criteria | Ketiga dosen dapat memberi nilai seluruh komponen; input cepat menyamakan ketiga komponen; bobot 50/30/20 dihitung otomatis; nilai akhir baru tersedia setelah tiga penilai lengkap. |

### 6.15 Profil Dosen dan Tanda Tangan Digital

| Item | Detail |
| --- | --- |
| Nama fitur | Profil dosen |
| Deskripsi | Kaprodi/DPM melihat profil dosen dan mengunggah tanda tangan digital. |
| Lokasi halaman/route | Filament `/admin/lecturer-profile` |
| Aktor | Kaprodi, DPM |
| Input | File tanda tangan PNG/JPEG maksimal 2 MB |
| Output | `lecturers.signature_path` tersimpan |
| Validasi/aturan bisnis | Data dosen utama read-only; tanda tangan digunakan untuk dokumen resmi seperti surat keterangan Form 1; approve Form 1 oleh Kaprodi mensyaratkan tanda tangan. |
| Acceptance criteria | Kaprodi dapat menyimpan tanda tangan dan setelah itu bisa approve Form 1. |

### 6.16 Data Mahasiswa dan Dosen

| Item | Detail |
| --- | --- |
| Nama fitur | Monitoring data mahasiswa dan dosen |
| Deskripsi | PPAIP dan Kaprodi melihat data mahasiswa; PPAIP mengelola data dosen. |
| Lokasi halaman/route | `/admin/ppaip/students`, `/admin/kaprodi/students`, `/admin/ppaip/lecturers`, API `/api/ppaip/students`, `/api/kaprodi/students`, `/api/dpm/students` |
| Aktor | PPAIP, Kaprodi, DPM |
| Input | Filter status, prodi, pencarian |
| Output | Daftar mahasiswa, status, DPM, jumlah logbook approved; daftar dosen dan bimbingan |
| Validasi/aturan bisnis | PPAIP lintas prodi; Kaprodi per prodi; DPM hanya mahasiswa bimbingannya. |
| Acceptance criteria | Setiap role melihat cakupan data sesuai kewenangan. |

## 7. Requirement Fungsional

| ID | Requirement |
| --- | --- |
| FR-001 | Sistem harus menyediakan login dengan NIM atau email dan password. |
| FR-002 | Sistem harus membatasi akses berdasarkan role pengguna. |
| FR-003 | Sistem harus menyediakan dashboard mahasiswa yang menampilkan tahap magang berdasarkan `access_status`. |
| FR-004 | Sistem harus memungkinkan mahasiswa submit Form 1 dengan upload transkrip. |
| FR-005 | Sistem harus menyimpan data akademik Form 1 dari data server, bukan input bebas mahasiswa. |
| FR-006 | Sistem harus memungkinkan Kaprodi approve/reject Form 1 mahasiswa dalam program studi yang sama. |
| FR-007 | Sistem harus menyediakan unduhan surat keterangan Form 1 setelah approved. |
| FR-008 | Sistem harus menampilkan lowongan aktif dan belum melewati deadline. |
| FR-009 | Sistem harus memungkinkan PPAIP membuat, mengedit, menghapus, dan mengaktifkan/nonaktifkan lowongan. |
| FR-010 | Sistem harus memungkinkan mahasiswa melamar lowongan dengan upload CV PDF. |
| FR-011 | Sistem harus mencegah mahasiswa melamar lowongan yang sama dua kali. |
| FR-012 | Sistem harus membatasi maksimal 5 lamaran aktif per mahasiswa. |
| FR-013 | Sistem harus memungkinkan mahasiswa mengajukan Form 2 setelah Form 1 approved. |
| FR-014 | Sistem harus memungkinkan PPAIP approve/reject Form 2. |
| FR-015 | Sistem harus memungkinkan mahasiswa mengajukan pembimbing dengan data praktisi, periode magang, dan LoA. |
| FR-016 | Sistem harus mencegah lebih dari satu pengajuan pembimbing per mahasiswa. |
| FR-017 | Sistem harus memungkinkan Kaprodi assign DPM eligible sesuai prodi. |
| FR-018 | Sistem harus memungkinkan mahasiswa membuat dan memperbarui logbook dalam periode magang. |
| FR-019 | Sistem harus mencegah duplikasi logbook pada tanggal yang sama untuk mahasiswa yang sama. |
| FR-020 | Sistem harus memungkinkan DPM approve/reject logbook mahasiswa bimbingannya. |
| FR-021 | Sistem harus otomatis menaikkan status mahasiswa menjadi `LogbookComplete` setelah minimal 6 logbook approved. |
| FR-022 | Sistem harus memungkinkan mahasiswa mengajukan sidang setelah logbook lengkap. |
| FR-023 | Sistem harus memungkinkan Kaprodi menjadwalkan sidang untuk mahasiswa prodi terkait. |
| FR-024 | Sistem harus menampilkan jadwal sidang ke mahasiswa setelah status sidang `Scheduled`. |
| FR-025 | Sistem harus menyediakan penyelesaian siklus magang setelah sidang dijadwalkan dan penilaian DPM serta kedua penguji lengkap. |
| FR-026 | Sistem harus menyimpan file upload secara aman dan hanya menampilkan file melalui route terproteksi. |
| FR-027 | Sistem harus menyediakan bulk download transkrip untuk Kaprodi/PPAIP sesuai otorisasi. |
| FR-028 | Sistem harus memungkinkan Dosen Penguji melihat daftar dan detail sidang yang ditugaskan kepadanya secara read-only. |
| FR-029 | Sistem harus memungkinkan DPM dan dua Dosen Penguji memberi dan mengubah nilai sidang yang menjadi tanggung jawabnya. |
| FR-030 | Sistem harus menghitung nilai tiap dosen dari Kinerja Magang 50%, Laporan Akhir 30%, dan Ujian Presentasi 20%. |
| FR-031 | Sistem harus menyediakan input cepat yang menerapkan satu nilai ke seluruh komponen. |
| FR-032 | Sistem harus menghitung nilai akhir sebagai rata-rata nilai berbobot DPM, Penguji 1, dan Penguji 2, lalu mengonversinya ke nilai huruf. |
| FR-033 | Sistem tidak boleh menampilkan nilai sidang kepada mahasiswa pada tahap implementasi saat ini. |
| FR-034 | Sistem harus menampilkan progres penilaian dan nilai akhir sidang kepada PPAIP. |

## 8. Requirement Non-Fungsional

### 8.1 Keamanan

- Autentikasi menggunakan session web dan Laravel Sanctum CSRF cookie.
- Endpoint API penting berada di middleware `auth:sanctum`.
- Role middleware `role:*` membatasi endpoint per role.
- Policy Laravel membatasi akses detail berdasarkan pemilik data, DPM terkait, prodi Kaprodi, role PPAIP, atau lecturer yang ditugaskan sebagai Dosen Penguji.
- Login memiliki rate limit per IP dan per kombinasi login/IP.
- Login mencatat percobaan gagal dan dapat mengunci akun sementara.
- File disimpan di disk lokal/private.
- `StoredFilePath::resolve` mencegah path traversal, path absolut, drive path, dan null byte.
- Transkrip, LoA, dan dokumen sidang hanya dapat diakses melalui route yang melakukan otorisasi.

### 8.2 Performa

- Query penting menggunakan pagination melalui helper controller.
- Index database tersedia pada:
  - `students(study_program, access_status)`.
  - `applications(student_id, status)`.
  - `logbooks(student_id, status)`.
  - `users.locked_until`.
- Frontend mahasiswa memuat data workflow dengan `Promise.allSettled` sehingga kegagalan sebagian endpoint tidak langsung memblokir seluruh halaman.
- Lowongan portal dimuat dari API dan difilter di sisi frontend untuk pencarian sederhana.

### 8.3 Responsiveness

- Layout mahasiswa menggunakan sidebar desktop dan bottom navigation pada layar kecil.
- Form dan grid menggunakan layout responsif satu kolom pada mobile dan multi-kolom pada desktop.
- Modal logbook dan upload file memiliki ukuran adaptif.

### 8.4 Usability

- UI mahasiswa menyediakan quick action berdasarkan status.
- Form menggunakan read-only field untuk data akademik/profil agar mahasiswa tidak mengubah data authoritative.
- Ada empty state untuk lowongan kosong, Form 2 kosong, logbook kosong, dan lowongan tidak ditemukan.
- Ada loading state pada login, detail lowongan, submit Form 1, submit Form 2, submit sidang, dan download Form 1.
- Status ditampilkan dengan badge/chip warna.
- Halaman terkunci memberi penjelasan dan tombol menuju tahap yang harus diselesaikan.

### 8.5 Maintainability

- Backend memisahkan controller, FormRequest, Resource, Policy, Service, Observer, dan Model.
- Transisi `access_status` dipusatkan di `StudentStateMachine`.
- Review logbook dipusatkan di `LogbookReviewService`.
- Assign DPM dipusatkan di `DpmAssignmentService`.
- File path resolution dipusatkan di `StoredFilePath`.
- Komponen React dipisah menjadi halaman, fragment, layout, hooks, context, dan utility.

### 8.6 Error Handling

- API mengembalikan pesan JSON untuk kondisi gagal umum, validasi, forbidden, dan not found.
- Frontend `api.js` menangani 401, 419, dan error validasi.
- ErrorBoundary membungkus route React agar crash satu halaman tidak menjatuhkan seluruh aplikasi.
- Upload file menampilkan pesan format/ukuran di sisi UI.
- Saat submit gagal, UI menampilkan pesan error pada form terkait.

## 9. Struktur Data

### 9.1 Tabel/Model Utama

| Model/Tabel | Field penting | Relasi | Fungsi data |
| --- | --- | --- | --- |
| `User` / `users` | `name`, `email`, `password`, `role` (`mahasiswa`, `kaprodi`, `dpm`, `ppaip`, `dosen_penguji`), `failed_login_attempts`, `locked_until`, `last_login_at`, `deleted_at` | HasOne `Student`, HasOne `Lecturer` | Akun login dan role akses. |
| `Student` / `students` | `user_id`, `dpm_id`, `nim`, `name`, `study_program`, `email`, `semester`, `jumlah_sks`, `ipk`, `access_status`, `is_independent`, `form1_data`, `form1_pdf_path`, `form1_rejection_reason`, `form1_approved_by`, `form1_approved_at`, `deleted_at` | BelongsTo `User`, BelongsTo `Lecturer` sebagai DPM, HasMany `Application`, HasMany `Form2Submission`, HasMany `Logbook`, HasOne `DefenseSubmission`, HasOne `SupervisorApplication` | Profil mahasiswa dan state utama siklus magang. |
| `Lecturer` / `lecturers` | `user_id`, `nidn`, `lecturer_name`, `contact`, `study_program`, `signature_path` | BelongsTo `User`, HasMany supervised students, HasMany `DefenseAssessment` | Data dosen, Kaprodi, DPM, dosen penguji, dan tanda tangan digital. |
| `Internship` / `internships` | `company_name`, `position`, `description`, `capacity`, `duration`, `bidang`, `start_date`, `job_description`, `skills`, `requirements`, `minimum_education`, `sistem_kerja`, `location`, `deadline`, `is_active` | HasMany `Application` | Data lowongan magang mitra. |
| `Application` / `applications` | `student_id`, `internship_id`, `cv_file_path`, `loa_path`, `status`, `deleted_at` | BelongsTo `Student`, BelongsTo `Internship` | Lamaran mahasiswa ke lowongan mitra. |
| `Form2Submission` / `form2_submissions` | `student_id`, `company_name`, `alamat_perusahaan`, `lingkup_magang`, `tanggal_mulai`, `tanggal_selesai`, `status`, `rejection_reason`, `pdf_path`, `submitted_at`, `deleted_at` | BelongsTo `Student` | Pengajuan magang mandiri. |
| `SupervisorApplication` / `supervisor_applications` | `student_id`, `company_name`, `company_contact`, `nama_praktisi`, `jabatan_praktisi`, `no_telepon`, `email`, `mulai_magang`, `selesai_magang`, `loa_path`, `submitted_at`, `deleted_at` | BelongsTo `Student` | Pengajuan pembimbing dan periode magang. |
| `Logbook` / `logbooks` | `student_id`, `tanggal`, `kegiatan_harian`, `hasil`, `status`, `dpm_note`, `deleted_at` | BelongsTo `Student` | Catatan aktivitas magang dan review DPM. |
| `DefenseSubmission` / `sidang_submissions` | `student_id`, `laporan_path`, `poster_path`, `foto_kegiatan_1_path`, `foto_kegiatan_2_path`, `krs_path`, `status`, `scheduled_date`, `scheduled_time`, `room`, `dosen_penguji_1_id`, `dosen_penguji_2_id`, `scheduled_by`, `scheduled_at`, `submitted_at`, `deleted_at` | BelongsTo `Student`, BelongsTo `Lecturer` scheduler, examinerOne, examinerTwo, HasMany `DefenseAssessment` | Pengajuan dan jadwal sidang magang. |
| `DefenseAssessment` / `defense_assessments` | `defense_submission_id`, `lecturer_id`, `assessor_role`, `internship_performance_score`, `final_report_score`, `presentation_score` | BelongsTo `DefenseSubmission`, BelongsTo `Lecturer` | Nilai per komponen dari DPM, Penguji 1, atau Penguji 2. |

### 9.2 Relasi Penting

- Satu `User` dapat memiliki satu `Student` atau satu `Lecturer`.
- Satu `Student` dapat memiliki satu DPM melalui `students.dpm_id`.
- Satu `Lecturer` dapat membimbing banyak `Student`.
- Satu `Internship` dapat memiliki banyak `Application`.
- Satu `Student` dapat memiliki banyak `Application`, banyak `Form2Submission`, banyak `Logbook`.
- Satu `Student` memiliki satu `SupervisorApplication` dan satu `DefenseSubmission`.
- Satu `DefenseSubmission` memiliki dua dosen penguji dan satu scheduler dari tabel `lecturers`.
- Satu `DefenseSubmission` memiliki maksimal satu penilaian untuk setiap peran: DPM, Penguji 1, dan Penguji 2.

### 9.3 Constraint dan Index Penting

| Tabel | Constraint/Index | Fungsi |
| --- | --- | --- |
| `students` | `nim` unique | Mencegah NIM ganda. |
| `students` | index `study_program, access_status` | Mempercepat filter Kaprodi. |
| `lecturers` | `nidn` unique | Mencegah NIDN ganda. |
| `applications` | unique `student_id, internship_id` | Mencegah lamaran ganda ke lowongan sama. |
| `applications` | index `student_id, status` | Mempercepat hitung lamaran aktif. |
| `supervisor_applications` | unique `student_id` | Satu pengajuan pembimbing per mahasiswa. |
| `logbooks` | unique `student_id, tanggal` | Satu logbook per tanggal per mahasiswa. |
| `logbooks` | index `student_id, status` | Mempercepat hitung logbook approved. |
| `sidang_submissions` | unique `student_id` | Satu submission sidang per mahasiswa. |
| `defense_assessments` | unique `defense_submission_id, assessor_role` | Mencegah dua nilai untuk peran penilai yang sama pada satu sidang. |
| `defense_assessments` | index `lecturer_id, defense_submission_id` | Mempercepat pencarian nilai milik dosen. |

## 10. API / Route Documentation

### 10.1 Web Routes

| Method | Route | Fungsi | Auth/Middleware |
| --- | --- | --- | --- |
| GET | `/admin/transkrip/{student}/preview` | Preview transkrip dalam iframe atau image. | `web`, `auth`, Gate `viewTranscript` |
| GET | `/admin/transkrip/{student}/download` | Download satu transkrip. | `web`, `auth`, Gate `viewTranscript` |
| POST | `/admin/transkrip/bulk-download` | Download beberapa transkrip dalam ZIP. | `web`, `auth`, Gate `viewTranscripts` |
| GET | `/admin/defense-documents/{submission}/{document}/preview` | Preview dokumen sidang yang terotorisasi. | `web`, `auth`, Gate `view` pada `DefenseSubmission`, `StoredFilePath` |
| GET | `/admin/defense-documents/{submission}/{document}/download` | Download dokumen sidang yang terotorisasi. | `web`, `auth`, Gate `view` pada `DefenseSubmission`, `StoredFilePath` |
| GET | `/{any}` | Fallback ke React app. | Public, kecuali prefix API/admin/filament/debugbar |

### 10.2 Auth API

| Method | Endpoint | Fungsi | Request | Response | Middleware |
| --- | --- | --- | --- | --- | --- |
| POST | `/api/login` | Login user. | `login`, `password` | `user` | `throttle:login` |
| POST | `/api/logout` | Logout user. | - | `message` | `auth:sanctum` |
| GET | `/api/me` | Ambil profil user. | - | `user` | `auth:sanctum` |

### 10.3 API Mahasiswa

| Method | Endpoint | Fungsi | Request/Input | Response/Output | Middleware |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/internships` | Daftar lowongan aktif. | Query `search`, `location`, pagination | `internships` | `auth:sanctum` |
| GET | `/api/internships/{id}` | Detail lowongan aktif. | `id` | `internship` | `auth:sanctum` |
| GET | `/api/form1` | Status Form 1 mahasiswa. | - | Data Form 1 | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/form1` | Submit Form 1. | `skemaMagang`, `topikMagang`, `outputTarget`, `transkrip` | `message`, `access_status` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/form1/surat-keterangan` | Download PDF surat keterangan. | - | File PDF | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/applications` | Daftar lamaran mahasiswa. | Pagination | `applications` | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/applications` | Lamar lowongan. | `internship_id`, `cv_file` | `application` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/form2` | Daftar Form 2 mahasiswa. | Pagination | `submissions` | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/form2` | Submit Form 2. | Data perusahaan dan periode | `submission` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/supervisor-application` | Lihat pengajuan pembimbing dan DPM. | - | `application`, `dpm` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/supervisor-application/loa` | Download LoA mahasiswa. | - | File | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/supervisor-application` | Submit pengajuan pembimbing. | Data perusahaan/praktisi/periode, `loa_file` | `message` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/logbooks` | Daftar logbook mahasiswa. | Pagination | `logbooks`, `approved_logbook_count`, `internship_period` | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/logbooks` | Tambah logbook. | `tanggal`, `kegiatan_harian`, `hasil` | `logbook` | `auth:sanctum`, `role:mahasiswa` |
| PUT | `/api/logbooks/{id}` | Update logbook pending/rejected. | Field logbook | `logbook` | `auth:sanctum`, `role:mahasiswa` |
| GET | `/api/defense` | Lihat status sidang. | - | `submission`, `access_status` | `auth:sanctum`, `role:mahasiswa` |
| POST | `/api/defense` | Submit dokumen sidang. | `laporan`, `poster`, `foto_kegiatan_1`, `foto_kegiatan_2`, `krs` | `message`, `access_status` | `auth:sanctum`, `role:mahasiswa` |

### 10.4 API Kaprodi

| Method | Endpoint | Fungsi | Request/Input | Response/Output | Middleware |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/kaprodi/form1` | Daftar Form 1 prodi. | Pagination | `submissions` | `auth:sanctum`, `role:kaprodi` |
| POST | `/api/kaprodi/form1/{studentId}/approve` | Approve Form 1. | `studentId` | `access_status` | `auth:sanctum`, `role:kaprodi` |
| POST | `/api/kaprodi/form1/{studentId}/reject` | Reject Form 1. | `reason` | `access_status` | `auth:sanctum`, `role:kaprodi` |
| GET | `/api/kaprodi/supervisor-applications` | Daftar pengajuan pembimbing per prodi. | Pagination | `applications` | `auth:sanctum`, `role:kaprodi` |
| GET | `/api/kaprodi/supervisor-applications/{studentId}/loa` | Download LoA mahasiswa prodi terkait. | `studentId` | File | `auth:sanctum`, `role:kaprodi` |
| POST | `/api/kaprodi/assign-dpm` | Assign DPM. | `student_id`, `lecturer_id` | `access_status` | `auth:sanctum`, `role:kaprodi` |
| GET | `/api/kaprodi/defense` | Daftar mahasiswa menunggu sidang. | Pagination | `students` | `auth:sanctum`, `role:kaprodi` |
| POST | `/api/kaprodi/defense/{studentId}/schedule` | Jadwalkan sidang. | Jadwal, ruangan, penguji | `message` | `auth:sanctum`, `role:kaprodi` |
| POST | `/api/kaprodi/defense/{studentId}/complete` | Selesaikan siklus. | `studentId` | `access_status` | `auth:sanctum`, `role:kaprodi` |
| GET | `/api/kaprodi/students` | Daftar mahasiswa per prodi. | Pagination | `students` | `auth:sanctum`, `role:kaprodi` |
| GET | `/api/kaprodi/students/{studentId}/transkrip` | Download/preview transkrip. | `studentId` | File | `auth:sanctum`, `role:kaprodi` |

### 10.5 API DPM

| Method | Endpoint | Fungsi | Request/Input | Response/Output | Middleware |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/dpm/logbooks` | Daftar logbook mahasiswa bimbingan. | Pagination | `logbooks` | `auth:sanctum`, `role:dpm` |
| POST | `/api/dpm/logbooks/{id}/approve` | Approve logbook. | `id` | `approved_logbook_count` | `auth:sanctum`, `role:dpm` |
| POST | `/api/dpm/logbooks/{id}/reject` | Reject logbook. | `note` opsional | `message` | `auth:sanctum`, `role:dpm` |
| GET | `/api/dpm/students` | Daftar mahasiswa bimbingan. | Pagination | `students` | `auth:sanctum`, `role:dpm` |

### 10.6 API PPAIP

| Method | Endpoint | Fungsi | Request/Input | Response/Output | Middleware |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/ppaip/form2` | Daftar semua Form 2. | Pagination | `submissions` | `auth:sanctum`, `role:ppaip` |
| POST | `/api/ppaip/form2/{id}/approve` | Approve Form 2. | `id` | `submission` | `auth:sanctum`, `role:ppaip` |
| POST | `/api/ppaip/form2/{id}/reject` | Reject Form 2. | `reason` | `submission` | `auth:sanctum`, `role:ppaip` |
| GET | `/api/ppaip/students` | Daftar semua mahasiswa. | Pagination | `students` | `auth:sanctum`, `role:ppaip` |
| POST | `/api/ppaip/internships` | Buat lowongan. | Data lowongan | `internship` | `auth:sanctum`, `role:ppaip` |
| PUT | `/api/ppaip/internships/{id}` | Update lowongan. | Data lowongan parsial | `internship` | `auth:sanctum`, `role:ppaip` |
| DELETE | `/api/ppaip/internships/{id}` | Hapus lowongan. | `id` | `message` | `auth:sanctum`, `role:ppaip` |

## 11. UI/UX Overview

### 11.1 Layout Utama

UI mahasiswa menggunakan layout dashboard:

- Sidebar desktop di kiri dengan lebar sekitar 260px.
- Bottom navigation pada mobile.
- Header halaman berisi judul halaman dan avatar inisial mahasiswa.
- Main content berada di background abu-abu muda.
- Komponen utama menggunakan kartu putih, border, badge status, dan warna brand maroon `#682828`.

Navigasi mahasiswa:

- Beranda (`/dashboard`).
- Portal Magang (`/portal`).
- Bimbingan & Logbook (`/guidance`).
- Sidang Magang (`/defense`).
- Profil/Form 1 (`/form1`).

Panel admin Filament:

- Path utama `/admin`.
- Resource dipisah per role melalui slug:
  - DPM: `/admin/dpm/logbooks`.
  - Dosen Penguji: `/admin/penguji/defenses`.
  - Kaprodi: `/admin/kaprodi/students`.
  - PPAIP: `/admin/ppaip/students`, `/admin/ppaip/internships`, `/admin/ppaip/form2`, `/admin/ppaip/lecturers`.
  - Profil dosen: `/admin/lecturer-profile`.

### 11.2 Komponen Penting

- `ProtectedRoute` dan `GuestRoute` untuk guard frontend.
- `DashboardLayout` dan `Sidebar`.
- `CycleStepper` untuk progres siklus.
- `NotificationCard` untuk notifikasi workflow.
- `QuickActionButton` untuk tindakan berikutnya.
- `Form1Card`, `Form1StatusPanel`, `Form1SubmittedData`.
- `VacancyGrid`, `VacancyCard`, `VacancyApplySidebar`, `ActiveApplicationsSidebar`.
- `IndependentTabContent` untuk Form 2.
- `SupervisorRequestForm`, `RequestSubmittedView`, `DpmAssignedView`, `LogbookTabContent`.
- `DefenseLockedState`, `DefenseFormView`, `DefenseSuccessView`, `DefenseScheduledView`, `DefenseCompletedView`.
- Elemen umum: `Button`, `Card`, `Badge`, `StatusChip`, `FileUpload`, `EmptyState`, `LoadingSpinner`, `Modal`.

### 11.3 Empty State, Loading State, Error State

| Kondisi | Implementasi UI |
| --- | --- |
| Login loading | Full screen spinner saat autentikasi dan setelah login berhasil. |
| Login error | Alert merah dengan pesan error. |
| Lowongan loading | Detail lowongan menampilkan loader "Memuat lowongan...". |
| Lowongan tidak ditemukan | Empty state dengan pesan lowongan mungkin dihapus/nonaktif. |
| Portal terkunci | Banner informasi bahwa Form 1 harus selesai sebelum melamar. |
| Form 2 kosong | Empty state "Belum Ada Pengajuan Form 2". |
| Guidance terkunci | Halaman locked state sesuai status. |
| Logbook kosong | Empty state "Belum Ada Entri Logbook". |
| Sidang terkunci | Halaman locked state sesuai status. |
| Upload error | Pesan format/ukuran file pada field terkait. |
| API error | Pesan error ditampilkan dari `err.message` pada form submit. |

## 12. Business Rules

### 12.1 Role dan Permission

- Mahasiswa hanya dapat mengakses data dan workflow miliknya sendiri.
- Kaprodi hanya dapat mengakses mahasiswa dengan `study_program` sama dengan lecturer profile miliknya.
- DPM hanya dapat melihat dan mereview mahasiswa dengan `students.dpm_id` sama dengan lecturer id miliknya.
- PPAIP dapat melihat mahasiswa lintas prodi dan mengelola data lowongan/dosen serta review Form 2.
- Route React mahasiswa hanya untuk role `mahasiswa`; role lain diarahkan ke admin.

### 12.2 Aturan Form 1

- Form 1 hanya dapat diajukan dari status `Unverified` atau `RejectedForm1`.
- Data akademik (`semester`, `jumlah_sks`, `ipk`) harus sudah ada di database.
- Mahasiswa tidak dapat menginput manual semester/SKS/IPK pada Form 1.
- Kaprodi wajib memiliki tanda tangan digital sebelum approve Form 1.
- Surat keterangan hanya dapat diunduh jika status `ApprovedForm1`.

### 12.3 Aturan Lowongan dan Lamaran

- Lowongan portal harus `is_active = true` dan `deadline >= today`.
- Mahasiswa harus sudah `ApprovedForm1` untuk melamar.
- CV wajib PDF maksimal 5 MB.
- Maksimal 5 lamaran aktif status `Applied`.
- Satu mahasiswa tidak boleh melamar lowongan yang sama dua kali.
- Jika sebuah lamaran berubah menjadi `Accepted`, observer otomatis membatalkan lamaran lain dengan status `Applied` untuk mahasiswa yang sama menjadi `Canceled`.

### 12.4 Aturan Form 2 Mandiri

- Form 2 hanya dapat diajukan setelah Form 1 approved atau status setelahnya.
- Tanggal selesai harus setelah tanggal mulai.
- PPAIP dapat approve atau reject Form 2.
- Jika Form 2 approved saat mahasiswa masih `ApprovedForm1`, status mahasiswa menjadi `HasApplication`.
- Form 2 rejected menyimpan alasan penolakan.

### 12.5 Aturan Pengajuan Pembimbing dan DPM

- Pengajuan pembimbing hanya dapat dilakukan saat status `HasApplication`.
- Satu mahasiswa hanya boleh memiliki satu pengajuan pembimbing.
- LoA wajib PDF/JPG/PNG maksimal 5 MB.
- DPM yang dipilih harus:
  - Berasal dari lecturer yang user-nya role `dpm`.
  - Memiliki `study_program` sama dengan mahasiswa.
- Mahasiswa tidak boleh sudah memiliki DPM saat proses assign.

### 12.6 Aturan Logbook

- Logbook baru dapat dibuat saat status `HasDPM` atau `LogbookComplete`.
- Tanggal logbook harus berada dalam periode pengajuan pembimbing.
- Tanggal maksimum adalah tanggal selesai magang atau hari ini, mana yang lebih awal.
- Satu tanggal hanya boleh memiliki satu logbook per mahasiswa.
- DPM hanya dapat mereview logbook `PendingReview`.
- Edit logbook hanya untuk status `PendingReview` atau `Rejected`.
- Edit logbook menghapus `dpm_note` dan mengembalikan status ke `PendingReview`.
- Minimal 6 logbook approved diperlukan untuk membuka sidang.

### 12.7 Aturan Sidang

- Sidang hanya dapat diajukan jika status mahasiswa `LogbookComplete`.
- Mahasiswa hanya boleh memiliki satu submission sidang.
- Submission baru berstatus `Pending`.
- Setelah submit sidang, status mahasiswa menjadi `MenungguSidang`.
- Kaprodi hanya dapat menjadwalkan mahasiswa prodi terkait yang statusnya `MenungguSidang`.
- Jadwal sidang API harus setelah hari ini.
- Dosen penguji 1 dan 2 harus berbeda serta tidak boleh sama dengan DPM mahasiswa.
- Dosen Penguji hanya dapat melihat sidang jika `lecturers.id` miliknya sama dengan `dosen_penguji_1_id` atau `dosen_penguji_2_id`.
- DPM hanya dapat melihat dan menilai sidang mahasiswa dengan `students.dpm_id` yang sesuai.
- DPM dan kedua penguji menilai seluruh komponen: Kinerja Magang 50%, Laporan Akhir 30%, dan Ujian Presentasi Hasil Magang 20%.
- Input cepat menerapkan nilai akhir yang dimasukkan ke seluruh komponen, sehingga hasil berbobot dosen sama dengan nilai input.
- Nilai akhir sidang adalah rata-rata nilai berbobot DPM, Penguji 1, dan Penguji 2 serta baru tersedia setelah ketiganya menilai.
- Konversi nilai: A 85-100; A- 80-84,99; B+ 75-79,99; B 70-74,99; C+ 65-69,99; C 60-64,99; D 50-59,99; E 0-49,99.
- Resource submission tetap read-only; penilai tidak dapat membuat, menghapus, menjadwalkan, atau menyelesaikan sidang, tetapi dapat mengubah penilaiannya sendiri.
- Nilai sidang belum ditampilkan kepada mahasiswa.
- PPAIP dapat melihat progres jumlah penilai dan nilai akhir pada daftar mahasiswa.
- Siklus selesai hanya dapat diproses setelah sidang berstatus `Scheduled` dan penilaian DPM, Penguji 1, serta Penguji 2 lengkap.
- Penyelesaian siklus mempertahankan data Form 1 sebagai riwayat; data cycle lama yang telanjur kosong ditampilkan sebagai ringkasan akademik arsip pada tab Profil.

### 12.8 Aturan File

- File transkrip Form 1: PDF/JPG/JPEG/PNG, maksimal 5 MB.
- CV lamaran: PDF, maksimal 5 MB.
- LoA: PDF/JPG/JPEG/PNG, maksimal 5 MB.
- Laporan sidang: PDF, maksimal 10 MB.
- Poster sidang: PDF, maksimal 5 MB.
- Foto kegiatan sidang: backend menerima JPG/JPEG/PNG/PDF maksimal 5 MB; UI saat ini membatasi input ke PDF.
- KRS sidang: PDF, maksimal 5 MB.
- File yang disajikan kembali harus lolos `StoredFilePath::resolve`.

## 13. Assumption & Gap

### 13.1 Assumption

- Nama aplikasi diambil dari nama repository/dokumen existing dan label UI: SIPMAG atau Portal Magang by PPAIP.
- "Kaprodi" diasumsikan sebagai user dengan relasi `lecturer` dan `study_program` terisi.
- "DPM eligible" diasumsikan sebagai lecturer dengan user role `dpm` dan program studi sama.
- Data mahasiswa, user, dan dosen awal diasumsikan dibuat melalui seeder atau proses admin lain, karena registrasi mandiri tidak tersedia.
- Perubahan status lamaran menjadi `Accepted` diasumsikan akan dilakukan oleh mekanisme admin/operasional lain, karena UI khusus perubahan status lamaran belum terlihat.

### 13.2 Gap

| Area | Gap yang ditemukan |
| --- | --- |
| Register/reset password | Tidak ada halaman register dan reset password yang terimplementasi. Tombol "Lupa Sandi?" tidak terhubung ke alur. |
| Form 2 PDF | Model memiliki `pdf_path`, UI menampilkan tombol "Unduh Form 2", tetapi endpoint/service generate atau download Form 2 belum terlihat dan tombol masih `#`. |
| Lamaran perusahaan | Model dan observer mendukung `Accepted`, `RejectedByCompany`, `Canceled`, tetapi tidak terlihat UI/admin untuk mengubah status lamaran perusahaan. |
| Syarat 100 hari kerja | UI sidang menampilkan syarat minimal 100 hari kerja, tetapi backend hanya mensyaratkan `LogbookComplete`. |
| Foto kegiatan sidang | Backend menerima image atau PDF, tetapi UI upload sidang hanya menerima `.pdf`. |
| Tindak lanjut sidang | Penilaian sudah tersedia, tetapi berita acara, catatan revisi, approval hasil sidang, dan publikasi nilai mahasiswa belum tersedia. |
| Penyelesaian siklus | API `completeCycle` berada pada prefix Kaprodi, sementara Filament PPAIP juga memiliki action "Selesaikan Siklus". Perlu konfirmasi role final yang berwenang. |
| Status setelah `SiklusSelesai` | State machine menjadikan `SiklusSelesai` terminal. Tidak terlihat alur reset ke siklus baru selain pembersihan beberapa field saat complete. |
| Manajemen user | Tidak terlihat resource khusus untuk CRUD user/role. |
| Notifikasi | Notifikasi mahasiswa dibangun dari state frontend, bukan sistem notifikasi persisten/realtime. |
| WhatsApp group | Tombol "Join Grup Whatsapp" menggunakan `dpm.whatsappGroupLink`, tetapi mapping data DPM tidak mengisi field tersebut. |

### 13.3 Hal yang Perlu Dikonfirmasi

- Apakah PPAIP atau Kaprodi yang secara resmi berwenang menyelesaikan siklus magang?
- Apakah Form 2 harus menghasilkan PDF resmi seperti Form 1?
- Siapa yang mengubah status lamaran mitra menjadi diterima/ditolak?
- Apakah jumlah logbook 6 merupakan representasi final syarat magang, atau sementara untuk simulasi?
- Apakah syarat 100 hari kerja perlu divalidasi backend berdasarkan tanggal/periode magang?
- Bagaimana format berita acara, revisi, dan mekanisme publikasi nilai sidang kepada mahasiswa?
- Apakah mahasiswa boleh memiliki lebih dari satu pengajuan Form 2 aktif?
- Apakah siklus magang dapat dimulai ulang setelah `SiklusSelesai`?
- Apakah data user/mahasiswa/dosen akan dikelola PPAIP melalui aplikasi atau impor dari sistem akademik lain?

## 14. Future Improvement

- Menambahkan alur reset password dan/atau integrasi SSO/BIG Universitas Bakrie.
- Menambahkan modul manajemen user dan role untuk PPAIP/admin.
- Menambahkan endpoint dan generator PDF Form 2 resmi serta tombol download yang terhubung.
- Menambahkan UI manajemen status lamaran mitra, termasuk accepted/rejected dan upload LoA dari jalur mitra jika diperlukan.
- Menambahkan validasi backend untuk durasi magang/minimal 100 hari kerja.
- Menyamakan validasi upload foto kegiatan sidang antara UI dan backend.
- Menambahkan notifikasi persisten dan/atau email untuk perubahan status Form 1, Form 2, DPM, logbook, dan sidang.
- Menambahkan audit trail untuk aksi approval, reject, assign DPM, dan schedule sidang.
- Menambahkan fitur komentar atau revisi terstruktur untuk Form 1, Form 2, logbook, dan dokumen sidang.
- Menambahkan berita acara, catatan revisi, approval hasil sidang, dan mekanisme publikasi nilai mahasiswa.
- Menambahkan export laporan untuk PPAIP/Kaprodi, misalnya progres magang per prodi, jumlah mahasiswa per status, dan performa approval.
- Menambahkan dashboard khusus PPAIP, Kaprodi, DPM, dan Dosen Penguji dengan statistik operasional.
- Menambahkan validasi kapasitas lowongan agar jumlah pelamar/diterima tidak melebihi kapasitas.
- Menambahkan mekanisme arsip siklus lama agar mahasiswa dapat menjalani siklus magang berikutnya tanpa kehilangan riwayat.
- Menambahkan integrasi kalender untuk jadwal sidang.
- Menambahkan penyimpanan file di object storage jika skala upload meningkat.
