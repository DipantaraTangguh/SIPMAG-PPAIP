import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../../context/AppContext';
import { useForm2Workflow } from '../../../context/StudentWorkflowContext';
import { Info } from 'lucide-react';
import IndependentInfoBanner from './IndependentInfoBanner';
import IndependentSectionHeader from './IndependentSectionHeader';
import IndependentEmptyState from './IndependentEmptyState';
import IndependentSidePanel from './IndependentSidePanel';
import IndependentFilledState from './IndependentFilledState';
import {
    canAccessPortal,
    hasSecuredInternship,
    FORM2_LOCKED_MESSAGE,
} from '../../../utils/accessUtils';

export default function IndependentTabContent() {
    const navigate = useNavigate();
    const location = useLocation();
    const { student } = useAuth();
    const { form2Submissions } = useForm2Workflow();

    const submissions = form2Submissions || [];
    const accessStatus = student?.accessStatus;

    // Form 2 baru kebuka setelah Form 1 aman.
    const isUnlocked = canAccessPortal(accessStatus); // Status ini sudah ikut helper akses terbaru.
    const internshipSecured = hasSecuredInternship(accessStatus);

    // Warning tetap muncul kalau user nyasar via URL manual.
    const showWarning = !isUnlocked || location.state?.blockedReason === 'form1_required';
    const showSecuredWarning =
        internshipSecured || location.state?.blockedReason === 'internship_secured';

    const handleCreateNew = () => {
        if (internshipSecured) return;
        navigate('/portal/independent/form2/new');
    };

    return (
        <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <IndependentInfoBanner />
            {showWarning && (
                <div className="my-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <Info className="h-[18px] w-[18px] flex-shrink-0 text-amber-500" />
                    <p className="flex-1 text-[13px] leading-relaxed text-amber-700">
                        Anda belum dapat mengajukan Form 2. Selesaikan dan tunggu
                        persetujuan Form 1 terlebih dahulu sebelum mengajukan surat
                        pengantar magang.
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
            {showSecuredWarning && (
                <div className="my-6 flex flex-col gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 sm:flex-row sm:items-center">
                    <Info className="h-[18px] w-[18px] flex-shrink-0 text-green-600" />
                    <p className="flex-1 text-[13px] leading-relaxed text-green-700">
                        {FORM2_LOCKED_MESSAGE}
                    </p>
                    <button
                        type="button"
                        onClick={() => navigate('/guidance')}
                        className="w-full flex-shrink-0 rounded-lg bg-primary px-3.5 py-2 text-xs font-bold text-white hover:bg-primary-hover sm:w-auto sm:py-1.5"
                    >
                        Buka Bimbingan &amp; Logbook
                    </button>
                </div>
            )}

            {submissions.length === 0 ? (
                <>
                    <IndependentSectionHeader count={0} />
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
                        <div>
                            <IndependentEmptyState
                                onCreateNew={handleCreateNew}
                                isLocked={!isUnlocked || internshipSecured}
                            />
                        </div>
                        <IndependentSidePanel />
                    </div>
                </>
            ) : (
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
                    <div>
                        <IndependentFilledState
                            submissions={submissions}
                            onCreateNew={handleCreateNew}
                            isLocked={internshipSecured}
                        />
                    </div>
                    <div className="max-lg:mt-2 lg:mt-[86px]">
                        <IndependentSidePanel />
                    </div>
                </div>
            )}
        </div>
    );
}
