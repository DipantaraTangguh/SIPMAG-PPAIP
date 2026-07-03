import React, { useState } from 'react';
import { CheckCircle, Clock, XCircle, Download, FileEdit, Info } from 'lucide-react';
import { api } from '../../../lib/api';
function ApprovedCard({ data }) {
    const [isDownloading, setIsDownloading] = useState(false);
    const [downloadError, setDownloadError] = useState(null);

    const handleDownload = async () => {
        setDownloadError(null);
        setIsDownloading(true);
        try {
            const filename = `Surat_Permohonan_Magang_${data.companyName || data.id}.pdf`;
            await api.download(`/form2/${data.id}/surat-pengantar`, filename);
        } catch (err) {
            console.error('[Form2] Download error:', err);
            setDownloadError(err.message || 'Gagal mengunduh dokumen.');
        } finally {
            setIsDownloading(false);
        }
    };

    return (
        <div className="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-start sm:gap-4">
            <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                <CheckCircle className="h-[22px] w-[22px] text-green-500" />
            </div>
            <div className="flex-1">
                <p className="text-[15px] font-bold text-[#1A1A1A]">{data.companyName}</p>
                <div className="mt-0.5 flex items-center gap-1.5 text-[12px]">
                    <span className="text-gray-500">{data.position}</span>
                    <span className="text-gray-300">•</span>
                    <span className="text-gray-400">Diajukan: {data.submittedAt}</span>
                </div>
                <div className="mt-2 inline-block rounded-full bg-green-100 px-3 py-1 text-[12px] font-bold text-green-600">
                    Disetujui
                </div>
                {downloadError && (
                    <p className="mt-2 text-[12px] text-red-600">{downloadError}</p>
                )}
            </div>
            <button
                type="button"
                onClick={handleDownload}
                disabled={isDownloading}
                className="mt-3 flex flex-shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-[13px] font-bold text-gray-700 transition-colors hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-60 sm:mt-0"
            >
                <Download className="h-3.5 w-3.5" />
                {isDownloading ? 'Memproses...' : 'Unduh Form 2'}
            </button>
        </div>
    );
}

function PendingCard({ data }) {
    return (
        <div className="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-start sm:gap-4">
            <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100">
                <Clock className="h-[22px] w-[22px] text-amber-500" />
            </div>
            <div className="flex-1">
                <p className="text-[15px] font-bold text-[#1A1A1A]">{data.companyName}</p>
                <div className="mt-0.5 flex items-center gap-1.5 text-[12px]">
                    <span className="text-gray-500">{data.position}</span>
                    <span className="text-gray-300">•</span>
                    <span className="text-gray-400">Diajukan: {data.submittedAt}</span>
                </div>
                <div className="mt-2 inline-block rounded-full bg-amber-100 px-3 py-1 text-[12px] font-bold text-amber-600">
                    Menunggu Review
                </div>
            </div>
            <div className="mt-3 sm:mt-0 sm:pt-2">
                <p className="text-[12px] italic text-gray-400">Sedang ditinjau...</p>
            </div>
        </div>
    );
}

function RejectedCard({ data }) {
    return (
        <div className="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5">
            <div className="flex items-start gap-4">
                <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                    <XCircle className="h-[22px] w-[22px] text-red-500" />
                </div>
                <div className="flex-1">
                    <p className="text-[15px] font-bold text-[#1A1A1A]">{data.companyName}</p>
                    <div className="mt-0.5 flex items-center gap-1.5 text-[12px]">
                        <span className="text-gray-500">{data.position}</span>
                        <span className="text-gray-300">•</span>
                        <span className="text-gray-400">Diajukan: {data.submittedAt}</span>
                    </div>
                    <div className="mt-2 inline-block rounded-full bg-red-100 px-3 py-1 text-[12px] font-bold text-red-500">
                        Ditolak
                    </div>
                </div>
            </div>

            {data.rejectionReason && (
                <div className="mt-3 flex items-start gap-2 rounded-lg border border-red-100 bg-red-50 px-3.5 py-3">
                    <Info className="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-red-400" />
                    <p className="text-[12px] leading-relaxed text-red-500">
                        <span className="font-bold text-red-600">Alasan Penolakan:</span>{' '}
                        {data.rejectionReason}
                    </p>
                </div>
            )}
        </div>
    );
}
export default function IndependentFilledState({
    submissions = [],
    onCreateNew,
    isLocked = false,
}) {
    return (
        <div className="flex flex-col">
            <div className="mb-4 mt-8 flex items-center justify-between">
                <div className="flex items-center gap-2.5">
                    <h2 className="text-[18px] font-bold text-[#1A1A1A]">
                        Pengajuan Form 2 Saya
                    </h2>
                    <div className="rounded-full bg-gray-200 px-2.5 py-0.5 text-[12px] font-bold text-gray-600">
                        {submissions.length}
                    </div>
                </div>
                <button
                    type="button"
                    onClick={isLocked ? undefined : onCreateNew}
                    disabled={isLocked}
                    className={`flex items-center gap-2 rounded-lg px-[18px] py-2.5 text-[13px] font-bold transition-colors ${
                        isLocked
                            ? 'cursor-not-allowed bg-gray-200 text-gray-400'
                            : 'bg-primary text-white hover:bg-primary-hover'
                    }`}
                >
                    <FileEdit className="h-4 w-4" />
                    Ajukan Baru
                </button>
            </div>
            <div className="flex flex-col gap-3">
                {submissions.map((submission) => {
                    if (submission.status === 'Disetujui') {
                        return <ApprovedCard key={submission.id} data={submission} />;
                    }
                    if (submission.status === 'Menunggu Review') {
                        return <PendingCard key={submission.id} data={submission} />;
                    }
                    if (submission.status === 'Ditolak') {
                        return <RejectedCard key={submission.id} data={submission} />;
                    }
                    return null;
                })}
            </div>
        </div>
    );
}
