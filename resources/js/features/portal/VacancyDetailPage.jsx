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
const mockVacancyDetail = {
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

    pendidikanMinimal:
        'S1 Mahasiswa Aktif (Semester 6 ke atas) - Desain Komunikasi Visual, Teknik Informatika, atau sejenisnya.',

    keahlianUtama: [
        'Figma Specialist',
        'UX Research',
        'Prototyping',
        'Critical Thinking',
    ],

    persyaratan: [
        'Memiliki portofolio desain UI/UX yang menunjukkan proses pemecahan masalah.',
        'Memahami dasar-dasar desain visual (typography, color theory, layout).',
        'Mampu bekerja dalam tim dan memiliki kemampuan komunikasi yang baik.',
    ],
};

const mockSimilarVacancies = [
    {
        id: 9,
        companyName: 'Gojek Indonesia',
        position: 'Graphic Design Intern',
        location: 'Jakarta Selatan',
        logoColor: '#00AA5B',
        logoInitial: 'G',
    },
    {
        id: 10,
        companyName: 'Shopee Indonesia',
        position: 'Product Manager Trainee',
        location: 'Jakarta Pusat',
        logoColor: '#EE4D2D',
        logoInitial: 'S',
    },
];

/* ── Component ─────────────────────────────────────── */
export default function VacancyDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();

    // In a real app we'd fetch by id — simulation uses the single mock
    const vacancy = mockVacancyDetail;

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
            <div className="grid grid-cols-[1fr_340px] items-start gap-6">
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
                    similarVacancies={mockSimilarVacancies}
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
