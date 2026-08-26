# Deploy ke Render (Docker penuh)

Blueprint di [`render.yaml`](../render.yaml) membuat dua service:

| Service         | Tipe   | Isi                                                    |
| --------------- | ------ | ------------------------------------------------------ |
| `sipmag`        | `web`  | Laravel + Apache, dibangun dari [`Dockerfile`](../Dockerfile) |
| `sipmag-mysql`  | `pserv`| MySQL 8.4, privat -- tidak terjangkau dari internet     |

Keduanya memakai persistent disk, jadi **butuh instance berbayar**: Render tidak
menyediakan disk di paket free.

## Langkah deploy

1. Push repo ini ke GitHub/GitLab.
2. Render Dashboard → **New** → **Blueprint**, arahkan ke repo ini.
3. Render membaca `render.yaml` dan menanyakan satu nilai: **`APP_KEY`**.
   Hasilkan dulu secara lokal, salin apa adanya termasuk awalan `base64:`:

   ```bash
   php artisan key:generate --show
   ```

4. **Apply**. Deploy pertama memakan waktu agak lama karena image memasang
   LibreOffice.
5. Setelah service hijau, isi data awal lewat Shell milik service `sipmag`:

   ```bash
   php artisan db:seed --class=TestingAccountsSeeder --force && php artisan db:seed --class=PurgeDemoStudentsSeeder --force && php artisan db:seed --class=InternshipSeeder --force
   ```

Migrasi **tidak** perlu dijalankan manual: entrypoint menjalankan
`php artisan migrate --force` setiap container start.

## Hal yang mudah salah

**`APP_URL` tidak diisi di `render.yaml`, dan itu disengaja.** Entrypoint
mengisinya dari `RENDER_EXTERNAL_URL` saat container start supaya selalu sama
persis dengan origin yang dibuka browser. Kalau berbeda, unggah berkas di panel
Filament menggantung tanpa pesan error dan sesi API ikut rusak. Variabel itu
hanya tersedia saat runtime, sehingga `config:cache` juga dijalankan di
entrypoint, bukan saat image dibangun.

**Pakai domain kustom?** Isi `APP_URL` dan `SANCTUM_STATEFUL_DOMAINS` manual di
dashboard. Contoh: `APP_URL=https://magang.bakrie.ac.id` dan
`SANCTUM_STATEFUL_DOMAINS=magang.bakrie.ac.id` (tanpa skema). Kalau
`SANCTUM_STATEFUL_DOMAINS` salah, seluruh permintaan API dari SPA ditolak
sebagai lintas domain dan mahasiswa gagal login.

**Disk di-mount di `storage/app`, bukan `storage/`.** Cache dan log framework
sengaja dibiarkan di filesystem container karena memang sementara. Konsekuensi
yang perlu diingat: berkas bawaan repo (logo perusahaan) tertutup oleh mount
saat deploy pertama, jadi entrypoint menyalinnya dari cadangan di dalam image.
Penyalinan memakai `cp -n` sehingga tidak pernah menimpa unggahan pengguna.

**Service tidak bisa di-scale lebih dari 1 instance.** Unggahan disimpan di disk
lokal (`FILESYSTEM_DISK=local`), jadi instance kedua tidak akan melihat berkas
milik instance pertama. Untuk aplikasi ini tidak masalah; kalau suatu saat perlu
scale, pindahkan penyimpanan ke S3 dan ubah 8 pemanggilan disk `'local'` di
controller.

**LibreOffice wajib ada di image.** Seluruh surat (Form 1 & Form 2) dibuat dengan
merender template DOCX lewat `soffice --convert-to pdf`. Tanpa paket itu,
unduh surat gagal dengan 503 -- persis kegagalan yang terjadi waktu aplikasi ini
dideploy dengan buildpack otomatis, bukan Docker.

## Backup database

MySQL berjalan sebagai service sendiri di atas disk, jadi **tidak ada backup
otomatis** seperti Render Postgres. Ambil dump berkala lewat Shell service
`sipmag`:

```bash
mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > /tmp/sipmag-backup.sql
```

Lalu unduh berkasnya keluar dari Render.

## Pengembangan lokal tidak berubah

`compose.yaml` dan `Dockerfile.dev` tetap seperti semula dan tidak dipakai
Render. Alur lokal masih:

```bash
docker compose up -d
```
