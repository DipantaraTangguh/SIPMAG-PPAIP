#!/bin/sh
# Bootstrap container saat start di Render.
#
# Semua yang bergantung pada nilai runtime dikerjakan di sini, bukan saat image
# dibangun: RENDER_EXTERNAL_URL, PORT, dan kredensial database baru ada setelah
# container jalan.
set -e

PORT="${PORT:-10000}"

# ── APP_URL harus sama persis dengan origin yang dibuka browser ──────────────
# Kalau berbeda, unggah berkas di panel Filament menggantung tanpa pesan error
# dan sesi API ikut rusak. Nama variabelnya berbeda tiap platform dan baru ada
# saat runtime, jadi tidak bisa dibakukan ke dalam image:
#   Render  -> RENDER_EXTERNAL_URL (URL lengkap dengan skema)
#   Railway -> RAILWAY_PUBLIC_DOMAIN (hostname saja, tanpa skema)
if [ -z "${APP_URL}" ]; then
    if [ -n "${RENDER_EXTERNAL_URL}" ]; then
        export APP_URL="${RENDER_EXTERNAL_URL}"
    elif [ -n "${RAILWAY_PUBLIC_DOMAIN}" ]; then
        export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
    fi
fi

# Sanctum memakai sesi cookie, bukan token. Host aplikasi wajib terdaftar di
# sini atau setiap permintaan API dari SPA ditolak sebagai lintas domain --
# mahasiswa gagal login meski kata sandinya benar. Diisi hostname tanpa skema.
if [ -z "${SANCTUM_STATEFUL_DOMAINS}" ]; then
    if [ -n "${RENDER_EXTERNAL_HOSTNAME}" ]; then
        export SANCTUM_STATEFUL_DOMAINS="${RENDER_EXTERNAL_HOSTNAME}"
    elif [ -n "${RAILWAY_PUBLIC_DOMAIN}" ]; then
        export SANCTUM_STATEFUL_DOMAINS="${RAILWAY_PUBLIC_DOMAIN}"
    fi
fi

# Dicetak setelah APP_URL dan SANCTUM_STATEFUL_DOMAINS diselesaikan supaya yang
# tampil adalah nilai yang benar-benar dipakai, bukan yang belum terisi. Satu
# baris ini cukup untuk memastikan konfigurasi terbaca benar, tanpa perlu
# membedah jejak tumpukan.
echo "SIPMAG boot: PORT=${PORT}" \
     "DB_CONNECTION=${DB_CONNECTION:-<bawaan>}" \
     "DB_HOST=${DB_HOST:-<kosong>}" \
     "DB_PORT=${DB_PORT:-<kosong>}" \
     "DB_DATABASE=${DB_DATABASE:-<kosong>}" \
     "APP_URL=${APP_URL:-<kosong>}" \
     "SANCTUM=${SANCTUM_STATEFUL_DOMAINS:-<kosong>}"

# ── Server mendengarkan di port yang ditentukan platform ─────────────────────
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/__PORT__/${PORT}/" /etc/apache2/sites-available/000-default.conf

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
# Diperiksa sebelum menyentuh artisan. Tanpa penjagaan ini, DB_HOST yang kosong
# lolos begitu saja lalu `migrate` gagal dengan galat PDO "No such file or
# directory" (PDO jatuh ke mode unix socket saat host kosong) plus jejak
# tumpukan puluhan baris -- menyesatkan, karena akar masalahnya cuma variabel
# yang belum diisi. Containernya lalu mati berulang dan health check platform
# tidak pernah hijau.
if [ "${DB_CONNECTION:-}" = "mysql" ] && [ -z "${DB_HOST}" ]; then
    echo "FATAL: DB_CONNECTION=mysql tetapi DB_HOST kosong." >&2
    echo "Isi DB_HOST, DB_PORT (3306, bukan port web), DB_DATABASE, DB_USERNAME, dan DB_PASSWORD." >&2
    echo "Di Railway, arahkan ke service MySQL-nya, misalnya DB_HOST=\${{MySQL.MYSQLHOST}}." >&2
    exit 1
fi

# MySQL jalan sebagai service terpisah dan bisa saja belum siap menerima
# koneksi saat web service sudah start.
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

# ── MPM Apache ───────────────────────────────────────────────────────────────
# mod_php hanya jalan di atas mpm_prefork. Kalau MPM lain ikut aktif, Apache
# menolak start dengan "More than one MPM loaded", tidak ada yang mendengarkan
# di port, dan health check platform menggantung sampai menyerah -- kegagalan
# yang menyesatkan karena seluruh langkah sebelumnya terlihat sukses.
#
# Ditegakkan saat start, bukan saat build, karena kondisinya pernah berbeda
# antara image yang dibangun lokal dan yang dibangun di platform.
mpm_aktif=$(ls /etc/apache2/mods-enabled/ 2>/dev/null | grep -c '^mpm_.*\.load$' || true)
if [ "${mpm_aktif}" != "1" ]; then
    echo "MPM Apache bermasalah: ${mpm_aktif} MPM aktif, seharusnya tepat 1 (mpm_prefork). Memperbaiki."
    ls /etc/apache2/mods-enabled/ | grep '^mpm_' || true
    a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
    a2enmod mpm_prefork >/dev/null 2>&1 || true
fi

# Gagalkan di sini dengan pesan Apache sendiri, bukan setelah exec, supaya
# alasannya tercetak utuh di log.
apache2ctl -t || {
    echo "FATAL: konfigurasi Apache tidak valid, lihat pesan di atas." >&2
    exit 1
}

exec apache2-foreground
