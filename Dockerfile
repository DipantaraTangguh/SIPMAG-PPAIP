# Image produksi untuk deploy Docker penuh di Render.
#
# Untuk pengembangan lokal pakai Dockerfile.dev lewat compose.yaml -- file ini
# sengaja terpisah supaya perubahan deploy tidak pernah mengganggu alur kerja
# lokal yang sudah jalan.

# ─────────────────────────────────────────────────────────────────────────────
# Tahap 1 -- bangun aset frontend.
#
# public/build di-gitignore, jadi harus dibangun di sini. Kalau tidak, halaman
# mahasiswa memuat tanpa CSS/JS sama sekali.
# ─────────────────────────────────────────────────────────────────────────────
FROM node:22-bookworm-slim AS assets

WORKDIR /app

# Layer dependensi dipisah supaya npm ci tidak terulang tiap kali kode berubah.
COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tsconfig.json ./
COPY resources ./resources

RUN npm run build

# ─────────────────────────────────────────────────────────────────────────────
# Tahap 2 -- runtime PHP + Apache.
#
# Dipakai Apache dengan mod_php, bukan `php artisan serve`. Server bawaan PHP
# itu single-threaded dan memang tidak ditujukan untuk produksi: satu unduhan
# PDF yang lambat akan memblokir semua permintaan lain.
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.4-apache AS runtime

# libreoffice-writer wajib: seluruh surat (Form 1 & Form 2) dibuat dengan
# merender template DOCX lewat `soffice --convert-to pdf`. Tanpa ini
# DocxToPdfRenderer mengembalikan null dan unduh surat gagal dengan 503 --
# persis kegagalan yang terjadi waktu dideploy tanpa Docker.
# Dipakai varian -writer, bukan paket libreoffice penuh, supaya image ringan.
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libreoffice-writer \
    fonts-liberation \
    default-mysql-client \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        zip \
        pdo_mysql \
        opcache

# opcache mati secara bawaan di image resmi PHP. Tanpa ini setiap request
# mem-parsing ulang seluruh Filament, yang berat di instance kecil.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Unggahan terbesar adalah berkas sidang (laporan + poster + 2 foto sekaligus),
# jadi batas bawaan 2M jelas kurang.
RUN { \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=25M'; \
        echo 'memory_limit=512M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Dependensi PHP dulu, terpisah dari kode aplikasi, supaya layer-nya bisa
# dipakai ulang selama composer.lock tidak berubah.
#
# --no-scripts dipakai karena skrip post-autoload-dump memanggil artisan,
# sementara kode aplikasinya belum disalin pada tahap ini.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --no-scripts \
        --no-autoloader

COPY . .
COPY --from=assets /app/public/build ./public/build

# Baru setelah kode lengkap: autoloader dioptimalkan dan aset Filament
# diterbitkan. filament:upgrade wajib dipanggil eksplisit karena
# post-autoload-dump dilewati di atas -- tanpa ini panel admin tampil tanpa
# CSS sama sekali.
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade

# Berkas bawaan repo (logo perusahaan) disimpan sebagai cadangan di luar
# storage/, karena storage/app akan ditimpa mount persistent disk saat runtime
# dan isinya jadi kosong. Entrypoint menyalin yang belum ada ke disk.
RUN mkdir -p /opt/seed-storage \
    && cp -r storage/app/public /opt/seed-storage/public

COPY docker/render/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/render/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && a2enmod rewrite headers \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
