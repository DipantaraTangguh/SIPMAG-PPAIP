# SIPMAG UBakrie — Dokumentasi Frontend

> SPA React 18 + TypeScript (parsial), React Router, Tailwind CSS v4, Vite —
> khusus untuk role **mahasiswa**. Dosen/staf memakai Filament (`/admin`), bukan SPA ini.
> Pelengkap: [DOCS-CODEBASE.md](DOCS-CODEBASE.md) · [DOCS-BACKEND.md](DOCS-BACKEND.md) · [DOCS-DATABASE.md](DOCS-DATABASE.md).

---

## 1. Bootstrapping & Build

```
resources/views (blade shell) ── <div id="app"> ──┐
resources/css/app.css  (Tailwind v4 @theme)       │  vite.config: input =
resources/ts/main.tsx  (ReactDOM.createRoot)  ────┴─ [app.css, main.tsx]
        └─► <App/> (StrictMode)
```

- **Entry**: `resources/ts/main.tsx` → render `App.tsx` ke `#app`. Laravel route
  catch-all `/{any}` (routes/web.php) menyajikan shell blade yang sama untuk semua
  path SPA → routing sepenuhnya di client.
- **Build**: `npm run dev` (HMR) / `npm run build` (produksi → `public/build`,
  ~432 KB / 115 KB gzip). Tidak ada tailwind.config.js — **Tailwind v4** memakai
  blok `@theme` di `resources/css/app.css`.
- **Tema warna** (CSS variables di `@theme`): `--color-primary: #85171A` (maroon UB),
  `-dark #682828`, `-hover #943939`, `-pale #F9ECEC`. Dipakai sebagai kelas Tailwind
  `bg-primary`, `text-primary-hover`, dst. Ikon: `lucide-react`.

---

## 2. Peta Direktori (`resources/ts/`)

```
main.tsx                 entry
App.tsx                  BrowserRouter + semua <Route> + guard
context/
  AppContext.tsx         AUTH: user login, profil student, refreshProfile
  StudentWorkflowContext.tsx  WORKFLOW: seluruh data & mutasi siklus magang (632 baris)
  simulationMappers.js   mapper snake_case API → camelCase UI (mapStudent, mapForm2Submission)
hooks/
  useForm1.js            state+validasi Form 1 (termasuk kunci wajib)
  useVacancyDetail.js    detail lowongan + lamar
lib/
  api.js                 satu-satunya gerbang HTTP (CSRF, error handling, download blob)
  fetch.js               util lama (fetchWithCsrf) — sisa; jalur utama pakai api.js
utils/
  accessUtils.js         gerbang status frontend (mirror konstanta backend)
Components/
  Elements/              ATOM: Button, Card, Badge, Modal, FileUpload, LoadingSpinner,
                         EmptyState, StatusChip (generik, tanpa logika domain)
  Fragments/<domain>/    MOLEKUL/ORGANISME per domain (punya logika domain):
                         form1/ dashboard/ guidance/ portal/ vacancy/ independent/ defense/
  Layouts/               DashboardLayout, Sidebar, ProtectedRoute, GuestRoute, ErrorBoundary
Pages/                   satu folder per route (komposisi Fragments di dalam Layout)
```

**Aturan atomic design yang dipakai**: `Elements` tidak boleh menyentuh context/API;
`Fragments` boleh baca context domainnya; `Pages` merakit + guard; fetch HANYA via
`lib/api.js`; formatting tanggal Indonesia di mapper/komponen, bukan di API.

---

## 3. Routing (`App.tsx`)

| Path | Page | Guard |
|---|---|---|
| `/login` | LoginPage | `GuestRoute` (sudah login → redirect) |
| `/dashboard` | DashboardPage | `ProtectedRoute` mahasiswa |
| `/form1` | Form1Page (isi form) | + redirect ke status bila sudah submit |
| `/form1/status` | Form1StatusPage | + redirect ke form bila belum submit |
| `/portal` | InternshipPortalPage | mahasiswa |
| `/portal/vacancy/:id` | VacancyDetailPage | mahasiswa |
| `/portal/independent/form2/new` | Form2NewPage | + guard Form 1 approved & belum secured |
| `/guidance` | GuidancePage | + gate status & gate non-wajib |
| `/defense` | InternshipDefensePage | mahasiswa |
| `/history` | MagangHistoryPage | mahasiswa |
| `*` | → `/dashboard` | |

**Guard berlapis**:
1. `ProtectedRoute` — cek `isLoggedIn` (spinner saat `isLoading`), lalu cek role;
   role non-mahasiswa dilempar keluar SPA ke `/admin` (redirect eksternal).
2. **Guard per halaman** — berdasarkan `accessStatus` (mis. Form1Page hanya
   `Unverified|RejectedForm1`) dan jenis magang (GuidancePage menolak non-wajib).
3. `ErrorBoundary` membungkus tiap route — crash komponen tidak mematikan seluruh SPA.

> Penting: guard frontend = UX saja. Penegakan sesungguhnya selalu di backend
> (middleware/policy/state) — lihat DOCS-BACKEND §3.

---

## 4. Lapisan HTTP — `lib/api.js` (baca file ini dulu sebelum menyentuh fetch)

Satu modul `api` dengan method `get / post / put / upload / download / login`.
Perilaku pentingnya:

1. **CSRF otomatis**: sebelum method tidak-aman (POST/PUT/PATCH/DELETE), pastikan
   cookie `XSRF-TOKEN` ada (hit `/sanctum/csrf-cookie` sekali) lalu kirim header
   `X-XSRF-TOKEN`. GET tidak perlu.
2. **Content-Type cerdas**: JSON di-set manual; `FormData` (upload) dibiarkan —
   browser yang mengisi boundary multipart.
3. **Penanganan sesi terpusat**: respons **401** → redirect paksa `/login`;
   **419** (CSRF basi) → minta reload. Komponen tidak perlu menangani ini.
4. **Error dinormalkan**: pesan diambil dari `data.message` atau gabungan
   `data.errors` → dilempar sebagai `Error(message)` — komponen cukup `catch (err)
   → err.message` (sudah Bahasa Indonesia dari backend).
5. **`download(url, filename)`**: fetch → blob → anchor click → revoke; dipakai
   unduh PDF surat (401 tetap dialihkan ke login).
6. `credentials: 'same-origin'` di semua request — SPA wajib se-origin dengan API
   (relevan saat deploy; lihat SANCTUM_STATEFUL_DOMAINS di DOCS-BACKEND §10).

---

## 5. State Management — dua context, dua tanggung jawab

### 5.1 `AppContext.tsx` — AUTH (siapa yang login)
- State: `{ isLoggedIn, isLoading, student, userRole }`.
- Saat mount → `refreshProfile()` → `GET /me` → `mapStudent(user)`:
  mapper mengubah snake_case API → camelCase UI (`study_program → programStudi`,
  `access_status → accessStatus`, dst.) — **komponen tidak pernah melihat snake_case**.
- `login(loginId, password)` (email/NIM), `logout()`, `updateStudentLocally(patch)` —
  patch optimistis (mis. langsung set `accessStatus` baru tanpa menunggu refetch).
- Konsumen via `useAuth()`.

### 5.2 `StudentWorkflowContext.tsx` — WORKFLOW (632 baris, jantung frontend)
- Saat mahasiswa login → `fetchAllStudentData()`:
  `Promise.allSettled` 6 endpoint sekaligus (`/form1, /applications, /form2,
  /logbooks, /defense, /supervisor-application`) — gagal sebagian tidak
  menggagalkan semua; guard `fetchRunRef` mencegah balapan hasil fetch lama
  menimpa yang baru.
- Menyimpan state ter-map (camelCase, tanggal terformat id-ID) untuk semua domain.
- **Mutasi** (semua: panggil API → refresh → update state + push notifikasi):
  `submitForm1`, `applyToVacancy`, `submitForm2`, `submitPengajuanPembimbing`,
  `addLogbookEntry`, `updateLogbookEntry`, `submitSidang`, **`resetCycle`**
  (POST reset → kosongkan seluruh state workflow → `accessStatus: 'Unverified'`).
- **Notifikasi diturunkan (derived)**: daftar notifikasi dashboard bukan dari server —
  dihitung `useMemo` dari state (form1 pending? lamaran diterima? jadwal sidang?).
- **Context di-split** agar re-render terisolasi per domain — konsumen memilih hook
  paling sempit:

| Hook | Isi |
|---|---|
| `useForm1Workflow()` | form1Submission, submitForm1, resetForm1, **resetCycle** |
| `useApplicationWorkflow()` | activeApplications, applyToVacancy |
| `useForm2Workflow()` | form2Submissions, submitForm2 |
| `useGuidanceWorkflow()` | pengajuanPembimbing, submitPengajuanPembimbing |
| `useLogbookWorkflow()` | logbookEntries, logbookPeriod, add/update |
| `useDefenseWorkflow()` | sidangSubmission, sidangSchedule, submitSidang |
| `useWorkflowNotifications()` | notifications |
| `useStudentWorkflow()` | semuanya (hindari; bikin re-render luas) |

⚠️ Utang teknis yang disadari: file ini god-object (lihat DOCS-CODEBASE §9) —
kandidat dipecah per domain bila proyek berlanjut.

---

## 6. Gating UI Berdasarkan Status — `utils/accessUtils.js`

Mirror manual dari aturan backend (⚠️ dua sumber kebenaran — jaga sinkron!):

| Fungsi/konstanta | Keputusan UI |
|---|---|
| `canAccessPortal / canSubmitForm2` | buka portal & Form 2: `ApprovedForm1` … `SelesaiNonWajib` |
| `SECURED_INTERNSHIP_STATUSES` + `hasSecuredInternship` | sudah `HasDPM`+ / selesai → tombol lamar & Form 2 dikunci + pesan |
| `LOGBOOK_ACCESS_STATUSES` / `SIDANG_ACCESS_STATUSES` | tab logbook (HasDPM+) / halaman sidang (LogbookComplete+) |
| `isCycleComplete` | `SiklusSelesai` **atau** `SelesaiNonWajib` |

Pola pemakaian: Page membaca `student.accessStatus` → pilih render penuh /
panel terkunci / redirect.

---

## 7. Alur UI per Fitur (komponen kuncinya)

### Form 1 (`Pages/Form1/` + `Fragments/form1/`)
- **Form1Page** → hook `useForm1`: readOnlyFields dari profil (nama/NIM/IPK… terkunci,
  ikon gembok), field pilihan: **Jenis Magang** (radio wajib/non-wajib —
  wajib terkunci + helper text bila `has_completed_wajib` dari `GET /form1`),
  skema, topik, output, checkbox pernyataan. Validasi client mirror backend;
  submit `FormData` → navigate `/form1/status`.
- **Form1StatusPage** → **Form1StatusPanel** = router panel per status:
  `PendingReview→Form1PendingPanel · ApprovedForm1→Form1ApprovedPanel (unduh surat,
  lanjut portal) · RejectedForm1→Form1RejectedPanel (alasan + isi ulang) ·
  SiklusSelesai→Form1CompletedPanel · SelesaiNonWajib→Form1NonWajibDonePanel`.
  Dua panel terakhir memuat **CycleResetButton** (konfirmasi inline dua-langkah →
  `resetCycle()` → navigate `/form1`).

### Portal & lamaran (`Pages/Portal/`, `Fragments/portal|vacancy|independent/`)
Daftar lowongan → detail → lamar (upload CV) → `HasApplication`. Tab jalur mandiri
(`independent/`) menuju **Form2NewPage**: guard approved + belum secured; input
perusahaan + periode (granularitas bulan); hasil & unduhan surat pengantar di tab
yang sama.

### Bimbingan & logbook (`Pages/Guidance/`, `Fragments/guidance/`)
Gate berlapis: bukan `HasApplication`+ → panel terkunci; **jenis non-wajib → panel
informasi khusus** (tidak ada tahap DPM/logbook/sidang). Isi: SupervisorRequestForm
(LoA dsb.) dan LogbookTabContent (entri harian, status review DPM, progres 6).

### Sidang (`Pages/Defense/`) — upload laporan/poster/foto → jadwal tampil setelah
kaprodi menetapkan; selesai siklus digerakkan dari sisi admin.

### Riwayat (`Pages/History/MagangHistoryPage.tsx`)
Menu sidebar "Riwayat Magang". Fetch `GET /student/cycle/history` → layout
master-detail (list kiri: nomor siklus/jenis/perusahaan/periode/badge nilai;
kanan: detail 3 seksi — Data Pengajuan / Tempat & Periode / Hasil). Loading state,
empty state, item pertama auto-terpilih.

### Dashboard (`Pages/Dashboard/`)
WelcomeBanner, **CycleStepper** (`deriveStep`: status → langkah 1–6),
DpmCard, NotificationCard (notifikasi derived), QuickActionButton
(status → aksi berikutnya), LogbookProgressCard, TipsMagangCard.

---

## 8. Layout & Navigasi

- **DashboardLayout** — kerangka semua halaman ber-login: Sidebar + header judul +
  konten.
- **Sidebar** (`Layouts/Sidebar.tsx`) — `navItems`: Beranda, Lowongan Magang,
  Bimbingan & Logbook, Sidang Magang, **Riwayat Magang**, Profil. Responsif:
  desktop = kolom kiri 260px; mobile = bottom bar. Active-state: exact match atau
  prefix (`/portal/vacancy/1` menyalakan `/portal`).
- `EmptyState`, `Modal`, `FileUpload`, `StatusChip` (Elements) dipakai lintas domain.

---

## 9. Konvensi & Pola (ikuti saat menambah fitur)

1. **Fetch hanya lewat `lib/api.js`** — jangan `fetch()` langsung; error/CSRF/401
   sudah ditangani terpusat.
2. **snake_case berhenti di mapper** — tambah field API baru? map ke camelCase di
   `simulationMappers.js` / mapping context, jangan bocorkan ke komponen.
3. Komponen baru: generik → `Elements`; ber-domain → `Fragments/<domain>/`;
   halaman baru = folder di `Pages` + route + item Sidebar (bila perlu) + guard.
4. Mutasi workflow baru → tambahkan di `StudentWorkflowContext` (callback → expose
   via hook domain terkait), ikuti pola `resetCycle`.
5. Status/aturan akses baru → update `accessUtils.js` **dan** konstanta backend
   bersamaan (dua sumber kebenaran!), plus `Form1StatusPanel` bila status punya panel.
6. Teks UI Bahasa Indonesia; tanggal `toLocaleDateString('id-ID', …)`.
7. Styling: Tailwind inline; warna brand via `primary*`; ikon lucide 16–20px;
   state loading pakai `Loader2` spin / `FullScreenSpinner`.

---

## 10. Utang Teknis Frontend (ringkas — detail di DOCS-CODEBASE §9)

1. **TypeScript kosmetik**: 77 `.tsx` tapi hanya 3 ber-`interface`; jalur inti masih
   `.js` (useForm1, api, accessUtils); context bertipe `any`. Perbaikan bertahap:
   nyalakan strict, ketik props komponen yang disentuh.
2. **StudentWorkflowContext 632 baris** — pecah per domain.
3. **Duplikasi konstanta status** dengan backend — rawan drift.
4. **Nol test frontend** — seluruh jaminan dari test backend + manual.
5. `lib/fetch.js` legacy tak terpakai di jalur utama — kandidat hapus.
6. Race kecil: gate non-wajib GuidancePage menunggu fetch form1 → bisa flash form
   DPM sesaat (backend tetap menolak).
