/**
 * VacancyDetailPage.jsx
 * "Info Lowongan Magang" detail page — shown when clicking
 * "Lihat Detail →" on a vacancy card.
 * Portal Magang nav item is active.
 *
 * Route: /portal/vacancy/:id
 */
import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import DashboardLayout from '../../layouts/DashboardLayout';
import VacancyDetailHeader from '../../components/vacancy/VacancyDetailHeader';
import VacancyInfoSection from '../../components/vacancy/VacancyInfoSection';
import VacancyJobDescSection from '../../components/vacancy/VacancyJobDescSection';
import VacancyQualificationSection from '../../components/vacancy/VacancyQualificationSection';
import VacancyApplySidebar from '../../components/vacancy/VacancyApplySidebar';
import useVacancyDetail from '../../hooks/useVacancyDetail';

/* ── Mock data ─────────────────────────────────────── */
const mockVacanciesDB = {
    '1': {
        id: 1,
        companyName: 'PT. Gojek Tokopedia',
        position: 'Software Engineer Intern',
        logoColor: '#00AA5B',
        logoInitial: 'G',
        kapasitas: '3 Posisi',
        sistemKerja: 'Hybrid',
        durasi: '3 Bulan',
        bidang: 'Software Engineering',
        mulaiMagang: '1 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Mengembangkan fitur baru pada platform Gojek menggunakan microservices architecture.',
            'Menulis unit test dan integration test untuk memastikan kualitas kode.',
            'Berkolaborasi dengan tim Product dan Design dalam sprint planning.',
            'Melakukan code review dan pair programming dengan senior engineers.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Informatika, Ilmu Komputer, atau sejenisnya.',
        keahlianUtama: ['Go/Java', 'REST API', 'Git', 'Problem Solving'],
        persyaratan: [
            'Memiliki pemahaman dasar tentang data structures dan algorithms.',
            'Familiar dengan version control (Git) dan CI/CD pipeline.',
            'Mampu bekerja dalam tim agile.',
        ],
    },
    '2': {
        id: 2,
        companyName: 'Telkomsel',
        position: 'Marketing Communications Intern',
        logoColor: '#E02020',
        logoInitial: 'T',
        kapasitas: '2 Posisi',
        sistemKerja: 'WFO (On-site)',
        durasi: '3 Bulan',
        bidang: 'Marketing & Communications',
        mulaiMagang: '1 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Menyusun strategi konten media sosial untuk brand Telkomsel.',
            'Membantu perencanaan dan eksekusi kampanye marketing digital.',
            'Melakukan analisis kompetitor dan market research.',
            'Membuat laporan performa kampanye secara berkala.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Ilmu Komunikasi, Marketing, atau sejenisnya.',
        keahlianUtama: ['Content Strategy', 'Social Media', 'Analytics', 'Copywriting'],
        persyaratan: [
            'Memiliki pemahaman tentang digital marketing dan social media trends.',
            'Kreatif dan mampu menulis copy yang menarik.',
            'Familiar dengan tools analytics (Google Analytics, Meta Business Suite).',
        ],
    },
    '3': {
        id: 3,
        companyName: 'Traveloka Indonesia',
        position: 'UI/UX Designer Intern',
        logoColor: '#0194F3',
        logoInitial: 'T',
        kapasitas: '2 Posisi',
        sistemKerja: 'WFO (On-site)',
        durasi: '3 Bulan',
        bidang: 'Product Design',
        mulaiMagang: '1 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
            'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
            'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
            'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
        keahlianUtama: ['Figma Specialist', 'UX Research', 'Prototyping', 'Critical Thinking'],
        persyaratan: [
            'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
            'Memahami dasar-dasar desain visual (typography, color theory, layout).',
            'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
        ],
    },
    '4': {
        id: 4,
        companyName: 'PT. Microsoft',
        position: 'Data Analyst Intern',
        logoColor: '#737373',
        logoInitial: 'M',
        kapasitas: '2 Posisi',
        sistemKerja: 'Hybrid',
        durasi: '6 Bulan',
        bidang: 'Data Analytics',
        mulaiMagang: '15 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Menganalisis dataset besar untuk menghasilkan insight bisnis yang actionable.',
            'Membuat dashboard dan visualisasi data menggunakan Power BI.',
            'Mendukung tim product dalam A/B testing dan eksperimen.',
            'Menulis query SQL untuk mengekstrak dan memanipulasi data.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Matematika, Teknik Informatika, atau sejenisnya.',
        keahlianUtama: ['SQL', 'Power BI', 'Python', 'Statistical Analysis'],
        persyaratan: [
            'Memiliki kemampuan analisis data yang kuat.',
            'Familiar dengan SQL dan setidaknya satu bahasa pemrograman (Python/R).',
            'Mampu mengkomunikasikan insight data secara jelas.',
        ],
    },
    '5': {
        id: 5,
        companyName: 'PT. Google Indonesia',
        position: 'Data Analyst Intern',
        logoColor: '#4285F4',
        logoInitial: 'G',
        kapasitas: '1 Posisi',
        sistemKerja: 'Hybrid',
        durasi: '3 Bulan',
        bidang: 'Data Analytics',
        mulaiMagang: '1 September 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Mengembangkan pipeline data untuk analisis performa produk Google di Indonesia.',
            'Membangun dashboard otomatis untuk monitoring KPI tim regional.',
            'Berkolaborasi dengan tim engineering untuk data quality improvement.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ilmu Komputer, atau sejenisnya.',
        keahlianUtama: ['BigQuery', 'Python', 'Looker Studio', 'Data Visualization'],
        persyaratan: [
            'Familiar dengan Google Cloud Platform (BigQuery, Looker).',
            'Memiliki pengalaman menggunakan Python untuk data analysis.',
            'Mampu bekerja secara mandiri maupun dalam tim.',
        ],
    },
    '6': {
        id: 6,
        companyName: 'PT. Bank Central Asia',
        position: 'Data Analyst Intern',
        logoColor: '#003D79',
        logoInitial: 'B',
        kapasitas: '2 Posisi',
        sistemKerja: 'WFO (On-site)',
        durasi: '6 Bulan',
        bidang: 'Banking & Finance Analytics',
        mulaiMagang: '1 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Melakukan analisis data transaksi nasabah untuk identifikasi pola dan tren.',
            'Membuat laporan dan dashboard performa bisnis untuk manajemen.',
            'Mendukung tim risk management dalam pemodelan data.',
            'Mengoptimalkan proses reporting melalui otomatisasi.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Statistika, Ekonomi, Teknik Informatika, atau sejenisnya.',
        keahlianUtama: ['SQL', 'Excel Advanced', 'Tableau', 'Financial Analysis'],
        persyaratan: [
            'Memiliki ketertarikan di bidang perbankan dan keuangan.',
            'Mampu mengolah data dalam volume besar dengan akurasi tinggi.',
            'Memiliki kemampuan presentasi yang baik.',
        ],
    },
    '7': {
        id: 7,
        companyName: 'PT. Indofood',
        position: 'Data Analyst Intern',
        logoColor: '#D4251D',
        logoInitial: 'I',
        kapasitas: '1 Posisi',
        sistemKerja: 'WFO (On-site)',
        durasi: '3 Bulan',
        bidang: 'Business Intelligence',
        mulaiMagang: '1 Agustus 2024',
        deadline: '30 Jun 2024',
        deadlineDaysLeft: 12,
        deskripsiPekerjaan: [
            'Menganalisis data penjualan dan distribusi produk Indofood secara nasional.',
            'Membuat laporan mingguan performa sales per region.',
            'Mendukung tim supply chain dalam forecasting demand.',
            'Membangun visualisasi data untuk presentasi ke stakeholder.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Teknik Industri, Statistika, Manajemen, atau sejenisnya.',
        keahlianUtama: ['Excel Advanced', 'SQL', 'Power BI', 'Supply Chain Basics'],
        persyaratan: [
            'Memiliki kemampuan analisis yang kuat dan detail-oriented.',
            'Familiar dengan tools visualisasi data (Tableau/Power BI).',
            'Mampu bekerja di lingkungan FMCG yang dinamis.',
        ],
    },
    '8': {
        id: 8,
        companyName: 'Traveloka Indonesia',
        position: 'UI/UX Designer Intern',
        logoColor: '#0194F3',
        logoInitial: 'T',
        kapasitas: '2 Posisi',
        sistemKerja: 'WFO (On-site)',
        durasi: '3 Bulan',
        bidang: 'Product Design',
        mulaiMagang: '1 Agustus 2024',
        deadline: '15 Jul 2024',
        deadlineDaysLeft: 18,
        deskripsiPekerjaan: [
            'Bekerja sama dengan tim Product Design dalam merancang antarmuka pengguna (UI) yang intuitif dan menarik untuk platform Traveloka.',
            'Melakukan riset pengguna dan pengujian usability untuk mengidentifikasi area pengembangan pada produk yang sudah ada.',
            'Membuat wireframe, prototype, dan desain high-fidelity menggunakan tool desain standar industri (Figma).',
            'Menjaga konsistensi desain sesuai dengan Design System Traveloka.',
        ],
        pendidikanMinimal: 'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',
        keahlianUtama: ['Figma Specialist', 'UX Research', 'Prototyping', 'Critical Thinking'],
        persyaratan: [
            'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
            'Memahami dasar-dasar desain visual (typography, color theory, layout).',
            'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
        ],
    },
};

/* ── Component ─────────────────────────────────────── */
export default function VacancyDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();

    const vacancy = mockVacanciesDB[id] || mockVacanciesDB['8'];
    const dynamicSimilarVacancies = Object.values(mockVacanciesDB)
        .filter(v => v.id !== vacancy.id)
        .map(v => ({
            id: v.id,
            companyName: v.companyName,
            position: v.position,
            location: v.sistemKerja === 'Hybrid' ? 'Jakarta Selatan' : 'Jakarta Pusat',
            logoColor: v.logoColor,
            logoInitial: v.logoInitial
        })).slice(0, 2);

    const detail = useVacancyDetail(vacancy);

    return (
        <DashboardLayout pageTitle="Info Lowongan Magang">
            {/* Back button */}
            <button
                type="button"
                onClick={() => navigate('/portal')}
                className="mb-6 inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
            >
                <ArrowLeft className="h-4 w-4" />
                Kembali
            </button>

            {/* Two-column layout */}
            <div key={vacancy.id} className="grid grid-cols-[1fr_340px] items-start gap-6 animate-fadeIn">
                {/* Left: content sections */}
                <div className="flex flex-col gap-5">
                    <VacancyDetailHeader vacancy={vacancy} />
                    <VacancyInfoSection vacancy={vacancy} />
                    <VacancyJobDescSection
                        deskripsiPekerjaan={vacancy.deskripsiPekerjaan}
                    />
                    <VacancyQualificationSection vacancy={vacancy} />
                </div>

                {/* Right: sticky sidebar */}
                <VacancyApplySidebar
                    vacancy={vacancy}
                    similarVacancies={dynamicSimilarVacancies}
                    accessStatus={detail.accessStatus}
                    cvFile={detail.cvFile}
                    cvError={detail.cvError}
                    isApplying={detail.isApplying}
                    isApplied={detail.isApplied}
                    canApply={detail.canApply}
                    onFileChange={detail.handleFileChange}
                    onRemoveFile={detail.handleRemoveFile}
                    onApply={detail.handleApply}
                />
            </div>
        </DashboardLayout>
    );
}
