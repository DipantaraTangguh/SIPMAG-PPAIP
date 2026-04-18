/**
 * MandiriInfoBanner.jsx
 * Banner containing instructions for the Mandiri tab.
 */
import React from 'react';
import { Info } from 'lucide-react';

export default function MandiriInfoBanner() {
    return (
        <div className="flex items-center gap-3.5 rounded-xl border border-gray-100 border-l-4 border-l-primary bg-primary-pale px-5 py-4">
            <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-primary text-white">
                <Info className="h-4 w-4" />
            </div>
            <div className="flex-1">
                <p className="text-[15px] font-bold text-primary">
                    Informasi Pengajuan Mandiri
                </p>
                <p className="mt-1 text-[13px] leading-relaxed text-gray-600">
                    Ajukan Form 2 untuk setiap perusahaan yang ingin Anda lamar
                    secara mandiri. Setiap pengajuan yang disetujui akan
                    menghasilkan surat pengantar resmi bertanda tangan pimpinan
                    universitas.
                </p>
            </div>
        </div>
    );
}
