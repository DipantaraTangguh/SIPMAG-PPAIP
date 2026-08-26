#!/bin/sh
# Bootstrap container saat start di Render.
#
# Semua yang bergantung pada nilai runtime dikerjakan di sini, bukan saat image
# dibangun: RENDER_EXTERNAL_URL, PORT, dan kredensial database baru ada setelah
# container jalan.
set -e

PORT="${PORT:-10000}"

# ── Apache mendengarkan di port yang ditentukan Render ───────────────────────
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/__PORT__/${PORT}/" /etc/apache2/sites-available/000-default.conf

# ── APP_URL harus sama persis dengan origin yang dibuka browser ──────────────
# Kalau berbeda, unggah berkas di panel Filament menggantung tanpa pesan error
# dan sesi API ikut rusak. RENDER_EXTERNAL_URL hanya tersedia saat runtime,
# jadi tidak bisa dibakukan ke dalam image.
if [ -z "${APP_URL}" ] && [ -n "${RENDER_EXTERNAL_URL}" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

# Sanctum memakai sesi cookie, bukan token. Host aplikasi wajib terdaftar di
# sini atau setiap permintaan API dari SPA ditolak sebagai lintas domain.
if [ -z "${SANCTUM_STATEFUL_DOMAINS}" ] && [ -n "${RENDER_EXTERNAL_HOSTNAME}" ]; then
    export SANCTUM_STATEFUL_DOMAINS="${RENDER_EXTERNAL_HOSTNAME}"
fi

# ── Persistent disk ──────────────────────────────────────────────────────────
# storage/app ditimpa mount disk, jadi saat deploy pertama isinya kosong --
# termasuk logo perusahaan yang ikut di-commit di repo. Berkas bawaan disalin
# dari cadangan di image, tanpa menimpa yang sudah ada supaya unggahan
# pengguna tidak pernah tertimpa saat deploy ulang.
mkdir -p storage/app/public
if [ -d /opt/seed-storage/public ]; then
    cp -rn /opt/seed-storage/public/. storage/app/public/ 2>/dev/null || true
fi

# Direktori ini di luar storage/app sehingga tidak ikut ter-mount, tapi tetap
# dibuat agar container baru tidak gagal menulis cache/log.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

ln -sfn ../storage/app/public public/storage
chown -R www-data:www-data storage bootstrap/cache

# ── Database ─────────────────────────────────────────────────────────────────
# MySQL jalan sebagai private service terpisah dan bisa saja belum siap
# menerima koneksi saat web service sudah start.
if [ -n "${DB_HOST}" ]; then
    printf 'Menunggu MySQL di %s:%s' "${DB_HOST}" "${DB_PORT:-3306}"
    i=0
    until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" --silent 2>/dev/null; do
        i=$((i + 1))
        if [ "$i" -ge 60 ]; then
            echo " gagal."
            echo "MySQL tidak merespons setelah 60 percobaan. Periksa DB_HOST/DB_PORT dan status private service-nya." >&2
            exit 1
        fi
        printf '.'
        sleep 2
    done
    echo " siap."
fi

php artisan migrate --force

# ── Cache konfigurasi ────────────────────────────────────────────────────────
# Dijalankan di sini, bukan saat build, supaya APP_URL di atas ikut terbaca.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
