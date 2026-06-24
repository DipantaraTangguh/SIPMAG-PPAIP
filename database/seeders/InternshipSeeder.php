<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $december2026StartDate = '2026-12-01';
        $december2026Deadline = '2026-12-31';

        $rows = [
            [
                'company_name' => 'PT. Gojek Tokopedia',
                'position' => 'Software Engineer Intern',
                'description' => 'Bergabung dengan tim engineering Gojek untuk mengembangkan fitur-fitur baru pada platform menggunakan arsitektur microservices.',
                'capacity' => '3 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Software Engineering',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Mengembangkan fitur baru pada platform Gojek menggunakan microservices architecture.',
                    'Menulis unit test dan integration test untuk memastikan kualitas kode.',
                    'Berkolaborasi dengan tim Product dan Design dalam sprint planning.',
                    'Melakukan code review dan pair programming dengan senior engineers.',
                ],
                'skills' => ['Go/Java', 'REST API', 'Git', 'Problem Solving'],
                'requirements' => [
                    'Memiliki pemahaman dasar tentang data structures dan algorithms.',
                    'Familiar dengan version control (Git) dan CI/CD pipeline.',
                    'Mampu bekerja dalam tim agile.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Informatika, Ilmu Komputer, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'Telkomsel',
                'position' => 'Marketing Communications Intern',
                'description' => 'Bergabung dengan tim Marketing Telkomsel untuk menyusun strategi konten dan kampanye digital.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Marketing & Communications',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Menyusun strategi konten media sosial untuk brand Telkomsel.',
                    'Membantu perencanaan dan eksekusi kampanye marketing digital.',
                    'Melakukan analisis kompetitor dan market research.',
                    'Membuat laporan performa kampanye secara berkala.',
                ],
                'skills' => ['Content Strategy', 'Social Media', 'Analytics', 'Copywriting'],
                'requirements' => [
                    'Memiliki pemahaman tentang digital marketing dan social media trends.',
                    'Kreatif dan mampu menulis copy yang menarik.',
                    'Familiar dengan tools analytics (Google Analytics, Meta Business Suite).',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Ilmu Komunikasi, Marketing, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'Traveloka Indonesia',
                'position' => 'UI/UX Designer Intern',
                'description' => 'Bekerja dengan tim Product Design Traveloka untuk merancang antarmuka pengguna yang intuitif.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Product Design',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
                    'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
                    'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
                    'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
                ],
                'skills' => ['Figma Specialist', 'UX Research', 'Prototyping', 'Critical Thinking'],
                'requirements' => [
                    'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
                    'Memahami dasar-dasar desain visual (typography, color theory, layout).',
                    'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'PT. Microsoft',
                'position' => 'Data Analyst Intern',
                'description' => 'Bergabung dengan tim Data Analytics Microsoft Indonesia untuk menganalisis dataset dan membuat dashboard.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'bidang' => 'Data Analytics',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Menganalisis dataset besar untuk menghasilkan insight bisnis yang actionable.',
                    'Membuat dashboard dan visualisasi data menggunakan Power BI.',
                    'Mendukung tim product dalam A/B testing dan eksperimen.',
                    'Menulis query SQL untuk mengekstrak dan memanipulasi data.',
                ],
                'skills' => ['SQL', 'Power BI', 'Python', 'Statistical Analysis'],
                'requirements' => [
                    'Memiliki kemampuan analisis data yang kuat.',
                    'Familiar dengan SQL dan setidaknya satu bahasa pemrograman (Python/R).',
                    'Mampu mengkomunikasikan insight data secara jelas.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Matematika, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'PT. Google Indonesia',
                'position' => 'Data Analyst Intern',
                'description' => 'Mengembangkan pipeline data dan dashboard monitoring KPI untuk tim regional Google Indonesia.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Data Analytics',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Mengembangkan pipeline data untuk analisis performa produk Google di Indonesia.',
                    'Membangun dashboard otomatis untuk monitoring KPI tim regional.',
                    'Berkolaborasi dengan tim engineering untuk data quality improvement.',
                ],
                'skills' => ['BigQuery', 'Python', 'Looker Studio', 'Data Visualization'],
                'requirements' => [
                    'Familiar dengan Google Cloud Platform (BigQuery, Looker).',
                    'Memiliki pengalaman menggunakan Python untuk data analysis.',
                    'Mampu bekerja secara mandiri maupun dalam tim.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ilmu Komputer, atau sejenisnya.',
                'sistem_kerja' => 'Hybrid',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'PT. Bank Central Asia',
                'position' => 'Data Analyst Intern',
                'description' => 'Bergabung dengan tim analytics BCA untuk menganalisis data transaksi nasabah dan mendukung risk management.',
                'capacity' => '2 Posisi',
                'duration' => '6 Bulan',
                'bidang' => 'Banking & Finance Analytics',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Melakukan analisis data transaksi nasabah untuk identifikasi pola dan tren.',
                    'Membuat laporan dan dashboard performa bisnis untuk manajemen.',
                    'Mendukung tim risk management dalam pemodelan data.',
                    'Mengoptimalkan proses reporting melalui otomatisasi.',
                ],
                'skills' => ['SQL', 'Excel Advanced', 'Tableau', 'Financial Analysis'],
                'requirements' => [
                    'Memiliki ketertarikan di bidang perbankan dan keuangan.',
                    'Mampu mengolah data dalam volume besar dengan akurasi tinggi.',
                    'Memiliki kemampuan presentasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ekonomi, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'PT. Indofood',
                'position' => 'Data Analyst Intern',
                'description' => 'Bergabung dengan tim Business Intelligence Indofood untuk menganalisis data penjualan dan distribusi produk.',
                'capacity' => '1 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Business Intelligence',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Menganalisis data penjualan dan distribusi produk Indofood secara nasional.',
                    'Membuat laporan mingguan performa sales per region.',
                    'Mendukung tim supply chain dalam forecasting demand.',
                    'Membangun visualisasi data untuk presentasi ke stakeholder.',
                ],
                'skills' => ['Excel Advanced', 'SQL', 'Power BI', 'Supply Chain Basics'],
                'requirements' => [
                    'Memiliki kemampuan analisis yang kuat dan detail-oriented.',
                    'Familiar dengan tools visualisasi data (Tableau/Power BI).',
                    'Mampu bekerja di lingkungan FMCG yang dinamis.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Industri, Statistika, Manajemen, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Jakarta Selatan',
                'deadline' => $december2026Deadline,
            ],
            [
                'company_name' => 'Traveloka Indonesia',
                'position' => 'UI/UX Designer Intern',
                'description' => 'Posisi kedua di kantor Traveloka Tangerang untuk tim Product Design.',
                'capacity' => '2 Posisi',
                'duration' => '3 Bulan',
                'bidang' => 'Product Design',
                'start_date' => $december2026StartDate,
                'job_description' => [
                    'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
                    'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
                    'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
                    'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
                ],
                'skills' => ['Figma Specialist', 'UX Research', 'Prototyping', 'Critical Thinking'],
                'requirements' => [
                    'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
                    'Memahami dasar-dasar desain visual (typography, color theory, layout).',
                    'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
                ],
                'minimum_education' => 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
                'sistem_kerja' => 'WFO (On-site)',
                'location' => 'Tangerang',
                'deadline' => $december2026Deadline,
            ],
        ];

        $now = now();
        $payload = array_map(function ($r) use ($now) {
            $r['job_description'] = json_encode($r['job_description']);
            $r['skills'] = json_encode($r['skills']);
            $r['requirements'] = json_encode($r['requirements']);
            $r['is_active'] = true;
            $r['created_at'] = $now;
            $r['updated_at'] = $now;

            return $r;
        }, $rows);

        DB::table('internships')->insert($payload);
    }
}
