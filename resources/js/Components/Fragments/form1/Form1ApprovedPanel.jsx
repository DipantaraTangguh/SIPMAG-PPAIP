/**
 * Form1ApprovedPanel.jsx
 * Status panel shown when form1_status === "ApprovedForm1".
 * Green themed card with centered success icon, approver info,
 * PDF download, and CTA to Portal Magang.
 *
 * @prop {string} pdfPath       — URL to download the signed PDF.
 * @prop {string} pdfFileName   — Display name of the PDF file.
 * @prop {string} pdfSize       — Human-readable file size (e.g. "1.2 MB").
 * @prop {string} approverName  — Name of the approving Kaprodi.
 * @prop {string} approverNidn  — NIDN of the approver.
 * @prop {string} approverRole  — Role title (e.g. "Kaprodi Informatika").
 * @prop {string} approvalDate  — Date of approval (e.g. "10/03/2026").
 */
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    CheckCircle,
    ShieldCheck,
    FileText,
    Download,
    ArrowRight,
} from 'lucide-react';
import { api } from '../../../lib/api';

export default function Form1ApprovedPanel({
    pdfPath,
    pdfFileName = 'Surat_Keterangan_Form1.pdf',
    pdfSize = 'PDF',
    approverName = '—',
    approverNidn = '—',
    approverRole = '—',
    approvalDate = '—',
    studentNim,
}) {
    const navigate = useNavigate();
    const [isDownloading, setIsDownloading] = useState(false);
    const [downloadError, setDownloadError] = useState(null);

    const handleDownload = async () => {
        setDownloadError(null);
        setIsDownloading(true);
        try {
            const filename = studentNim
                ? `Surat_Keterangan_Form1_${studentNim}.pdf`
                : pdfFileName;
            await api.download('/form1/surat-keterangan', filename);
        } catch (err) {
            console.error('[Form1] Download error:', err);
            setDownloadError(err.message || 'Gagal mengunduh dokumen.');
        } finally {
            setIsDownloading(false);
        }
    };

    return (
        <div className="flex flex-col gap-4">
            {/* Main status card */}
            <div className="rounded-xl border border-gray-200 border-l-4 border-l-green-600 bg-white p-6">
                {/* ── Centered success icon + title ── */}
                <div className="flex flex-col items-center text-center">
                    <div className="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-green-100">
                        <CheckCircle className="h-10 w-10 text-green-600" />
                    </div>
                    <h3 className="mt-3 text-[22px] font-bold text-green-600">
                        Pengajuan Disetujui!
                    </h3>
                    <p className="mx-auto mt-3 max-w-[320px] text-sm leading-relaxed text-gray-600">
                        Selamat! Form Magang-01 Anda telah disetujui oleh Program Studi.
                        Anda kini dapat mengunduh dokumen resmi dan melanjutkan ke tahap
                        pendaftaran di portal magang perusahaan.
                    </p>
                </div>

                {/* ── Divider ── */}
                <hr className="my-5 border-gray-100" />

                {/* ── Approver info card ── */}
                <div className="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div className="flex items-center gap-1.5">
                        <ShieldCheck className="h-4 w-4 text-green-600" />
                        <span className="text-[11px] font-bold uppercase tracking-wider text-green-600">
                            Disetujui Oleh:
                        </span>
                    </div>
                    <p className="mt-1.5 text-base font-bold text-gray-900">
                        {approverName}
                    </p>
                    <div className="mt-1 flex items-center gap-2 text-xs text-gray-500">
                        <span>NIDN: {approverNidn}</span>
                        <span className="text-gray-300">•</span>
                        <span>{approverRole}</span>
                    </div>
                    <p className="mt-2 text-xs italic text-green-600">
                        Digital signature verified pada {approvalDate}
                    </p>
                </div>

                {/* ── Divider ── */}
                <hr className="my-5 border-gray-100" />

                {/* ── Document download section ── */}
                <p className="mb-3 text-[11px] font-bold uppercase tracking-wider text-primary">
                    Dokumen Resmi Anda
                </p>
                <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3.5">
                    {/* PDF icon */}
                    <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-red-100">
                        <FileText className="h-5 w-5 text-red-600" />
                    </div>

                    {/* File info */}
                    <div className="min-w-0 flex-1">
                        <p className="max-w-[160px] truncate text-[13px] font-bold text-gray-900">
                            {pdfFileName}
                        </p>
                        <p className="text-[11px] uppercase tracking-wider text-gray-400">
                            Signed PDF • {pdfSize}
                        </p>
                    </div>

                    {/* Download button */}
                    <button
                        type="button"
                        onClick={handleDownload}
                        disabled={isDownloading}
                        className="flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-[13px] font-medium text-white transition-colors hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Download className="h-4 w-4" />
                        {isDownloading ? 'Memproses...' : 'Unduh'}
                    </button>
                </div>
                {downloadError && (
                    <p className="mt-2 text-xs text-red-600">{downloadError}</p>
                )}
            </div>

            {/* ── CTA button ── */}
            <button
                type="button"
                onClick={() => navigate('/portal')}
                className="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-[15px] font-bold text-white transition-colors hover:bg-primary-hover"
            >
                Lanjut ke Portal Magang
                <ArrowRight className="h-4 w-4" />
            </button>
        </div>
    );
}
