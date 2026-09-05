# SIPMAG — Sistem Informasi Portal Magang

Aplikasi web untuk mengelola seluruh siklus magang mahasiswa Universitas Bakrie, dari pengajuan kelayakan akademik sampai penilaian sidang. Dibangun untuk PPAIP (Pusat Pengembangan Akademik dan Inovasi Pembelajaran) beserta Kaprodi, dosen pembimbing, dan dosen penguji tiap program studi.

Repositori ini berisi dua aplikasi dalam satu basis kode:

- **Portal mahasiswa** — SPA React yang dipakai mahasiswa (`/`)
- **Panel admin** — Filament, dipakai PPAIP, Kaprodi, DPM, dan dosen penguji (`/admin`)

Keduanya berbagi satu origin, satu database, dan satu sesi login.

---

## Alur yang didukung

Magang dibedakan menjadi **wajib** (berujung sidang dan nilai) dan **non-wajib** (selesai setelah konfirmasi penerimaan).

```
Form 1  ──►  disetujui Kaprodi  ──►  cari tempat magang  ──►  lapor diterima
                                          │                        │
                              ┌───────────┴───────────┐            │
                        lamar lowongan mitra    ajukan Form 2      │
                        (portal PPAIP)          (perusahaan        │
                                                 sendiri)          │
                                                                   ▼
                                        wajib ──► DPM ditunjuk ──► logbook (6 disetujui)
                                                                   ──► sidang ──► nilai ──► riwayat
                                        non-wajib ──► konfirmasi + LoA ──► riwayat
```

Satu mahasiswa boleh menjalani lebih dari satu siklus; riwayat siklus lama tersimpan permanen dan tidak ikut terhapus saat siklus direset.

## Peran pengguna

| Peran | Kewenangan utama |
| --- | --- |
| **Mahasiswa** | Mengisi Form 1, melamar lowongan atau mengajukan Form 2, mengajukan pembimbing, mengisi logbook, mendaftar sidang |
| **Kaprodi** | Meninjau Form 1, menunjuk DPM, menjadwalkan sidang — terbatas pada program studinya sendiri |
| **Staff Prodi** | Akun terpisah dengan kewenangan sama seperti Kaprodi; surat resmi tetap tercetak atas nama dan NIDN Kaprodi |
| **PPAIP** | Meninjau Form 2, mengelola lowongan mitra, data dosen, akun Staff Prodi, dan rekap seluruh prodi |
| **DPM** | Meninjau logbook mahasiswa bimbingannya dan menilai sidangnya |
| **Dosen Penguji** | Menilai sidang yang ditugaskan kepadanya |

Nilai akhir sidang dihitung dari tiga penilai: DPM sekali, dua penguji dirata-rata, lalu digabung dengan bobot 1 : 2.

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Laravel Sanctum (sesi cookie) |
| Panel admin | Filament 3 |
| Frontend | React 19, TypeScript, Vite 8, Tailwind CSS 4 |
| Database | MySQL 8.4 |
| Dokumen | DOCX diisi lalu dikonversi ke PDF lewat LibreOffice headless |

---

## Menjalankan secara lokal

Butuh Docker dan Docker Compose. Tidak perlu memasang PHP, Node, atau MySQL di mesin sendiri.

```bash
cp .env.example .env
```

`.env.example` masih memakai bawaan Laravel, jadi sesuaikan dulu agar cocok dengan `compose.yaml`:

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql          # nama service, bukan 127.0.0.1
DB_PORT=3306           # port di dalam jaringan Docker
DB_DATABASE=sipmag
DB_USERNAME=admin
DB_PASSWORD=admin
```

Lalu nyalakan dan siapkan datanya:

```bash
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Layanan yang tersedia setelah itu:

| Alamat | Isi |
| --- | --- |
| http://localhost:8000 | Portal mahasiswa |
| http://localhost:8000/admin | Panel admin |
| http://localhost:8080 | phpMyAdmin |
| localhost:3307 | MySQL (dari mesin host) |

> `APP_URL` wajib sama persis dengan alamat yang dibuka di peramban. Kalau berbeda, unggah berkas di panel admin menggantung tanpa pesan galat dan sesi API ikut rusak.

### Akun contoh

Seluruh akun hasil seeder memakai kata sandi `password`. Mahasiswa masuk dengan **NIM**, selain itu dengan **email**.

| Peran | Kredensial |
| --- | --- |
| PPAIP | `ppaip@bakrie.ac.id` |
| Kaprodi | email dosen Kaprodi tiap prodi, mis. `taufiq.amir@bakrie.ac.id` |
| DPM | mis. `dita.nurmadewi@bakrie.ac.id` |
| Mahasiswa | NIM, mis. `1231001162` |

## Pengujian

```bash
docker compose exec app php artisan test
```

Rangkaian tes berjalan di atas SQLite in-memory, jadi database pengembangan tidak tersentuh.

Untuk memeriksa tipe pada sisi frontend:

```bash
npx tsc --noEmit
```

## Dokumentasi lain

| Berkas | Isi |
| --- | --- |
| [docs/PRD.md](docs/PRD.md) | Spesifikasi produk: peran, alur, aturan bisnis, dan state mahasiswa |
| [docs/deploy-render.md](docs/deploy-render.md) | Panduan deploy Docker penuh |

## Deploy

`Dockerfile` membangun image produksi lengkap: aset frontend dikompilasi, PHP dijalankan lewat Apache dengan mod_php, dan LibreOffice disertakan untuk konversi dokumen. `render.yaml` mendeklarasikan dua service — aplikasi dan MySQL — untuk Render Blueprint.

Platform lain yang membaca `Dockerfile` juga bisa dipakai, tetapi service database harus dibuat sendiri: berkas blueprint itu hanya dibaca Render.
