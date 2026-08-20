<?php

namespace Database\Seeders;

use App\Models\Internship;
use Illuminate\Database\Seeder;

/**
 * Lowongan mitra dari perusahaan grup Bakrie, lengkap dengan logo yang
 * diunggah lewat panel admin.
 *
 * Idempotent dan aman dijalankan ulang: baris dicocokkan lewat kombinasi
 * nama perusahaan + posisi + lokasi. Kolom logo_path SENGAJA tidak ditimpa
 * kalau barisnya sudah punya logo, supaya logo yang diunggah manual lewat
 * Filament tidak hilang saat seeder dijalankan lagi.
 *
 * Berkas logonya ikut di-commit di storage/app/public/logo-perusahaan
 * supaya path di bawah tetap valid di environment lain.
 */
class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            $key = [
                'company_name' => $row['company_name'],
                'position' => $row['position'],
                'location' => $row['location'],
            ];

            $existing = Internship::where($key)->first();

            // Logo yang sudah ada menang atas nilai bawaan seeder.
            if ($existing?->logo_path) {
                $row['logo_path'] = $existing->logo_path;
            }

            Internship::updateOrCreate($key, $row);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'company_name' => 'PT Bakrie & Brothers Tbk',
                'logo_path' => 'logo-perusahaan/01KZRFXDY470HGTREXBKPPJJQD.png',
                'position' => 'IT Business Analyst / System Analyst',
                'description' => 'Mendampingi tim Teknologi Informasi dalam menerjemahkan kebutuhan unit bisnis menjadi rancangan sistem yang siap dikembangkan.',
                'capacity' => '3 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Menggali dan mendokumentasikan kebutuhan unit bisnis terhadap sistem internal perusahaan.',
                    'Menyusun dokumen analisis kebutuhan, proses bisnis, dan alur data.',
                    'Membantu pelaksanaan pengujian penerimaan pengguna sebelum sistem dirilis.',
                    'Menjembatani komunikasi antara pengguna dan tim pengembang.',
                ],
                'skills' => [
                    'Analisis Proses Bisnis',
                    'UML / BPMN',
                    'SQL',
                    'Dokumentasi Teknis',
                ],
                'requirements' => [
                    'Memahami konsep basis data dan pemetaan proses bisnis.',
                    'Mampu menyusun dokumentasi teknis yang runut dan mudah dipahami.',
                    'Terbiasa berkomunikasi dengan pengguna non-teknis.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Informatika, Sistem Informasi, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bumi Resources Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG0SYE8ZJYMJ1GE94NSP8J.png',
                'position' => 'Junior Data Analyst',
                'description' => 'Mengolah data operasional dan korporat menjadi laporan yang mudah dibaca manajemen serta pemangku kepentingan.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Ilmu Komunikasi',
                    'Manajemen',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengumpulkan dan merapikan data produksi serta penjualan dari unit usaha terkait.',
                    'Menyusun laporan berkala dan materi presentasi untuk manajemen.',
                    'Membantu penyiapan data untuk laporan tahunan dan komunikasi korporat.',
                    'Membuat visualisasi data agar temuan mudah dipahami pembaca non-teknis.',
                ],
                'skills' => [
                    'Excel Advanced',
                    'Visualisasi Data',
                    'Penulisan Laporan',
                    'Riset',
                ],
                'requirements' => [
                    'Teliti dalam mengolah data bervolume besar.',
                    'Mampu menyampaikan temuan data secara jelas, lisan maupun tulisan.',
                    'Terbiasa bekerja dengan tenggat pelaporan yang tetap.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Manajemen, Ilmu Komunikasi, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Energi Mega Persada Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG37BH0PJAVEAJFT1GVY9T.webp',
                'position' => 'Junior IT Support / Application Support',
                'description' => 'Mendukung operasional harian pengguna dan aplikasi internal di lingkungan bisnis minyak dan gas.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Menangani permintaan bantuan pengguna atas perangkat dan aplikasi internal.',
                    'Melakukan pemeriksaan berkala dan pemantauan ketersediaan aplikasi.',
                    'Mencatat serta menindaklanjuti tiket gangguan sampai tuntas.',
                    'Menyusun panduan singkat pemakaian aplikasi untuk pengguna.',
                ],
                'skills' => [
                    'Troubleshooting',
                    'Help Desk / Ticketing',
                    'Jaringan Dasar',
                    'SQL Dasar',
                ],
                'requirements' => [
                    'Memahami dasar jaringan, sistem operasi, dan basis data.',
                    'Sabar dan komunikatif saat membantu pengguna non-teknis.',
                    'Rapi dalam pencatatan tiket dan dokumentasi penanganan.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Informatika, Sistem Informasi, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrieland Development Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG4NPADEJR3ZGAVEWSRK5F.png',
                'position' => 'Business Development Analyst',
                'description' => 'Mendukung tim Business Development dalam analisis pasar dan kajian kelayakan proyek properti.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Informatika',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengumpulkan data pasar properti dan aktivitas pesaing di wilayah sasaran.',
                    'Membantu penyusunan studi kelayakan dan proyeksi awal untuk proyek baru.',
                    'Mengolah data penjualan dan tingkat hunian menjadi laporan manajemen.',
                    'Menyiapkan materi presentasi untuk rapat pengembangan bisnis.',
                ],
                'skills' => [
                    'Riset Pasar',
                    'SQL',
                    'Excel Advanced',
                    'Analisis Kelayakan',
                ],
                'requirements' => [
                    'Memiliki kemampuan analisis kuantitatif yang kuat.',
                    'Terbiasa mengolah data lewat spreadsheet atau kueri sederhana.',
                    'Mampu menarik kesimpulan yang ringkas dari data mentah.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Sistem Informasi, Informatika, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrie Sumatera Plantations Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG72D76AHA2T4ZGDA03C9G.png',
                'position' => 'Management Information System Officer',
                'description' => 'Menjaga aliran data dari kebun dan pabrik agar menjadi laporan manajemen yang siap dipakai.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Informatika',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengumpulkan dan memvalidasi data operasional dari unit kebun serta pabrik.',
                    'Membangun laporan dan dasbor pemantauan indikator kinerja utama.',
                    'Membantu perawatan basis data dan integrasi antar sistem internal.',
                    'Mendokumentasikan alur data dan prosedur pelaporan.',
                ],
                'skills' => [
                    'SQL',
                    'Power BI',
                    'Excel Advanced',
                    'Pemodelan Data',
                ],
                'requirements' => [
                    'Menguasai dasar basis data relasional dan kueri SQL.',
                    'Familiar dengan tools pelaporan atau visualisasi data.',
                    'Mampu bekerja mandiri dengan data dari banyak sumber.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Sistem Informasi, Informatika, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Darma Henwa Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG8YG5RPJCAQ12FHCP7FDN.png',
                'position' => 'Data Analyst Intern',
                'description' => 'Menganalisis data produksi dan biaya jasa pertambangan untuk mendukung pengendalian kinerja proyek.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'study_programs' => [
                    'Akuntansi',
                    'Manajemen',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengolah data produksi alat berat dan capaian proyek menjadi laporan berkala.',
                    'Membantu analisis biaya operasional terhadap anggaran proyek.',
                    'Menyusun laporan kinerja proyek untuk manajemen.',
                    'Merapikan dan mengotomatiskan proses pelaporan yang berulang.',
                ],
                'skills' => [
                    'Excel Advanced',
                    'SQL',
                    'Analisis Biaya',
                    'Pelaporan Keuangan',
                ],
                'requirements' => [
                    'Memahami dasar akuntansi biaya dan pembacaan laporan keuangan.',
                    'Mampu mengolah data bervolume besar dengan akurasi tinggi.',
                    'Teliti dan terbiasa dengan tenggat pelaporan bulanan.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Akuntansi, Manajemen, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT VKTR Teknologi Mobilitas Tbk',
                'logo_path' => 'logo-perusahaan/01KZRGBM2SFZCNRKDB3D1WEN11.png',
                'position' => 'Data Analyst Intern',
                'description' => 'Mengolah data produksi dan purna jual kendaraan listrik untuk mendukung perbaikan proses.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Teknik Industri',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengolah data lini produksi dan hasil uji kendaraan listrik.',
                    'Menyusun laporan performa produksi serta temuan purna jual.',
                    'Membantu analisis rantai pasok komponen dan perencanaan kebutuhan.',
                    'Membangun visualisasi data untuk rapat evaluasi produksi.',
                ],
                'skills' => [
                    'Excel Advanced',
                    'SQL',
                    'Power BI',
                    'Analisis Proses Produksi',
                ],
                'requirements' => [
                    'Memahami dasar proses produksi dan pengendalian kualitas.',
                    'Familiar dengan tools pengolahan dan visualisasi data.',
                    'Mampu bekerja rapi dengan data lapangan yang belum terstruktur.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Industri, Sistem Informasi, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Kaltim Prima Coal Tbk',
                'logo_path' => 'logo-perusahaan/01KZRGDK065CE7YF3K0WM64SCR.png',
                'position' => 'UI/UX Designer Intern',
                'description' => 'Merancang antarmuka aplikasi internal agar mudah dipakai staf kantor maupun pengguna di area operasi.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Merancang antarmuka aplikasi internal untuk kebutuhan operasional perusahaan.',
                    'Melakukan wawancara pengguna dan pengujian kebergunaan pada aplikasi berjalan.',
                    'Membuat wireframe, prototipe, dan desain final menggunakan Figma.',
                    'Menjaga konsistensi desain melalui design system internal.',
                ],
                'skills' => [
                    'Figma',
                    'UX Research',
                    'Prototyping',
                    'Design System',
                ],
                'requirements' => [
                    'Memiliki portofolio desain antarmuka yang menunjukkan proses pemecahan masalah.',
                    'Memahami dasar desain visual: tipografi, warna, dan tata letak.',
                    'Mampu menerima dan menindaklanjuti masukan dari pengguna.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Informatika, Sistem Informasi, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Tangerang',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],

            // Empat lowongan di bawah menutup program studi yang belum
            // kebagian di atas -- Ilmu Politik, Teknik Sipil, Ilmu &
            // Teknologi Pangan, dan Teknik Lingkungan -- supaya mahasiswa
            // dari kesepuluh prodi punya lowongan mitra yang bisa dilamar.
            [
                'company_name' => 'PT Bakrie & Brothers Tbk',
                'logo_path' => 'logo-perusahaan/01KZRFXDY470HGTREXBKPPJJQD.png',
                'position' => 'Corporate Affairs & Public Policy Intern',
                'description' => 'Mendukung tim Corporate Affairs dalam memantau kebijakan publik dan menjaga hubungan dengan pemangku kepentingan.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Ilmu Politik',
                    'Ilmu Komunikasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Memantau isu kebijakan publik dan regulasi yang berdampak pada lini bisnis perusahaan.',
                    'Menyusun ringkasan kebijakan dan analisis pemangku kepentingan untuk manajemen.',
                    'Membantu persiapan pertemuan dengan mitra pemerintah dan asosiasi industri.',
                    'Mendokumentasikan kegiatan hubungan kelembagaan perusahaan.',
                ],
                'skills' => [
                    'Analisis Kebijakan',
                    'Riset',
                    'Penulisan Laporan',
                    'Komunikasi Publik',
                ],
                'requirements' => [
                    'Mengikuti isu politik, regulasi, dan pemerintahan di Indonesia.',
                    'Mampu menulis ringkasan analitis yang padat dan terstruktur.',
                    'Teliti dalam mengolah sumber data sekunder.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Ilmu Politik, Ilmu Komunikasi, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrieland Development Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG4NPADEJR3ZGAVEWSRK5F.png',
                'position' => 'Site Engineer Intern',
                'description' => 'Mendampingi tim proyek di lapangan untuk pengawasan mutu pekerjaan struktur dan pelaporan progres konstruksi.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Teknik Sipil',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mendampingi pengawasan pekerjaan struktur dan arsitektur di lokasi proyek.',
                    'Menyusun laporan progres harian dan mingguan bersama site manager.',
                    'Membantu pemeriksaan volume pekerjaan terhadap gambar kerja.',
                    'Mendokumentasikan temuan lapangan terkait mutu dan keselamatan kerja.',
                ],
                'skills' => [
                    'AutoCAD',
                    'Pembacaan Gambar Kerja',
                    'Quantity Take-off',
                    'K3 Konstruksi',
                ],
                'requirements' => [
                    'Memahami dasar mekanika rekayasa dan teknologi bahan konstruksi.',
                    'Mampu membaca gambar kerja struktur dan arsitektur.',
                    'Bersedia ditempatkan di lokasi proyek.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Sipil atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrie Sumatera Plantations Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG72D76AHA2T4ZGDA03C9G.png',
                'position' => 'Quality Assurance & Food Safety Intern',
                'description' => 'Bergabung dengan tim Quality Assurance untuk pengujian mutu produk turunan kelapa sawit dan penerapan standar keamanan pangan.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Ilmu & Teknologi Pangan',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Melakukan pengujian mutu bahan baku dan produk jadi di laboratorium.',
                    'Membantu penerapan dan pemutakhiran dokumen HACCP serta GMP.',
                    'Mencatat dan menganalisis data hasil uji untuk laporan mutu bulanan.',
                    'Mendukung persiapan audit internal keamanan pangan.',
                ],
                'skills' => [
                    'Analisis Laboratorium',
                    'HACCP',
                    'GMP',
                    'Pengendalian Mutu',
                ],
                'requirements' => [
                    'Memahami prinsip keamanan pangan dan pengendalian mutu.',
                    'Terbiasa bekerja dengan prosedur laboratorium yang baku.',
                    'Teliti dalam pencatatan data hasil pengujian.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Ilmu & Teknologi Pangan, Teknologi Hasil Pertanian, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Kaltim Prima Coal Tbk',
                'logo_path' => 'logo-perusahaan/01KZRGDK065CE7YF3K0WM64SCR.png',
                'position' => 'Environmental Compliance Intern',
                'description' => 'Mendukung tim Environment dalam pemantauan kualitas lingkungan dan pelaporan kepatuhan terhadap dokumen AMDAL.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Teknik Lingkungan',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Membantu pemantauan kualitas air, udara, dan pengelolaan limbah di area operasi.',
                    'Mengolah data hasil pemantauan menjadi laporan kepatuhan lingkungan berkala.',
                    'Mendukung penyusunan laporan pelaksanaan RKL-RPL.',
                    'Membantu sosialisasi program lingkungan kepada unit kerja terkait.',
                ],
                'skills' => [
                    'Pemantauan Kualitas Lingkungan',
                    'Pengelolaan Limbah',
                    'AMDAL / RKL-RPL',
                    'Pengolahan Data',
                ],
                'requirements' => [
                    'Memahami regulasi lingkungan hidup dan dokumen AMDAL.',
                    'Mampu mengolah data pemantauan menggunakan spreadsheet.',
                    'Bersedia melakukan pengambilan sampel di lapangan.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Lingkungan atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
        ];
    }
}
