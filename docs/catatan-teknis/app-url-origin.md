# APP_URL wajib sama dengan origin peramban
Di setup Docker Compose SIPMAG (dibuat Agustus 2026), `APP_URL` di `.env` wajib
persis sama dengan origin yang dipakai browser (`http://localhost:8000`). Dua hal
diam-diam rusak kalau beda (mis. `APP_URL=http://127.0.0.1:8000` tapi browser buka
`localhost:8000`):

1. **Filament FileUpload loading selamanya** saat re-upload/hapus gambar.
   `config/filesystems.php` disk `public` membangun `url` dari `APP_URL`, dan
   `vendor/filament/forms/dist/components/file-upload.js` memuat file existing lewat
   `fetch(url, {cache:'no-store'})` — bukan `<img>`. Beda origin + route `/storage/*`
   tanpa header CORS = fetch diblok browser, item FilePond tak pernah settle.
   Gejala menipu: gambar tetap tampil (tag `<img>` bebas CORS) dan `laravel.log`
   bersih (server balas 200, browser yang buang response).

2. **"Session store not set on request"** di `POST /api/login`. `statefulApi()` di
   `bootstrap/app.php` cuma aktifkan pipeline `web` kalau host Origin/Referer ada di
   `config('sanctum.stateful')`, yang defaultnya diturunkan dari `APP_URL`.
   Sudah dijinakkan dengan `SANCTUM_STATEFUL_DOMAINS` eksplisit di `.env`.

**Kenapa:** dua-duanya bergantung pada `APP_URL`, jadi satu env salah bikin dua bug yang
kelihatan sama sekali tak berhubungan — gampang salah didiagnosis jadi masalah
permission storage atau volume Docker. Container jalan sebagai root, `storage/` dan
`public/storage` symlink sudah benar; permission tidak pernah jadi penyebab.

**Cara memakainya:** kalau ada laporan upload/hapus gambar Filament menggantung, atau
error session di route API, cek `APP_URL` lawan origin browser dulu sebelum
menyentuh permission, volume, atau `config/filesystems.php` (file itu dipakai
production Railway — jangan diubah untuk memperbaiki dev). Akses dev konsisten lewat
`http://localhost:8000` saja, jangan campur dengan `127.0.0.1:8000`.

Terkait: [Desain siklus magang](desain-siklus-magang.md)
