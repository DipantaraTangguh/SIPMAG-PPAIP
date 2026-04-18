/**
 * Form1RejectedPanel.jsx
 * Status panel shown when form1_status === "RejectedForm1".
 * Red themed card with rejection reason, next steps, and resubmit CTA.
 *
 * @prop {string} rejectionReason — Reason provided by Kaprodi for rejection.
 * @prop {object} formData        — Previous submission data to pass for re-fill.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { XCircle, AlertCircle, RefreshCw } from 'lucide-react';

export default function Form1RejectedPanel({ rejectionReason, formData }) {
    const navigate = useNavigate();

    const handleResubmit = () => {
        navigate('/form1', { state: { prefill: formData } });
    };

    return (
        <div className="flex flex-col gap-4">
            {/* Status card */}
            <div className="rounded-xl border border-gray-200 border-l-4 border-l-red-600 bg-white p-6">
                {/* Header */}
                <div className="flex items-start gap-4">
                    <div className="flex h-[52px] w-[52px] flex-shrink-0 items-center justify-center rounded-xl bg-red-100">
                        <XCircle className="h-7 w-7 text-red-600" />
                    </div>
                    <div>
                        <h3 className="text-lg font-bold text-gray-900">Ditolak</h3>
                        <p className="text-[13px] text-red-600">
                            Status Pengajuan: Perlu Revisi
                        </p>
                    </div>
                </div>

                {/* Body */}
                <p className="mt-4 text-sm leading-relaxed text-gray-600">
                    Maaf, pengajuan Form 1 Anda ditolak oleh Ketua Program Studi.
                    Periksa alasan penolakan dan ajukan kembali.
                </p>

                {/* Rejection reason box */}
                <div className="mt-4 rounded-lg border border-red-200 border-l-4 border-l-red-600 bg-red-50 p-4">
                    <div className="flex items-center gap-1.5">
                        <AlertCircle className="h-4 w-4 text-red-700" />
                        <p className="text-[13px] font-bold text-red-700">
                            Alasan Penolakan
                        </p>
                    </div>
                    <p className="mt-2 text-sm text-red-600">
                        {rejectionReason || '—'}
                    </p>
                    <p className="mt-2 text-xs italic text-gray-400">
                        Ditolak oleh Kaprodi
                    </p>
                </div>

                {/* Next steps */}
                <div className="mt-5">
                    <p className="mb-2 text-sm font-bold text-gray-900">
                        Langkah Selanjutnya
                    </p>
                    <ol className="list-inside list-decimal space-y-1 text-[13px] text-gray-600">
                        <li>Periksa alasan penolakan di atas</li>
                        <li>Perbaiki data yang tidak sesuai</li>
                        <li>Ajukan kembali Form 1</li>
                    </ol>
                </div>
            </div>

            {/* CTA */}
            <button
                type="button"
                onClick={handleResubmit}
                className="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-sm font-bold text-white transition-colors hover:bg-primary-hover"
            >
                <RefreshCw className="h-4 w-4" />
                Ajukan Ulang Form 1
            </button>
        </div>
    );
}
