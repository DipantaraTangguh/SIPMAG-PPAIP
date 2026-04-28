/**
 * components/bimbingan/BimbinganLockedState.jsx
 * Locked state display for Bimbingan page when prerequisites are not met.
 * Extracted from BimbinganPage.jsx lines 37–116.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Lock, Clock, FileSearch } from 'lucide-react';

export default function BimbinganLockedState({ accessStatus }) {
    const navigate = useNavigate();

    // Configuration for different locked states
    const states = {
        Unverified: {
            icon: <Lock className="h-14 w-14 text-gray-400" />,
            iconBg: 'bg-gray-100',
            title: 'Akses Terbatas',
            description: 'Anda perlu menyelesaikan Form 1 terlebih dahulu untuk dapat mengakses fitur Bimbingan & Logbook.',
            buttons: (
                <button
                    onClick={() => navigate('/form1')}
                    className="mt-6 rounded-lg bg-primary px-6 py-3 font-bold text-white hover:bg-primary-hover"
                >
                    Isi Form 1 Sekarang →
                </button>
            )
        },
        PendingReview: {
            icon: <Clock className="h-14 w-14 text-amber-500" />,
            iconBg: 'bg-amber-100',
            title: 'Menunggu Persetujuan Form 1',
            description: 'Form 1 Anda sedang ditinjau oleh Kaprodi. Halaman ini akan dapat diakses setelah Form 1 Anda disetujui.',
            buttons: (
                <button
                    onClick={() => navigate('/form1/status')}
                    className="mt-6 rounded-lg bg-primary px-6 py-3 font-bold text-white hover:bg-primary-hover"
                >
                    Lihat Status Form 1 →
                </button>
            )
        },
        ApprovedForm1: {
            icon: <FileSearch className="h-14 w-14 text-blue-500" />,
            iconBg: 'bg-blue-100',
            title: 'Belum Ada Lamaran Aktif',
            description: 'Anda perlu mengajukan lamaran magang terlebih dahulu, baik melalui Portal Mitra maupun Magang Mandiri, sebelum dapat mengakses fitur Bimbingan dan Logbook.',
            buttons: (
                <div className="mt-6 flex justify-center gap-4">
                    <button
                        onClick={() => navigate('/portal')}
                        className="rounded-lg bg-primary px-5 py-3 font-bold text-white hover:bg-primary-hover"
                    >
                        Cari Lowongan Mitra →
                    </button>
                    <button
                        onClick={() => navigate('/portal', { state: { activeTab: 'mandiri' } })}
                        className="rounded-lg border border-primary bg-white px-5 py-3 font-bold text-primary hover:bg-primary-pale"
                    >
                        Ajukan Mandiri →
                    </button>
                </div>
            )
        }
    };

    const config = states[accessStatus] || states.Unverified;

    return (
        <div className="mx-auto mt-12 w-full max-w-[560px]">
            <div className="rounded-xl border border-gray-200 bg-white p-12 text-center">
                <div className={`mx-auto flex h-[56px] w-[56px] items-center justify-center rounded-full ${config.iconBg}`}>
                    {config.icon}
                </div>
                <h2 className="mt-4 text-[20px] font-bold text-[#1A1A1A]">
                    {config.title}
                </h2>
                <p className="mt-2 text-[14px] leading-relaxed text-gray-500">
                    {config.description}
                </p>
                {config.buttons}
            </div>
        </div>
    );
}
