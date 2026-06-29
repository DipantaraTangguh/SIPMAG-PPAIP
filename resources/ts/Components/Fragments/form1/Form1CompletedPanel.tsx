import React from 'react';
import { CheckCircle, Archive } from 'lucide-react';

export default function Form1CompletedPanel() {
    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-green-600 bg-white p-6">
            <div className="flex flex-col items-center text-center">
                <div className="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-green-100">
                    <CheckCircle className="h-10 w-10 text-green-600" />
                </div>
                <h3 className="mt-3 text-xl font-bold text-green-700">
                    Siklus Magang Selesai
                </h3>
                <p className="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">
                    Seluruh tahapan magang telah diselesaikan. Data Form Magang-01
                    tetap tersimpan sebagai riwayat akademik.
                </p>
            </div>
            <div className="mt-5 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <Archive className="h-5 w-5 flex-shrink-0 text-primary" />
                <p className="text-sm text-gray-600">
                    Profil ini merupakan arsip dari siklus magang yang telah selesai.
                </p>
            </div>
        </div>
    );
}
