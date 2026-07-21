import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Loader2, Search } from 'lucide-react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import VacancyDetailHeader from '../../Components/Fragments/vacancy/VacancyDetailHeader';
import VacancyInfoSection from '../../Components/Fragments/vacancy/VacancyInfoSection';
import VacancyJobDescSection from '../../Components/Fragments/vacancy/VacancyJobDescSection';
import VacancyQualificationSection from '../../Components/Fragments/vacancy/VacancyQualificationSection';
import VacancyApplySidebar from '../../Components/Fragments/vacancy/VacancyApplySidebar';
import useVacancyDetail from '../../hooks/useVacancyDetail';
import { api } from '../../lib/api';
const LOGO_PALETTE = ['#00AA5B', '#E02020', '#0194F3', '#F5A524', '#737373', '#682828', '#6F42C1'];
function pickLogoColor(seed = '') {
    let h = 0;
    for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) >>> 0;
    return LOGO_PALETTE[h % LOGO_PALETTE.length];
}

function formatIdDate(iso, { withDay = false } = {}) {
    if (!iso) return '-';
    const date = new Date(iso.slice(0, 10));
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: withDay ? 'long' : 'short',
        year: 'numeric',
    });
}

function daysUntil(iso) {
    if (!iso) return 0;
    const date = new Date(iso.slice(0, 10));
    return Math.max(
        0,
        Math.ceil((date.getTime() - Date.now()) / (1000 * 60 * 60 * 24)),
    );
}
function mapInternshipDetail(i) {
    if (!i) return null;
    const company = i.company_name || '';
    return {
        id: i.id,
        companyName: company,
        position: i.position,
        location: i.location || '-',
        logoColor: pickLogoColor(company),
        logoInitial: (company.trim()[0] || '?').toUpperCase(),
        kapasitas: i.capacity || '-',
        sistemKerja: i.sistem_kerja || '-',
        durasi: i.duration || '-',
        bidang: i.bidang || '-',
        mulaiMagang: formatIdDate(i.start_date, { withDay: true }),
        deadline: formatIdDate(i.deadline),
        deadlineDaysLeft: daysUntil(i.deadline),
        deskripsiPekerjaan: Array.isArray(i.job_description) ? i.job_description : [],
        pendidikanMinimal: i.minimum_education || '-',
        keahlianUtama: Array.isArray(i.skills) ? i.skills : [],
        persyaratan: Array.isArray(i.requirements) ? i.requirements : [],
    };
}
function mapSimilar(i) {
    const company = i.company_name || '';
    return {
        id: i.id,
        companyName: company,
        position: i.position,
        location: i.location || '-',
        logoColor: pickLogoColor(company),
        logoInitial: (company.trim()[0] || '?').toUpperCase(),
    };
}
export default function VacancyDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [vacancy, setVacancy] = useState(null);
    const [similarVacancies, setSimilarVacancies] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [notFound, setNotFound] = useState(false);

    useEffect(() => {
        let cancelled = false;
        setIsLoading(true);
        setNotFound(false);

        Promise.all([
            api.get(`/internships/${id}`),
            api.get('/internships'),
        ])
            .then(([detail, list]) => {
                if (cancelled) return;
                setVacancy(mapInternshipDetail(detail.internship));
                const numericId = Number(id);
                setSimilarVacancies(
                    (list.internships?.data ?? list.internships ?? [])
                        .filter((i) => i.id !== numericId)
                        .slice(0, 2)
                        .map(mapSimilar),
                );
                setIsLoading(false);
            })
            .catch(() => {
                if (cancelled) return;
                setNotFound(true);
                setIsLoading(false);
            });

        return () => { cancelled = true; };
    }, [id]);

    const detail = useVacancyDetail(vacancy);

    const BackButton = (
        <button
            type="button"
            onClick={() => navigate('/portal')}
            className="mb-6 inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
        >
            <ArrowLeft className="h-4 w-4" />
            Kembali
        </button>
    );

    if (isLoading) {
        return (
            <DashboardLayout pageTitle="Info Lowongan Magang">
                {BackButton}
                <div className="flex items-center justify-center rounded-xl border border-dashed border-gray-200 py-24 text-gray-400">
                    <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                    Memuat lowongan...
                </div>
            </DashboardLayout>
        );
    }

    if (notFound || !vacancy) {
        return (
            <DashboardLayout pageTitle="Info Lowongan Magang">
                {BackButton}
                <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-20">
                    <Search className="h-12 w-12 text-gray-300" />
                    <p className="mt-4 text-base font-bold text-gray-400">
                        Lowongan tidak ditemukan
                    </p>
                    <p className="mt-1 text-[13px] text-gray-400">
                        Lowongan ini mungkin telah dihapus atau dinonaktifkan.
                    </p>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Info Lowongan Magang">
            {BackButton}
            <div key={vacancy.id} className="grid grid-cols-1 items-start gap-6 animate-fadeIn xl:grid-cols-[1fr_340px]">
                <div className="flex flex-col gap-5">
                    <VacancyDetailHeader vacancy={vacancy} />
                    <VacancyInfoSection vacancy={vacancy} />
                    <VacancyJobDescSection
                        deskripsiPekerjaan={vacancy.deskripsiPekerjaan}
                    />
                    <VacancyQualificationSection vacancy={vacancy} />
                </div>
                <VacancyApplySidebar
                    vacancy={vacancy}
                    similarVacancies={similarVacancies}
                    accessStatus={detail.accessStatus}
                    cvFile={detail.cvFile}
                    cvError={detail.cvError}
                    isApplying={detail.isApplying}
                    isApplied={detail.isApplied}
                    canApply={detail.canApply}
                    internshipSecured={detail.internshipSecured}
                    securedInternshipMessage={detail.securedInternshipMessage}
                    onFileChange={detail.handleFileChange}
                    onRemoveFile={detail.handleRemoveFile}
                    onApply={detail.handleApply}
                />
            </div>
        </DashboardLayout>
    );
}
