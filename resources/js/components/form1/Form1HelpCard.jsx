/**
 * Form1HelpCard.jsx
 * "Butuh Bantuan?" info card — always shown below the back button.
 */
import React from 'react';
import { HelpCircle } from 'lucide-react';

export default function Form1HelpCard() {
    return (
        <div className="rounded-xl border border-gray-200 bg-gray-50 p-5">
            <div className="mb-2 flex items-center gap-2">
                <HelpCircle className="h-[18px] w-[18px] text-primary" />
                <h4 className="text-sm font-bold text-primary">Butuh Bantuan?</h4>
            </div>
            <p className="text-[13px] leading-relaxed text-gray-600">
                Jika pengajuan Anda belum mendapatkan pembaruan lebih dari 3 hari
                kerja, silakan hubungi Sekretariat Program Studi atau melalui Pusat
                Bantuan.
            </p>
        </div>
    );
}
