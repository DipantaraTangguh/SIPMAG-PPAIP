import React from "react";
import { CheckCircle, FileText } from "lucide-react";
import CycleResetButton from "./CycleResetButton";

/**
 * Panel status untuk magang non-wajib yang sudah selesai (ElectiveCompleted).
 * Alur non-wajib berhenti setelah surat pengantar (Form 2) disetujui PPAIP.
 */
export default function Form1NonWajibDonePanel() {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-green-600 bg-white p-6">
            <div className="flex flex-col items-center text-center">
                <div className="flex h-18 w-18 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle className="h-10 w-10 text-green-600" />
                </div>
                <h3 className="mt-3 text-xl font-bold text-green-700">
                    Magang Non-Wajib Selesai
                </h3>
                <p className="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">
                    Surat pengantar (Form 2) Anda telah disetujui atau lamaran
                    mitra Anda diterima. Proses magang non-wajib selesai sampai
                    di sini dan sudah tercatat di riwayat magang Anda.
                </p>
            </div>
            <div className="mt-5 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <FileText className="h-5 w-5 shrink-0 text-primary" />
                <p className="text-sm text-gray-600">
                    Magang non-wajib tidak melalui tahap DPM, logbook, maupun
                    sidang. Surat pengantar (bila lewat Form 2) dapat diunduh
                    dari halaman Form 2.
                </p>
            </div>
            <CycleResetButton />
        </div>
    );
}
