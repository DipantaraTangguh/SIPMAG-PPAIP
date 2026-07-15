# SIPMAG UBakrie — Dokumentasi Backend

> Laravel 12 (PHP 8.2). Dokumen ini membahas siklus request, autentikasi, otorisasi,
> katalog endpoint, Filament, penyimpanan file, dan keamanan. Pasangan dari
> [DOCS-CODEBASE.md](DOCS-CODEBASE.md) (alur bisnis) dan [DOCS-DATABASE.md](DOCS-DATABASE.md) (skema).

---

## 1. Arsitektur & Siklus Request

Dua pintu masuk dengan stack middleware berbeda, satu aplikasi:

```
Browser SPA (React)                          Browser Admin (dosen/staf)
      │  fetch /api/* (cookie session)             │  /admin/* (Filament)
      ▼                                            ▼
routes/api.php ──────────────┐            AdminPanelProvider (Filament)
  auth:sanctum (stateful)    │              session + CSRF + Authenticate
  role:<x> (CheckRole)       │              User::canAccessPanel()
      ▼                      │                     ▼
FormRequest (validasi) ──────┤            Resource per role (canAccess)
Gate::authorize (Policy) ────┤                     │
      ▼                      │                     ▼
Controller (tipis) ──────────┴──────► Service (logika domain)
                                            │
                              StudentStateMachine / DB::transaction
                                            ▼
                                      Eloquent Model → MySQL
```

Registrasi global ada di `bootstrap/app.php` (Laravel 11+ style, tanpa Kernel):
- `$middleware->statefulApi()` → Sanctum memperlakukan request SPA se-origin sebagai
  **session-based** (cookie), bukan token.
- Alias `role` → `App\Http\Middleware\CheckRole`.
- Observer & rate-limiter didaftarkan di `AppServiceProvider::boot`.

**Base controller** (`app/Http/Controllers/Controller.php`) menyediakan 2 helper
dipakai semua listing:
- `perPage($request, $default=20)` — baca `?per_page`, di-clamp **1–100** (anti abuse).
- `resourceCollection(...)` — bungkus paginator + transform lewat Resource.

---

## 2. Autentikasi (siapa kamu)

### 2.1 Model: Sanctum stateful SPA — BUKAN token
Login menghasilkan **session cookie** + CSRF cookie (`XSRF-TOKEN`), bukan bearer token.
Frontend (`resources/ts/lib/api.js`) mengambil CSRF cookie lalu mengirim header
`X-XSRF-TOKEN` di setiap request tulis. Tabel `personal_access_tokens` ada tapi tidak
dipakai (ada test yang memastikan legacy bearer token DITOLAK).

### 2.2 Alur login (`AuthController::login`) — berlapis
1. **Rate limit** `throttle:login` (di `AppServiceProvider`): 30/menit per IP **DAN**
   10/menit per kombinasi identifier+IP (identifier di-hash HMAC sebelum jadi key —
   tidak bocor di cache). Respons 429 + log ke channel `security`.
2. **Identifier fleksibel**: email **atau NIM** (`findUser`: cari `users.email`, fallback
   `students.nim` → relasi user).
3. **Anti user-enumeration**: kalau user tidak ditemukan, tetap jalankan `Hash::check`
   terhadap **dummy hash** (config `auth.login_security.dummy_password_hash`) supaya
   durasi respons sama dengan user yang ada — penyerang tak bisa membedakan
   "email salah" vs "password salah" dari timing.
4. **Lockout eksponensial** dalam `DB::transaction` + `lockForUpdate` (anti race):
   gagal ≥ 5× (`AUTH_LOCK_AFTER_ATTEMPTS`) → kunci 60 dtk, lalu 120, 240, … maksimum
   900 dtk (`AUTH_MAXIMUM_LOCK_SECONDS`). Respons 429 + header `Retry-After`.
   Sukses login → reset counter.
5. Sukses: `Auth::guard('web')->login()` + **`session()->regenerate()`**
   (anti session-fixation). Logout: invalidate session + regenerate CSRF token.
6. **Audit**: semua kejadian (failed/locked/rate-limited/succeeded) ditulis ke
   `storage/logs/security.log` (channel `security` di `config/logging.php`) berisi
   hash identifier, IP, user-agent — bukan kredensial mentah.

### 2.3 Login Filament terpisah
`/admin/login` memakai form login bawaan Filament (session sama, guard `web`).
Gerbangnya `User::canAccessPanel()`: hanya role `kaprodi|dpm|ppaip|dosen_penguji`.
Mahasiswa tidak bisa masuk `/admin` walau kredensial benar.
⚠️ Catatan: lockout eksponensial di atas hanya di `/api/login` — login Filament
tidak melewati `AuthController` (hanya dilindungi mekanisme Filament standar).

---

## 3. Otorisasi (boleh apa) — 3 lapis

| Lapis | Mekanisme | Contoh |
|---|---|---|
| 1. **Role gate** | Middleware `role:mahasiswa` dkk (`CheckRole`) — 403 bila role tak cocok | Grup route `/api/kaprodi/*` hanya kaprodi |
| 2. **Policy per-record** | `Gate::authorize('view', $model)` → `app/Policies/*` | Kaprodi hanya mahasiswa se-`study_program`; mahasiswa hanya miliknya; DPM hanya bimbingannya |
| 3. **State guard** | Cek `access_status` / status record sebelum aksi | Form 2 hanya saat `ApprovedForm1|HasApplication`; reset hanya dari `SiklusSelesai|SelesaiNonWajib` |

7 policy: `StudentPolicy` (view/reviewForm1/assignDpm/manageDefense/**resetCycle**),
`ApplicationPolicy`, `Form2SubmissionPolicy`, `SupervisorApplicationPolicy`,
`LogbookPolicy`, `DefenseSubmissionPolicy`, `InternshipPolicy`.
Pola scoping kunci di `StudentPolicy::sameStudyProgramKaprodi()` — kaprodi terkunci prodi.

Lapis 3 unik proyek ini: `StudentStateMachine` membuat lompatan status **mustahil**
dari kode mana pun (`InvalidStateTransitionException`), jadi otorisasi bukan cuma
"siapa boleh" tapi juga "kapan boleh".

---

## 4. Katalog Endpoint API (`routes/api.php`)

Semua respons JSON. Semua selain login butuh session (`auth:sanctum`).
Konvensi error: 401 belum login · 403 role/policy/state salah · 404 tak ditemukan ·
422 validasi/aturan bisnis · 429 rate-limit/lockout · 503 LibreOffice absen.

### Publik
| Method | Path | Aksi | Catatan |
|---|---|---|---|
| POST | `/api/login` | AuthController@login | throttle:login; login via email/NIM |

### Umum (semua role login)
| Method | Path | Aksi |
|---|---|---|
| POST | `/api/logout` | invalidate session |
| GET | `/api/me` | profil user + relasi student/lecturer |
| GET | `/api/internships`, `/api/internships/{id}` | lowongan (read semua role) |

### Mahasiswa (`role:mahasiswa`)
| Method | Path | Controller@method | Prasyarat status |
|---|---|---|---|
| GET | `/api/form1` | Form1@show | — (bawa `has_completed_wajib`) |
| POST | `/api/form1` | Form1@store | `Unverified|RejectedForm1`; data akademik lengkap; **tolak wajib bila sudah pernah wajib** |
| GET | `/api/form1/surat-keterangan` | Form1@downloadSuratKeterangan | Form 1 approved; PDF via LibreOffice |
| GET/POST | `/api/applications` | Application@index/store | store: `ApprovedForm1|HasApplication`, upload CV, blok bila secured |
| GET/POST | `/api/form2` | Form2@index/store | store: `ApprovedForm1|HasApplication`, blok bila secured; set `is_independent` |
| GET | `/api/form2/{id}/surat-pengantar` | Form2@downloadSuratPengantar | status `ApprovedForm2` |
| GET/POST | `/api/supervisor-application` | Supervisor@show/store | store: `HasApplication` + **blok non-wajib (403)** |
| GET | `/api/supervisor-application/loa` | Supervisor@downloadLoa | |
| GET/POST/PUT | `/api/logbooks`, `/api/logbooks/{id}` | Logbook@… | butuh tahap DPM+ |
| GET/POST | `/api/defense` | Defense@show/store | store: `LogbookComplete` (6 approved) |
| GET | `/api/student/cycle/history` | StudentCycle@history | riwayat `internship_cycles` |
| POST | `/api/student/cycle/reset` | StudentCycle@reset | policy `resetCycle`: pemilik + `SiklusSelesai|SelesaiNonWajib` |

### Kaprodi (`role:kaprodi`, prefix `/api/kaprodi`)
| Method | Path | Aksi |
|---|---|---|
| GET | `/form1` | daftar Form 1 se-prodi (Pending/Approved/Rejected) |
| POST | `/form1/{studentId}/approve` · `/reject` | transisi + catat approver/alasan (policy `reviewForm1`) |
| GET | `/supervisor-applications` (+ `/{id}/loa`) | pengajuan DPM se-prodi |
| POST | `/assign-dpm` | tunjuk DPM → `HasDPM` |
| GET | `/defense` | daftar sidang |
| POST | `/defense/{studentId}/schedule` | jadwal + 2 penguji → `Scheduled` |
| POST | `/defense/{studentId}/complete` | selesaikan siklus wajib (validasi 3 nilai) → `SiklusSelesai` |
| GET | `/students` · `/students/{id}/transkrip` | listing + PDF transkrip |

### DPM (`role:dpm`, prefix `/api/dpm`)
GET `/logbooks` · POST `/logbooks/{id}/approve|reject` (6 approved → `LogbookComplete`) · GET `/students`.

### PPAIP (`role:ppaip`, prefix `/api/ppaip`)
GET `/form2` · POST `/form2/{id}/approve` (**titik cabang**: non-wajib → `SelesaiNonWajib`
+ snapshot; wajib → `HasApplication`) · POST `/form2/{id}/reject` · GET `/students` ·
POST/PUT/DELETE `/internships` (CRUD lowongan).

### Route web non-SPA (`routes/web.php`, middleware `web,auth`)
| Path | Guard | Fungsi |
|---|---|---|
| `/admin/mitra-applications/export` | `isPpaip()` + Gate | export Excel pelamar |
| `/admin/mitra-applications/{app}/cv/preview|download` | `isPpaip()` + Gate view | file CV (disk local) |
| `/admin/defense-documents/{submission}/{doc}/preview|download` | Gate view | laporan/poster/foto sidang |
| `/…/{student}/transkrip preview|download` | Gate | PDF Form 1 |
| `/{any}` (catch-all) | — | serve SPA React |

Pola penting: **file privat TIDAK pernah diakses via URL storage publik** — selalu
lewat route ber-Gate yang membaca disk `local` lalu stream response. `StoredFilePath`
(`app/Support`) menormalkan path agar aman (anti path-traversal).

---

## 5. Lapisan Validasi (FormRequests, `app/Http/Requests/`)

Semua input tulis lewat FormRequest — controller tidak pernah memvalidasi manual:

| Request | Aturan kunci |
|---|---|
| `Auth\LoginRequest` | login+password required, max length |
| `Form1\StoreForm1Request` | `jenisMagang in:wajib,non_wajib`; `skemaMagang in:…`; `topikMagang max:2000`; `outputTarget in:Produk,Prototype,Laporan` |
| `Form1\RejectForm1Request` / `Form2\RejectForm2Request` | alasan wajib |
| `Form2\StoreForm2Request` | company/alamat/lingkup wajib; tanggal format `Y-m`, selesai ≥ mulai |
| `Applications\StoreApplicationRequest` | internship_id + `cv_file` (mimetype+size) |
| `Supervisors\StoreSupervisorApplicationRequest` | kontak lengkap; telp regex; `loa_file` mimes pdf/jpg/png + **mimetypes** (cek isi, bukan cuma ekstensi) max 5MB |
| `Supervisors\AssignDpmRequest` | student+lecturer id |

Catatan keamanan upload: validasi ganda `mimes` (ekstensi) + `mimetypes` (magic bytes),
ukuran dibatasi, disimpan ke disk **local** (privat) dengan nama acak dari `store()`.

Penting: **data akademik Form 1 tidak pernah dari input user** — `semester/jumlahSKS/ipk`
diambil dari row `students` di server (`Form1Controller::store`), input user diabaikan.

---

## 6. Output Shaping (`app/Http/Resources/`)

`Form1Resource` (+ `has_completed_wajib`), `StudentResource` (sengaja TANPA
`has_completed_wajib` — anti N+1 di listing), `Form2SubmissionResource`,
`ApplicationResource`, `SupervisorApplicationResource`, `LecturerResource`,
`DefenseSubmissionResource`, `UserResource`, `InternshipCycleResource`.
Pola: kolom dipilih eksplisit; `whenLoaded()` untuk relasi; tanggal diformat ISO/
lokal di sini, bukan di frontend.

---

## 7. Filament Admin (`/admin`)

- **Satu panel** (`AdminPanelProvider`): path `/admin`, login sendiri, warna maroon
  `#682828`, auto-discover resource dari `app/Filament/Resources` (subfolder per role).
- **Dua lapis akses**: (1) `User::canAccessPanel` — 4 role staf; (2) tiap resource
  meng-override `canAccess()`/`canViewAny()` per role (mis. `PpaipForm2Resource`
  hanya `role === 'ppaip'`; `KaprodiStudentResource` menambah scope query se-prodi
  via `getEloquentQuery()`).
- **Aksi penting yang menjalankan logika domain** (bukan CRUD biasa):

| Resource | Aksi | Efek domain |
|---|---|---|
| `Kaprodi/KaprodiStudentResource` | approveForm1 / rejectForm1 | transisi status + approver |
| | assignDpm | pilih DPM dari pengajuan → `HasDPM` |
| | scheduleSidang | jadwal + penguji |
| `Ppaip/PpaipForm2Resource` | Approve/Reject | **cabang wajib/non-wajib** (lihat Form2Controller — logika sama dipanggil) |
| `Ppaip/PpaipStudentResource` | Selesaikan Siklus | `InternshipCycleCompletionService::complete` |
| `Dpm/DpmLogbookResource` | approve/reject logbook + nilai sidang | `LogbookReviewService` / `DefenseAssessmentService` |
| `Penguji/ExaminedSessionResource` | isi nilai | `DefenseAssessmentService` (upsert per role) |
| `Ppaip/PpaipMitraApplicantResource` | ⚠️ view-only | **belum ada aksi Terima/Tolak lamaran** (isu terbuka #1) |

- Filament memakai session + CSRF sendiri (`authMiddleware: Authenticate`), Livewire
  di baliknya — tidak lewat `routes/api.php` sama sekali.

---

## 8. Services — kontrak singkat

(Detail di DOCS-CODEBASE §5.2; ini kontrak I/O-nya.)

| Service | Input → Output | Jaminan |
|---|---|---|
| `StudentStateMachine::transition($student, $to, $extra)` | throw `InvalidStateTransitionException` bila ilegal | satu-satunya penulis `access_status` |
| `InternshipCycleCompletionService::complete($student): float` | skor akhir | transaksi + `lockForUpdate`; tulis snapshot; validasi 3 nilai |
| `InternshipCycleSnapshotService::record($student): InternshipCycle` | baris riwayat | fallback tempat: Form2 → pengajuan DPM → lamaran diterima |
| `InternshipCycleResetService::reset($student)` | void / ValidationException | transaksi + lock; soft-delete children; → `Unverified` |
| `DefenseAssessmentService` | save/finalScore/letterGrade | upsert unik per (submission, role) |
| `DocxToPdfRenderer::render($template, $map): ?string` | path PDF sementara / null | escape XML kedua sisi; timeout 120 dtk; profil LO terisolasi per render |

---

## 9. Keamanan — Ringkasan Menyeluruh

### Sudah kuat ✅
| Area | Implementasi |
|---|---|
| Session | Sanctum stateful, cookie `HttpOnly`, regenerate saat login/logout (anti fixation) |
| CSRF | Cookie XSRF + header di SPA; VerifyCsrfToken di Filament |
| Brute force | Rate-limit 2 dimensi (IP + identifier) **dan** lockout eksponensial per-akun dalam transaksi |
| User enumeration | Dummy-hash timing equalization; pesan error seragam |
| Audit | Channel log `security` khusus (failed/locked/rate-limited/success, IP+UA, identifier di-hash) |
| Otorisasi | 3 lapis (role → policy → state); kaprodi terkunci prodi; mass-assignment dibatasi `$fillable` |
| Upload | mimes+mimetypes+max, disk privat, akses hanya via route ber-Gate, `StoredFilePath` anti traversal |
| Injeksi | Eloquent/parameter binding di semua query; placeholder DOCX di-escape XML |
| Data integritas | Input akademik diabaikan (server-side authoritative); race dicegah `lockForUpdate` di titik kritis (login, apply, complete, reset) |
| SQL berbahaya | Tidak ada raw query dari input user |

### Perlu perhatian ⚠️
1. `APP_DEBUG=true` di .env sekarang — **wajib false** di produksi (bocor stack trace).
2. Login Filament tidak ikut lockout eksponensial custom (hanya `/api/login`).
3. Belum ada header keamanan eksplisit (CSP, X-Frame-Options, HSTS) — tambah middleware/webserver config saat deploy.
4. Belum ada verifikasi email / reset password flow untuk user SPA (akun dibuat admin — by design, tapi catat).
5. Penyelesaian non-wajib belum atomik (3 write tanpa transaksi) — isu terbuka.
6. `SECURED_INTERNSHIP_STATUSES` duplikat di 2 controller + frontend — risiko drift aturan.
7. File orphan tidak dibersihkan (reset tidak menghapus file fisik).

---

## 10. Konfigurasi Env yang Menggerakkan Backend

| Var | Fungsi | Default |
|---|---|---|
| `AUTH_LOCK_AFTER_ATTEMPTS` | ambang lockout | 5 |
| `AUTH_INITIAL_LOCK_SECONDS` / `AUTH_MAXIMUM_LOCK_SECONDS` | backoff kunci | 60 / 900 |
| `AUTH_DUMMY_PASSWORD_HASH` | anti-enumeration | hash bcrypt statis |
| `LIBREOFFICE_PATH` | override lokasi `soffice` (config `services.libreoffice.path`) | autodetect 5 path umum |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | semuanya `database` | — |
| `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN` | WAJIB diset benar saat deploy beda domain | — |

---

## 11. Konvensi yang Harus Diikuti Kontributor

1. Status mahasiswa: **hanya** via `StudentStateMachine::transition` — jangan pernah
   `$student->access_status = ...` langsung.
2. Endpoint baru: FormRequest untuk input + `Gate::authorize` + cek state → baru
   panggil service. Controller tetap tipis.
3. Operasi multi-write yang mengubah siklus → bungkus `DB::transaction` +
   `lockForUpdate` pada row `students`.
4. Listing → selalu `paginate($this->perPage($request))` + Resource + eager load
   kolom terpilih (`with('student:id,nim,...')`).
5. File privat → disk `local`, akses via route ber-Gate; jangan taruh di `public`.
6. Respons manusia berbahasa Indonesia, kunci JSON snake_case bahasa Inggris.
7. Setiap perubahan alur → update `TRANSITIONS` + unit test `StudentStateMachineTest`
   + feature test end-to-end (pola `NonWajibFlowTest`).
