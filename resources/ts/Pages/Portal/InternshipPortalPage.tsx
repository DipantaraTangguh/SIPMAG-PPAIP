import React, { useState, useMemo, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from "../../context/AppContext";
import {
    useApplicationWorkflow,
    useForm1Workflow,
} from "../../context/StudentWorkflowContext";
import {
    canAccessPortal,
    hasSecuredInternship,
    SECURED_INTERNSHIP_MESSAGE,
} from "../../utils/accessUtils";
import { api } from "../../lib/api";
import { Info, FileText } from "lucide-react";
import DashboardLayout from "../../Components/Layouts/DashboardLayout";
import TabNavigation from "../../Components/Fragments/portal/TabNavigation";
import SearchFilterBar from "../../Components/Fragments/portal/SearchFilterBar";
import VacancyGrid from "../../Components/Fragments/portal/VacancyGrid";
import ActiveApplicationsSidebar from "../../Components/Fragments/portal/ActiveApplicationsSidebar";
import IndependentTabContent from "../../Components/Fragments/independent/IndependentTabContent";
const LOGO_PALETTE = [
    "#00AA5B",
    "#E02020",
    "#0194F3",
    "#F5A524",
    "#737373",
    "#682828",
    "#6F42C1",
];
function pickLogoColor(seed = "") {
    let h = 0;
    for (let i = 0; i < seed.length; i++)
        h = (h * 31 + seed.charCodeAt(i)) >>> 0;
    return LOGO_PALETTE[h % LOGO_PALETTE.length];
}
function mapInternship(i) {
    const company = i.company_name || "";
    const deadlineDate = i.deadline ? i.deadline.slice(0, 10) : null;
    const deadline = deadlineDate
        ? new Date(deadlineDate).toLocaleDateString("id-ID", {
              day: "numeric",
              month: "short",
              year: "numeric",
          })
        : "-";
    return {
        id: i.id,
        companyName: company,
        position: i.position,
        location: i.location || "",
        studyPrograms: i.study_programs || [],
        sistemKerja: i.sistem_kerja || "",
        deadline,
        deadlineDate,
        createdAt: i.created_at || "",
        logoUrl: i.logo_url || null,
        logoColor: pickLogoColor(company),
        logoInitial: (company.trim()[0] || "?").toUpperCase(),
        isActive: !!i.is_active,
    };
}

const mockActiveApplications = [
    {
        id: 1,
        companyName: "Shopee International",
        position: "Product Manager Intern",
        status: "Dilamar",
        statusColor: "blue",
        appliedAt: "12 Mei 2024",
    },
    {
        id: 2,
        companyName: "Dana Indonesia",
        position: "QA Engineer Intern",
        status: "Dilamar",
        statusColor: "blue",
        appliedAt: "28 Apr 2024",
    },
];
export default function InternshipPortalPage() {
    const { student } = useAuth();
    const { activeApplications } = useApplicationWorkflow();
    const { form1Submission } = useForm1Workflow();
    const navigate = useNavigate();
    const location = useLocation();
    const accessStatus = student?.accessStatus;
    // Non-wajib tidak punya Form 2, jadi tab Mandiri tidak berlaku.
    const isNonWajib = form1Submission?.jenisMagang === "non_wajib";

    // Balik dari Form 2 harus tetap mendarat di tab Mandiri.
    const [activeTab, setActiveTab] = useState(
        location.state?.activeTab || "mitra",
    );
    const [searchQuery, setSearchQuery] = useState("");
    const [sortBy, setSortBy] = useState("terbaru");
    const [vacancies, setVacancies] = useState([]);
    const [filters, setFilters] = useState({
        studyProgram: "",
        sistemKerja: "",
        location: "",
    });

    // State navigasi dibersihin supaya refresh nggak maksa tab lama.
    useEffect(() => {
        if (location.state?.activeTab) {
            window.history.replaceState({}, "");
        }
    }, []);

    // Lowongan aktif tetap ambil dari backend biar datanya fresh.
    useEffect(() => {
        let cancelled = false;
        api.get("/internships")
            .then((data) => {
                if (cancelled) return;
                setVacancies(
                    (data.internships?.data ?? data.internships ?? []).map(
                        mapInternship,
                    ),
                );
            })
            .catch(() => {
                /* kalau gagal, UI cukup tampil empty state */
            });
        return () => {
            cancelled = true;
        };
    }, []);
    const filteredVacancies = useMemo(() => {
        let result = [...vacancies];

        if (searchQuery.trim()) {
            const q = searchQuery.toLowerCase();
            result = result.filter(
                (v) =>
                    v.companyName.toLowerCase().includes(q) ||
                    v.position.toLowerCase().includes(q) ||
                    (v.location || "").toLowerCase().includes(q),
            );
        }

        if (filters.studyProgram) {
            result = result.filter(
                (v) =>
                    // Lowongan tanpa daftar prodi terbuka untuk semua prodi.
                    v.studyPrograms.length === 0 ||
                    v.studyPrograms.includes(filters.studyProgram),
            );
        }

        if (filters.sistemKerja) {
            result = result.filter(
                (v) => v.sistemKerja === filters.sistemKerja,
            );
        }

        if (filters.location) {
            result = result.filter((v) => v.location === filters.location);
        }

        if (sortBy === "terbaru") {
            result.sort(
                (a, b) =>
                    new Date(b.createdAt).getTime() -
                    new Date(a.createdAt).getTime(),
            );
        } else if (sortBy === "deadline") {
            result.sort((a, b) => {
                // Entry tanpa deadline didorong ke paling bawah.
                if (!a.deadlineDate && !b.deadlineDate) return 0;
                if (!a.deadlineDate) return 1;
                if (!b.deadlineDate) return -1;
                return (
                    new Date(a.deadlineDate).getTime() -
                    new Date(b.deadlineDate).getTime()
                );
            });
        }

        return result;
    }, [vacancies, searchQuery, sortBy, filters]);

    // Opsi unik untuk dropdown filter — dipopulate dari data aktual.
    const filterOptions = useMemo(
        () => ({
            studyProgram: [
                ...new Set(vacancies.flatMap((v) => v.studyPrograms)),
            ].sort(),
            sistemKerja: [
                ...new Set(vacancies.map((v) => v.sistemKerja).filter(Boolean)),
            ].sort(),
            location: [
                ...new Set(vacancies.map((v) => v.location).filter(Boolean)),
            ].sort(),
        }),
        [vacancies],
    );

    const handleFilterChange = (key: string, value: string) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
    };

    const activeFilterCount = Object.values(filters).filter(Boolean).length;

    const handleCardClick = (id) => {
        navigate(`/portal/vacancy/${id}`);
    };

    const showAccessBanner = !canAccessPortal(accessStatus); // Status ini sudah ikut helper akses terbaru.
    const internshipSecured = hasSecuredInternship(accessStatus);

    return (
        <DashboardLayout pageTitle="Lowongan Magang">
            <div className="flex flex-col gap-5">
                <TabNavigation
                    activeTab={activeTab}
                    onTabChange={setActiveTab}
                    isNonWajib={isNonWajib}
                />
                {activeTab === "mitra" || isNonWajib ? (
                    <>
                        {showAccessBanner && (
                            <div className="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center">
                                <Info className="h-4.5 w-4.5 shrink-0 text-amber-500" />
                                <p className="flex-1 text-[13px] leading-relaxed text-amber-700">
                                    Anda dapat melihat lowongan, namun belum
                                    bisa melamar. Selesaikan Form 1 terlebih
                                    dahulu.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate("/form1")}
                                    className="w-full shrink-0 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary-hover sm:w-auto sm:py-1.5"
                                >
                                    Isi Form 1 →
                                </button>
                            </div>
                        )}
                        {internshipSecured && (
                            <div className="flex flex-col gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 sm:flex-row sm:items-center">
                                <Info className="h-4.5 w-4.5 shrink-0 text-green-600" />
                                <p className="flex-1 text-[13px] leading-relaxed text-green-700">
                                    {SECURED_INTERNSHIP_MESSAGE}
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate("/guidance")}
                                    className="w-full shrink-0 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary-hover sm:w-auto sm:py-1.5"
                                >
                                    Buka Bimbingan &amp; Logbook
                                </button>
                            </div>
                        )}
                        <SearchFilterBar
                            searchQuery={searchQuery}
                            onSearchChange={setSearchQuery}
                            sortBy={sortBy}
                            onSortChange={setSortBy}
                            filters={filters}
                            onFilterChange={handleFilterChange}
                            filterOptions={filterOptions}
                            activeFilterCount={activeFilterCount}
                        />
                        <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-[1fr_300px]">
                            <VacancyGrid
                                vacancies={filteredVacancies}
                                accessStatus={accessStatus}
                                onCardClick={handleCardClick}
                            />
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
