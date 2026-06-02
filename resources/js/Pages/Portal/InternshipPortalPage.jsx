/**
 * InternshipPortalPage.jsx
 * "Portal Magang" info page with Mitra vacancy grid,
 * search/filter, active applications sidebar, and FAB.
 * Accessible to all logged-in students (browsing allowed
 * regardless of Form 1 status).
 *
 * Route: /portal
 */
import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import { canAccessPortal } from '../../utils/accessUtils';
import { api } from '../../lib/api';
import { Info, FileText } from 'lucide-react';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import TabNavigation from '../../Components/Fragments/portal/TabNavigation';
import SearchFilterBar from '../../Components/Fragments/portal/SearchFilterBar';
import VacancyGrid from '../../Components/Fragments/portal/VacancyGrid';
import ActiveApplicationsSidebar from '../../Components/Fragments/portal/ActiveApplicationsSidebar';
import IndependentTabContent from '../../Components/Fragments/independent/IndependentTabContent';

/* Deterministic logo color from a small brand palette */
const LOGO_PALETTE = ['#00AA5B', '#E02020', '#0194F3', '#F5A524', '#737373', '#682828', '#6F42C1'];
function pickLogoColor(seed = '') {
    let h = 0;
    for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) >>> 0;
    return LOGO_PALETTE[h % LOGO_PALETTE.length];
}

/** Map API Internship → shape expected by VacancyCard */
function mapInternship(i) {
    const company = i.company_name || '';
    const deadlineDate = i.deadline ? i.deadline.slice(0, 10) : null;
    const deadline = deadlineDate
        ? new Date(deadlineDate).toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric',
        })
        : '—';
    return {
        id: i.id,
        companyName: company,
        position: i.position,
        location: i.location,
        deadline,
        deadlineDate,
        logoColor: pickLogoColor(company),
        logoInitial: (company.trim()[0] || '?').toUpperCase(),
        isActive: !!i.is_active,
    };
}

const mockActiveApplications = [
    {
        id: 1,
        companyName: 'Shopee International',
        position: 'Product Manager Intern',
        status: 'Dilamar',
        statusColor: 'blue',
        appliedAt: '12 Mei 2024',
    },
    {
        id: 2,
        companyName: 'Dana Indonesia',
        position: 'QA Engineer Intern',
        status: 'Dilamar',
        statusColor: 'blue',
        appliedAt: '28 Apr 2024',
    },
];

/* ── Component ───────────────────────────────────── */
export default function InternshipPortalPage() {
    const { student, activeApplications } = useSimulation();
    const navigate = useNavigate();
    const location = useLocation();
    const accessStatus = student?.accessStatus;

    // Restore active tab from navigation state (e.g. after Form 2 submit)
    const [activeTab, setActiveTab] = useState(
        location.state?.activeTab || 'mitra'
    );
    const [searchQuery, setSearchQuery] = useState('');
    const [sortBy, setSortBy] = useState('terbaru');
    const [vacancies, setVacancies] = useState([]);

    // Clear navigation state after reading to prevent stale tab on refresh
    useEffect(() => {
        if (location.state?.activeTab) {
            window.history.replaceState({}, '');
        }
    }, []);

    // Fetch active vacancies from the backend
    useEffect(() => {
        let cancelled = false;
        api.get('/internships')
            .then((data) => {
                if (cancelled) return;
                setVacancies((data.internships || []).map(mapInternship));
            })
            .catch(() => { /* ignore — empty list */ });
        return () => { cancelled = true; };
    }, []);

    /* Derived filtered list */
    const filteredVacancies = useMemo(() => {
        let result = [...vacancies];

        if (searchQuery.trim()) {
            const q = searchQuery.toLowerCase();
            result = result.filter(
                (v) =>
                    v.companyName.toLowerCase().includes(q) ||
                    v.position.toLowerCase().includes(q) ||
                    v.location.toLowerCase().includes(q),
            );
        }

        if (sortBy === 'deadline') {
            result.sort(
                (a, b) =>
                    new Date(a.deadlineDate) - new Date(b.deadlineDate),
            );
        }

        return result;
    }, [vacancies, searchQuery, sortBy]);

    const handleCardClick = (id) => {
        navigate(`/portal/vacancy/${id}`);
    };

    const showAccessBanner = !canAccessPortal(accessStatus); // FIXED

    return (
        <DashboardLayout pageTitle="Portal Magang">
            <div className="flex flex-col gap-5">
                {/* Tab navigation */}
                <TabNavigation
                    activeTab={activeTab}
                    onTabChange={setActiveTab}
                />

                {/* Active Tab Content */}
                {activeTab === 'mitra' ? (
                    <>
                        {/* Access status banner */}
                        {showAccessBanner && (
                            <div className="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center">
                                <Info className="h-[18px] w-[18px] flex-shrink-0 text-amber-500" />
                                <p className="flex-1 text-[13px] leading-relaxed text-amber-700">
                                    Anda dapat melihat lowongan, namun belum bisa
                                    melamar. Selesaikan Form 1 terlebih dahulu.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate('/form1')}
                                    className="w-full flex-shrink-0 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary-hover sm:w-auto sm:py-1.5"
                                >
                                    Isi Form 1 →
                                </button>
                            </div>
                        )}

                        {/* Search + filter */}
                        <SearchFilterBar
                            searchQuery={searchQuery}
                            onSearchChange={setSearchQuery}
                            sortBy={sortBy}
                            onSortChange={setSortBy}
                        />

                        {/* Main content grid */}
                        <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-[1fr_300px]">
                            {/* Left: vacancy grid */}
                            <VacancyGrid
                                vacancies={filteredVacancies}
                                accessStatus={accessStatus}
                                onCardClick={handleCardClick}
                            />

                            {/* Right: sidebar — pushed down to align with first card row */}
                            <div className="pt-0 xl:pt-10">
                                <ActiveApplicationsSidebar
                                    applications={activeApplications}
                                />
                            </div>
                        </div>
                    </>
                ) : (
                    <IndependentTabContent />
                )}
            </div>
        </DashboardLayout>
    );
}
