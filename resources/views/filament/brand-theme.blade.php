{{--
    Sidebar maroon Universitas Bakrie untuk panel admin.

    Disisipkan lewat render hook, bukan lewat `php artisan make:filament-theme`.
    Tema kustom menuntut entri Vite dan konfigurasi Tailwind tersendiri, plus
    penyesuaian tiap Filament naik versi; untuk sekadar mewarnai satu permukaan
    itu terlalu mahal. Kelas yang dipakai di bawah (fi-sidebar dan turunannya)
    adalah kaitan CSS yang memang disediakan Filament untuk keperluan ini.

    Area konten sengaja dibiarkan terang. Kartu, tabel, dan badge Filament
    semuanya dirancang untuk latar terang; melatarinya maroon akan membuatnya
    tampak rusak, bukan berkarakter. Sidebar adalah permukaan terbesar yang
    bisa diwarnai tanpa melawan komponennya sendiri -- dan hasilnya sejalan
    dengan portal mahasiswa, yang juga bersidebar maroon dengan konten terang.
--}}
<style>
    :root {
        --sipmag-maroon: #682828;
        --sipmag-maroon-gelap: #501e1e;
        --sipmag-maroon-terang: #7d3434;
    }

    .fi-sidebar,
    .fi-sidebar-header {
        background-color: var(--sipmag-maroon) !important;
    }

    /* Garis pemisah bawaan berwarna gelap, tidak terlihat di atas maroon. */
    .fi-sidebar-header {
        --tw-ring-color: rgb(255 255 255 / 0.12) !important;
        box-shadow: none !important;
    }

    /* Nama grup: cukup terbaca, tetap satu tingkat di bawah item navigasinya. */
    .fi-sidebar-group-label {
        color: rgb(255 255 255 / 0.6) !important;
    }

    .fi-sidebar-item-label,
    .fi-sidebar-item-icon,
    .fi-sidebar-group-icon,
    .fi-sidebar-group-collapse-button {
        color: rgb(255 255 255 / 0.85) !important;
    }

    .fi-sidebar-item-button:hover {
        background-color: var(--sipmag-maroon-terang) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: var(--sipmag-maroon-gelap) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-label,
    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #ffffff !important;
    }

    /* Tombol menciutkan sidebar ikut di atas maroon. */
    .fi-sidebar-header .fi-icon-btn {
        color: rgb(255 255 255 / 0.7) !important;
    }

    .fi-sidebar-header .fi-icon-btn:hover {
        background-color: var(--sipmag-maroon-terang) !important;
        color: #ffffff !important;
    }
</style>
