import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import {
    Clock,
    MapPin,
    Info,
    CheckCircle,
    Loader2,
    Check,
} from 'lucide-react';
import { useForm1Workflow } from '../../../context/StudentWorkflowContext';
import FileDropInput from '../../Elements/FileDropInput';

export default function VacancyApplySidebar({
    vacancy,
    similarVacancies = [],
    accessStatus,
    cvFile,
    cvError,
    isApplying,
    isApplied,
    canApply,
    internshipSecured = false,
    securedInternshipMessage,
    onFileChange,
    onRemoveFile,
    onApply,
}) {
    const navigate = useNavigate();
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const { form1Submission } = useForm1Workflow();

    // Non-wajib tidak punya Bimbingan & Logbook -- LoA-nya diunggah lewat
    // panel konfirmasi magang di Profil.
    const loaMenuLabel =
        form1Submission?.jenisMagang === 'non_wajib'
            ? 'Profil'
            : 'Bimbingan & Logbook';

    return (
        <div className="self-start xl:sticky xl:top-6">
            <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div className="flex items-center gap-3 rounded-lg border border-primary/20 bg-primary-pale px-3.5 py-2.5">
                    <Clock className="h-4 w-4 flex-shrink-0 text-primary" />
                    <div>
                        <p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            Waktu Tersisa
                        </p>
                        <p className="text-sm font-bold text-primary">
                            Sisa {vacancy.deadlineDaysLeft} hari lagi
                        </p>
                    </div>
                </div>
                {internshipSecured && (
                    <div className="mt-4 rounded-xl border border-green-200 bg-green-50 p-4">
                        <div className="flex items-start gap-3">
                            <CheckCircle className="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600" />
                            <div>
                                <p className="text-[13px] font-bold text-green-800">
                                    Magang Sudah Disetujui
                                </p>
                                <p className="mt-1 text-[12px] leading-relaxed text-green-700">
                                    {securedInternshipMessage}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            onClick={() => navigate('/guidance')}
                            className="mt-4 w-full rounded-xl bg-primary py-3 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover"
                        >
                            Buka Bimbingan &amp; Logbook
                        </button>
                    </div>
                )}
                {!isApplied && !internshipSecured && (
                <div className="mt-4">
                    <p className="mb-2 text-[13px] font-bold text-gray-900">
                        Unggah CV &amp; Portfolio
                    </p>

                    <FileDropInput
                        hint="PDF (maks. 5MB)"
                        accept=".pdf"
                        allowedTypes={['application/pdf']}
                        maxSizeMB={5}
                        value={cvFile}
                        onChange={(file) => (file ? onFileChange(file) : onRemoveFile())}
                        error={cvError}
                    />

                </div>
                )}
                <div className="mt-4">
                    {internshipSecured ? (
                        <>
                            <button
                                type="button"
                                disabled
                                className="w-full cursor-not-allowed rounded-xl bg-gray-200 py-3.5 text-[15px] font-bold text-gray-400"
                            >
                                Lamar Sekarang ▶
                            </button>
                            <div className="mt-2 flex items-start gap-1.5">
                                <Info className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-green-600" />
                                <p className="text-xs text-green-700">
                                    Anda sudah memiliki pengajuan magang yang disetujui.
                                </p>
                            </div>
                        </>
                    ) : isApplied ? (
                        <>
                            <div className="rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm text-center">
                                <div className="mx-auto flex h-[48px] w-[48px] items-center justify-center rounded-full bg-green-100 mb-3 shadow-sm">
                                    <CheckCircle className="h-[24px] w-[24px] text-green-600" />
                                </div>
                                <h3 className="text-[16px] font-bold text-green-800">
                                    Lamaran Berhasil Dikirim!
                                </h3>
                                <p className="mt-1.5 text-[13px] font-medium text-green-700">
                                    Lamaran ke <span className="font-bold">{vacancy.companyName}</span> telah tercatat.
                                </p>
                                <p className="mt-3.5 text-[12px] leading-relaxed text-green-800/80">
                                    Cek berkala email Anda untuk mendapatkan pemberitahuan lolos atau seleksi lanjutan dari mitra.
                                </p>
                            </div>
                            <div className="mt-4 rounded-xl border border-primary/20 border-l-4 border-l-primary bg-primary-pale p-4 shadow-sm">
                                <div className="flex items-center gap-2">
                                    <Info className="h-[18px] w-[18px] text-primary" />
                                    <span className="text-[12px] font-bold uppercase tracking-wider text-primary">
                                        Catatan Penting
                                    </span>
                                </div>
                                <p className="mt-2.5 text-[13px] leading-relaxed text-gray-700">
                                    Setelah diterima magang, Anda wajib mengunggah{' '}
                                    <span className="font-semibold text-gray-900">Letter of Acceptance (LoA)</span>{' '}
                                    secara terpisah melalui menu{' '}
                                    <span className="font-semibold text-primary underline">{loaMenuLabel}</span>{' '}
                                    untuk memulai periode magang resmi.
                                </p>
                            </div>
                            <div className="mt-5">
                                <p className="mb-2 text-sm font-bold text-gray-900">
                                    Sudah dapat LOA?
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate('/guidance')}
                                    className="w-full rounded-xl bg-primary py-3.5 text-[15px] font-bold text-white transition-colors hover:bg-primary-hover"
                                >
                                    Ajukan Pembimbing
                                </button>
                            </div>
                        </>
                    ) : canApply ? (
                        <button
                            type="button"
                            onClick={() => setShowConfirmModal(true)}
                            disabled={isApplying || !cvFile}
                            className="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-[15px] font-bold text-white transition-colors hover:bg-primary-hover disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            {isApplying ? (
                                <>
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Mengirim...
                                </>
                            ) : (
                                'Lamar Sekarang ▶'
                            )}
                        </button>
                    ) : (
                        <>
                            <button
                                type="button"
                                disabled
                                className="w-full cursor-not-allowed rounded-xl bg-gray-200 py-3.5 text-[15px] font-bold text-gray-400"
                            >
                                Lamar Sekarang ▶
                            </button>
                            <div className="mt-2 flex items-start gap-1.5">
                                <Info className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-500" />
                                <p className="text-xs text-amber-600">
                                    Selesaikan Form 1 untuk dapat melamar
                                    lowongan ini.
                                </p>
                            </div>
                        </>
                    )}
                </div>
            </div>
            {similarVacancies.length > 0 && (
                <div className="mt-4 rounded-xl border border-gray-200 bg-white p-5">
                    <p className="mb-3.5 text-[11px] font-bold uppercase tracking-wider text-primary">
                        Lowongan Serupa
                    </p>

                    <div className="flex flex-col gap-3">
                        {similarVacancies.map((sv) => (
                            <button
                                key={sv.id}
                                type="button"
                                onClick={() =>
                                    navigate(`/portal/vacancy/${sv.id}`)
                                }
                                className="flex items-start gap-3 rounded-lg p-2 text-left transition-colors hover:bg-gray-50"
                            >
                                {sv.logoUrl ? (
                                    <img
                                        src={sv.logoUrl}
                                        alt={`Logo ${sv.companyName}`}
                                        className="h-8 w-8 flex-shrink-0 rounded-lg border border-gray-100 bg-white object-contain"
                                    />
                                ) : (
                                    <div
                                        className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white"
                                        style={{
                                            backgroundColor: sv.logoColor,
                                        }}
                                    >
                                        {sv.logoInitial}
                                    </div>
                                )}
                                <div className="min-w-0 flex-1">
                                    <p className="text-[13px] font-bold text-gray-900 truncate">
                                        {sv.position}
                                    </p>
                                    <p className="mt-0.5 text-xs text-gray-500 truncate">
                                        {sv.companyName}
                                    </p>
                                    <div className="mt-0.5 flex items-center gap-1 text-[11px] text-gray-400">
                                        <MapPin className="h-2.5 w-2.5" />
                                        {sv.location}
                                    </div>
                                </div>
                            </button>
                        ))}
                    </div>
                </div>
            )}
            {showConfirmModal && createPortal(
                <div
                    className="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                    style={{ backgroundColor: 'rgba(17, 24, 39, 0.5)', backdropFilter: 'blur(4px)' }}
                >
                    <div 
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="confirm-modal-title"
                        className="w-full max-w-[400px] rounded-2xl bg-white p-6 text-center shadow-xl"
                    >
                        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary-pale">
                            <Info className="h-7 w-7 text-primary" />
                        </div>
                        <h3 id="confirm-modal-title" className="mb-2 text-lg font-bold text-gray-900">
                            Apakah Anda yakin untuk melamar pada posisi ini?
                        </h3>
                        <p className="mb-6 text-sm text-gray-500">
                            Pastikan CV dan data diri Anda sudah sesuai sebelum melanjutkan.
                        </p>
                        <div className="flex gap-3">
                            <button
                                type="button"
                                onClick={() => setShowConfirmModal(false)}
                                className="flex-1 rounded-xl border border-gray-200 bg-white py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setShowConfirmModal(false);
                                    onApply();
                                }}
                                className="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary py-3 font-semibold text-white transition-colors hover:bg-primary-hover"
                            >
                                <Check className="h-5 w-5" />
                                Yakin
                            </button>
                        </div>
                    </div>
                </div>,
                document.body
            )}
        </div>
    );
}
