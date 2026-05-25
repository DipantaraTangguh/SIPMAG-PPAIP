/**
 * Form1PendingPanel.jsx
 * Status panel shown when form1_status === "PendingReview".
 * Amber/orange themed card indicating the submission is under review.
 */
import React from 'react';
import { Clock, Info } from 'lucide-react';

export default function Form1PendingPanel() {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-amber-500 bg-white p-6">
            {/* Header */}
            <div className="flex items-start gap-4">
                <div className="flex h-[52px] w-[52px] flex-shrink-0 items-center justify-center rounded-xl bg-amber-100">
                    <Clock className="h-7 w-7 text-amber-600" />
                </div>
                <div>
                    <h3 className="text-lg font-bold text-gray-900">Sedang Ditinjau</h3>
                    <p className="text-[13px] text-amber-600">
                        Status Pengajuan: Internal Review
                    </p>
                </div>
            </div>

            {/* Body text */}
            <p className="mt-4 text-sm leading-relaxed text-gray-600">
                Pengajuan Form 1 Anda sedang dalam proses peninjauan oleh Ketua Program
                Studi untuk memvalidasi kelayakan akademik dan kesesuaian tempat magang.
            </p>

            {/* Info box */}
            <div className="mt-4 flex gap-2.5 rounded-lg border border-amber-200 bg-amber-50 p-3">
                <Info className="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-500" />
                <p className="text-[13px] leading-relaxed text-amber-700">
                    Anda akan dapat melihat hasilnya dan mengunduh berkas yang telah
                    disetujui setelah status berubah menjadi &lsquo;Selesai&rsquo;.
                </p>
            </div>
        </div>
    );
}
