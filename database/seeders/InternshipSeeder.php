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
                'description' => 'Bergabung dengan tim engineering Gojek untuk mengembangkan fitur-fitur baru pada platform menggunakan arsitektur microservices.',
                'capacity' => '3 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengembangkan fitur baru pada platform Gojek menggunakan microservices architecture.',
                    'Menulis unit test dan integration test untuk memastikan kualitas kode.',
                    'Berkolaborasi dengan tim Product dan Design dalam sprint planning.',
                    'Melakukan code review dan pair programming dengan senior engineers.',
                ],
                'skills' => [
                    'Go/Java',
                    'REST API',
                    'Git',
                    'Problem Solving',
                ],
                'requirements' => [
                    'Memiliki pemahaman dasar tentang data structures dan algorithms.',
                    'Familiar dengan version control (Git) dan CI/CD pipeline.',
                    'Mampu bekerja dalam tim agile.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Informatika, Ilmu Komputer, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bumi Resources Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG0SYE8ZJYMJ1GE94NSP8J.png',
                'position' => 'Junior Data Analyst',
                'description' => 'Bergabung dengan tim Marketing Telkomsel untuk menyusun strategi konten dan kampanye digital.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Ilmu Komunikasi',
                    'Manajemen',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Menyusun strategi konten media sosial untuk brand Telkomsel.',
                    'Membantu perencanaan dan eksekusi kampanye marketing digital.',
                    'Melakukan analisis kompetitor dan market research.',
                    'Membuat laporan performa kampanye secara berkala.',
                ],
                'skills' => [
                    'Content Strategy',
                    'Social Media',
                    'Analytics',
                    'Copywriting',
                ],
                'requirements' => [
                    'Memiliki pemahaman tentang digital marketing dan social media trends.',
                    'Kreatif dan mampu menulis copy yang menarik.',
                    'Familiar dengan tools analytics (Google Analytics, Meta Business Suite).',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Ilmu Komunikasi, Marketing, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Energi Mega Persada Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG37BH0PJAVEAJFT1GVY9T.webp',
                'position' => 'Junior IT Support / Application Support',
                'description' => 'Bekerja dengan tim Product Design Traveloka untuk merancang antarmuka pengguna yang intuitif.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
                    'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
                    'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
                    'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
                ],
                'skills' => [
                    'Figma Specialist',
                    'UX Research',
                    'Prototyping',
                    'Critical Thinking',
                ],
                'requirements' => [
                    'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
                    'Memahami dasar-dasar desain visual (typography, color theory, layout).',
                    'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrieland Development Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG4NPADEJR3ZGAVEWSRK5F.png',
                'position' => 'Business Development Analyst',
                'description' => 'Bergabung dengan tim Data Analytics Microsoft Indonesia untuk menganalisis dataset dan membuat dashboard.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Informatika',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Menganalisis dataset besar untuk menghasilkan insight bisnis yang actionable.',
                    'Membuat dashboard dan visualisasi data menggunakan Power BI.',
                    'Mendukung tim product dalam A/B testing dan eksperimen.',
                    'Menulis query SQL untuk mengekstrak dan memanipulasi data.',
                ],
                'skills' => [
                    'SQL',
                    'Power BI',
                    'Python',
                    'Statistical Analysis',
                ],
                'requirements' => [
                    'Memiliki kemampuan analisis data yang kuat.',
                    'Familiar dengan SQL dan setidaknya satu bahasa pemrograman (Python/R).',
                    'Mampu mengkomunikasikan insight data secara jelas.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Matematika, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Bakrie Sumatera Plantations Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG72D76AHA2T4ZGDA03C9G.png',
                'position' => 'Management Information System Officer',
                'description' => 'Mengembangkan pipeline data dan dashboard monitoring KPI untuk tim regional Google Indonesia.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Informatika',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Mengembangkan pipeline data untuk analisis performa produk Google di Indonesia.',
                    'Membangun dashboard otomatis untuk monitoring KPI tim regional.',
                    'Berkolaborasi dengan tim engineering untuk data quality improvement.',
                ],
                'skills' => [
                    'BigQuery',
                    'Python',
                    'Looker Studio',
                    'Data Visualization',
                ],
                'requirements' => [
                    'Familiar dengan Google Cloud Platform (BigQuery, Looker).',
                    'Memiliki pengalaman menggunakan Python untuk data analysis.',
                    'Mampu bekerja secara mandiri maupun dalam tim.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ilmu Komputer, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Darma Henwa Tbk',
                'logo_path' => 'logo-perusahaan/01KZRG8YG5RPJCAQ12FHCP7FDN.png',
                'position' => 'Data Analyst Intern',
                'description' => 'Bergabung dengan tim analytics BCA untuk menganalisis data transaksi nasabah dan mendukung risk management.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'study_programs' => [
                    'Akuntansi',
                    'Manajemen',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Melakukan analisis data transaksi nasabah untuk identifikasi pola dan tren.',
                    'Membuat laporan dan dashboard performa bisnis untuk manajemen.',
                    'Mendukung tim risk management dalam pemodelan data.',
                    'Mengoptimalkan proses reporting melalui otomatisasi.',
                ],
                'skills' => [
                    'SQL',
                    'Excel Advanced',
                    'Tableau',
                    'Financial Analysis',
                ],
                'requirements' => [
                    'Memiliki ketertarikan di bidang perbankan dan keuangan.',
                    'Mampu mengolah data dalam volume besar dengan akurasi tinggi.',
                    'Memiliki kemampuan presentasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ekonomi, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT VKTR Teknologi Mobilitas Tbk',
                'logo_path' => 'logo-perusahaan/01KZRGBM2SFZCNRKDB3D1WEN11.png',
                'position' => 'Data Analyst Intern',
                'description' => 'Bergabung dengan tim Business Intelligence Indofood untuk menganalisis data penjualan dan distribusi produk.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Sistem Informasi',
                    'Teknik Industri',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Menganalisis data penjualan dan distribusi produk Indofood secara nasional.',
                    'Membuat laporan mingguan performa sales per region.',
                    'Mendukung tim supply chain dalam forecasting demand.',
                    'Membangun visualisasi data untuk presentasi ke stakeholder.',
                ],
                'skills' => [
                    'Excel Advanced',
                    'SQL',
                    'Power BI',
                    'Supply Chain Basics',
                ],
                'requirements' => [
                    'Memiliki kemampuan analisis yang kuat dan detail-oriented.',
                    'Familiar dengan tools visualisasi data (Tableau/Power BI).',
                    'Mampu bekerja di lingkungan FMCG yang dinamis.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Industri, Statistika, Manajemen, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'company_name' => 'PT Kaltim Prima Coal Tbk',
                'logo_path' => 'logo-perusahaan/01KZRGDK065CE7YF3K0WM64SCR.png',
                'position' => 'UI/UX Designer Intern',
                'description' => 'Posisi kedua di kantor Traveloka Tangerang untuk tim Product Design.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'study_programs' => [
                    'Informatika',
                    'Sistem Informasi',
                ],
                'start_date' => '2026-12-01',
                'job_description' => [
                    'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
                    'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
                    'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
                    'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
                ],
                'skills' => [
                    'Figma Specialist',
                    'UX Research',
                    'Prototyping',
                    'Critical Thinking',
                ],
                'requirements' => [
                    'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
                    'Memahami dasar-dasar desain visual (typography, color theory, layout).',
                    'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
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
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Sipil.',
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
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Lingkungan.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => '2026-12-31',
                'is_active' => true,
            ],
        ];
    }
}
