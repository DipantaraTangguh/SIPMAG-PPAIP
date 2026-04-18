/**
 * VacancyApplySidebar.jsx
 * Sticky right sidebar with deadline banner, CV upload, apply button,
 * and similar vacancies section.
 *
 * @prop {object}   vacancy           — Vacancy detail (deadline, company).
 * @prop {Array}    similarVacancies  — List of similar vacancy objects.
 * @prop {string}   accessStatus      — Student access status.
 * @prop {object}   cvFile            — Selected CV file object or null.
 * @prop {string}   cvError           — File validation error or null.
 * @prop {boolean}  isApplying        — True while submitting.
 * @prop {boolean}  isApplied         — True after successful application.
 * @prop {boolean}  canApply          — True if accessStatus = ApprovedForm1.
 * @prop {function} onFileChange      — Callback for CV file selection.
 * @prop {function} onRemoveFile      — Callback to clear selected file.
 * @prop {function} onApply           — Callback to submit application.
 * @prop {function} onNavigate        — Navigation function.
 */
import React, { useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import {
    Clock,
    CloudUpload,
    MapPin,
    X,
    FileText,
    Info,
    CheckCircle,
    Loader2,
    Check,
    CheckSquare,
} from 'lucide-react';

export default function VacancyApplySidebar({
    vacancy,
    similarVacancies = [],
    accessStatus,
    cvFile,
    cvError,
    isApplying,
    isApplied,
    canApply,
    onFileChange,
    onRemoveFile,
    onApply,
}) {
    const navigate = useNavigate();
    const fileInputRef = useRef(null);
    const [showConfirmModal, setShowConfirmModal] = useState(false);

    const handleDropzoneClick = () => fileInputRef.current?.click();

    const handleInputChange = (e) => {
        const file = e.target.files?.[0];
        if (file) onFileChange(file);
    };

    const formatSize = (bytes) => {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    return (
        <div className="sticky top-6 self-start">
            {/* ── Card 1: Apply ──────────────── */}
            <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                {/* Deadline banner */}
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

                {/* CV Upload */}
                <div className="mt-4">
                    <p className="mb-2 text-[13px] font-bold text-gray-900">
                        Unggah CV &amp; Portfolio
                    </p>

                    <input
                        ref={fileInputRef}
                        type="file"
                        accept=".pdf"
                        className="hidden"
                        onChange={handleInputChange}
                    />

                    {!cvFile ? (
                        <button
                            type="button"
                            onClick={handleDropzoneClick}
                            className="flex w-full flex-col items-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition-colors hover:border-primary/50 hover:bg-primary-pale"
                        >
                            <CloudUpload className="h-8 w-8 text-gray-300" />
                            <span className="mt-2 text-[13px] text-gray-500">
                                Klik untuk unggah CV
                            </span>
                            <span className="mt-0.5 text-[11px] italic text-gray-400">
                                PDF
                            </span>
                            <span className="text-[11px] italic text-gray-400">
                                Maksimal 5MB
                            </span>
                        </button>
                    ) : (
                        <div className="flex items-center justify-between rounded-xl border border-primary bg-primary-pale px-3 py-3">
                            <div className="flex items-center gap-2 overflow-hidden">
                                <FileText className="h-4 w-4 flex-shrink-0 text-primary" />
                                <div className="min-w-0">
                                    <p className="truncate text-xs font-bold text-gray-900">
                                        {cvFile.name}
                                    </p>
                                    <p className="text-[10px] text-gray-500">
                                        {formatSize(cvFile.size)}
                                    </p>
                                </div>
                            </div>
                            {!isApplied && (
                                <button
                                    type="button"
                                    onClick={onRemoveFile}
                                    className="flex-shrink-0 rounded p-0.5 text-gray-400 hover:text-red-500"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            )}
                        </div>
                    )}

                    {cvError && (
                        <p className="mt-1.5 text-xs text-red-500">
                            {cvError}
                        </p>
                    )}
                </div>

                {/* Apply button / success state */}
                <div className="mt-4">
                    {isApplied ? (
                        <>
                            <div className="rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-center">
                                <CheckCircle className="mx-auto h-8 w-8 text-green-500" />
                                <p className="mt-2 text-md font-bold text-gray-900">
                                    Lamaran Berhasil Dikirim!
                                </p>
                                <p className="mt-3 text-sm ">
                                    Lamaran Anda ke {vacancy.companyName} telah tercatat.
                                </p>
                                <p className="mt-2 text-sm leading-relaxed">
                                    Cek berkala email Anda untuk mendapatkan pemberitahuan lolos atau seleksi lanjutan dari mitra.
                                </p>
                            </div>

                            {/* Catatan Penting */}
                            <div className="mt-4 rounded-xl bg-primary-pale px-4 py-4">
                                <div className="flex items-center gap-2">
                                    <CheckSquare className="h-5 w-5 text-primary" />
                                    <p className="text-sm font-bold text-primary">
                                        CATATAN PENTING
                                    </p>
                                </div>
                                <p className="mt-3 text-sm leading-relaxed text-primary-dark">
                                    Setelah diterima magang, Anda wajib mengunggah{' '}
                                    <span className="font-bold">Letter of Acceptance (LoA)</span>{' '}
                                    secara terpisah melalui menu{' '}
                                    <span className="font-bold underline">Bimbingan &amp; Logbook</span>{' '}
                                    untuk memulai periode magang resmi.
                                </p>
                            </div>

                            {/* Sudah dapat LOA? */}
                            <div className="mt-5">
                                <p className="mb-2 text-sm font-bold text-gray-900">
                                    Sudah dapat LOA?
                                </p>
                                <button
                                    type="button"
                                    onClick={() => navigate('/bimbingan')}
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

            {/* ── Card 2: Similar Vacancies ──── */}
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
                                {/* Logo */}
                                <div
                                    className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white"
                                    style={{
                                        backgroundColor: sv.logoColor,
                                    }}
                                >
                                    {sv.logoInitial}
                                </div>

                                {/* Info */}
                                <div className="min-w-0 flex-1">
                                    <p className="text-[13px] font-bold text-gray-900">
                                        {sv.companyName}
                                    </p>
                                    <p className="mt-0.5 text-xs text-gray-500">
                                        {sv.position}
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
            {/* ── Confirmation Modal ──── */}
            {showConfirmModal && createPortal(
                <div
                    className="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                    style={{ backgroundColor: 'rgba(17, 24, 39, 0.5)', backdropFilter: 'blur(4px)' }}
                >
                    <div className="w-full max-w-[400px] rounded-2xl bg-white p-6 text-center shadow-xl">
                        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary-pale">
                            <Info className="h-7 w-7 text-primary" />
                        </div>
                        <h3 className="mb-2 text-lg font-bold text-gray-900">
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
