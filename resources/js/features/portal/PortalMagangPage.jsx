/**
 * PortalMagangPage.jsx
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
import { Info, FileText } from 'lucide-react';
import DashboardLayout from '../../layouts/DashboardLayout';
import TabNavigation from '../../components/portal/TabNavigation';
import SearchFilterBar from '../../components/portal/SearchFilterBar';
import VacancyGrid from '../../components/portal/VacancyGrid';
import ActiveApplicationsSidebar from '../../components/portal/ActiveApplicationsSidebar';
import MandiriTabContent from '../../components/portal/mandiri/MandiriTabContent';

/* ── Mock data ───────────────────────────────────── */
const mockVacancies = [
    {
        id: 1,
        companyName: 'PT. Gojek Tokopedia',
        position: 'Software Engineer Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#00AA5B',
        logoInitial: 'G',
        isActive: true,
    },
    {
        id: 2,
        companyName: 'Telkomsel',
        position: 'Marketing Communications Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#E02020',
        logoInitial: 'T',
        isActive: true,
    },
    {
        id: 3,
        companyName: 'Traveloka Indonesia',
        position: 'UI/UX Designer Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#0194F3',
        logoInitial: 'T',
        isActive: true,
    },
    {
        id: 4,
        companyName: 'PT. Microsoft',
        position: 'Data Analyst Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#737373',
        logoInitial: 'M',
        isActive: true,
    },
    {
        id: 5,
        companyName: 'PT. Google Indonesia',
        position: 'Data Analyst Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#737373',
        logoInitial: 'G',
        isActive: true,
    },
    {
        id: 6,
        companyName: 'PT. Bank Central Asia',
        position: 'Data Analyst Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#737373',
        logoInitial: 'B',
        isActive: true,
    },
    {
        id: 7,
        companyName: 'PT. Indofood',
        position: 'Data Analyst Intern',
        location: 'Jakarta Selatan',
        deadline: '30 Jun 2024',
        deadlineDate: '2024-06-30',
        logoColor: '#737373',
        logoInitial: 'I',
        isActive: true,
    },
    {
        id: 8,
        companyName: 'Traveloka Indonesia',
        position: 'UI/UX Designer Intern',
        location: 'Tangerang',
        deadline: '15 Jul 2024',
        deadlineDate: '2024-07-15',
        logoColor: '#0194F3',
        logoInitial: 'T',
        isActive: true,
    },
];

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
export default function PortalMagangPage() {
    const { student, activeApplications } = useSimulation();
    const navigate = useNavigate();
    const location = useLocation();
    const accessStatus = student.accessStatus;

    // Restore active tab from navigation state (e.g. after Form 2 submit)
    const [activeTab, setActiveTab] = useState(
        location.state?.activeTab || 'mitra'
    );
    const [searchQuery, setSearchQuery] = useState('');
    const [sortBy, setSortBy] = useState('terbaru');

    // Clear navigation state after reading to prevent stale tab on refresh
    useEffect(() => {
        if (location.state?.activeTab) {
            window.history.replaceState({}, '');
        }
    }, []);

    /* Derived filtered list */
    const filteredVacancies = useMemo(() => {
        let result = [...mockVacancies];

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
    }, [searchQuery, sortBy]);

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
                            <div className="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <Info className="h-[18px] w-[18px] flex-shrink-0 text-amber-500" />
                                <p className="flex-1 text-[13px] leading-relaxed text-amber-700">
                                    Anda dapat melihat lowongan, namun belum bisa
                                    melamar. Selesaikan Form 1 terlebih dahulu.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate('/form1')}
                                    className="flex-shrink-0 rounded-lg bg-primary px-3.5 py-1.5 text-xs font-bold text-white hover:bg-primary-hover"
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
                        <div className="grid grid-cols-[1fr_300px] items-start gap-6">
                            {/* Left: vacancy grid */}
                            <VacancyGrid
                                vacancies={filteredVacancies}
                                accessStatus={accessStatus}
                                onCardClick={handleCardClick}
                            />

                            {/* Right: sidebar — pushed down to align with first card row */}
                            <div className="pt-10">
                                <ActiveApplicationsSidebar
                                    applications={activeApplications}
                                />
                            </div>
                        </div>
                    </>
                ) : (
                    <MandiriTabContent />
                )}
            </div>
        </DashboardLayout>
    );
}
