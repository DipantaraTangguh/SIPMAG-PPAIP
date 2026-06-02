/**
 * components/defense/DefenseLockedState.jsx
 * Locked state for Sidang Magang page when prerequisites are not met.
 * Extracted from InternshipDefensePage.jsx lines 61–140.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Lock, BookOpen } from 'lucide-react';

export default function DefenseLockedState({ accessStatus }) {
    const navigate = useNavigate();

    const states = {
        Unverified: {
            icon: <Lock className="h-14 w-14 text-gray-400" />,
            iconBg: 'bg-gray-100',
            title: 'Akses Terbatas',
            description: 'Anda perlu menyelesaikan Form 1 terlebih dahulu untuk dapat mengakses fitur Sidang Magang.',
            buttons: (
                <button
                    onClick={() => navigate('/form1')}
                    className="mt-6 w-full rounded-lg bg-primary px-6 py-3 font-bold text-white transition-colors hover:bg-primary-hover sm:w-auto"
                >
                    Isi Form 1 Sekarang →
                </button>
            )
        },
        PendingReview: {
            icon: <Lock className="h-14 w-14 text-gray-400" />,
            iconBg: 'bg-gray-100',
            title: 'Halaman Belum Dapat Diakses',
            description: 'Selesaikan persyaratan sebelumnya (Form 1, Pengajuan Lamaran, dan Logbook) untuk membuka akses Sidang Magang.',
            buttons: (
                <button
                    onClick={() => navigate('/dashboard')}
                    className="mt-6 w-full rounded-lg bg-primary px-6 py-3 font-bold text-white transition-colors hover:bg-primary-hover sm:w-auto"
                >
                    Kembali ke Beranda
                </button>
            )
        },
        HasDPM: {
            icon: <BookOpen className="h-14 w-14 text-primary" />,
            iconBg: 'bg-primary-pale',
            title: 'Selesaikan Logbook Anda',
            description: 'Anda harus menyelesaikan dan mendapatkan persetujuan untuk 6 entri logbook sebelum dapat mendaftar Sidang Magang.',
            buttons: (
                <button
                    onClick={() => navigate('/guidance')}
                    className="mt-6 w-full rounded-lg bg-primary px-6 py-3 font-bold text-white transition-colors hover:bg-primary-hover sm:w-auto"
                >
                    Lanjutkan Mengisi Logbook →
                </button>
            )
        }
    };

    const config = states[accessStatus] || {
        icon: <Lock className="h-14 w-14 text-gray-400" />,
        iconBg: 'bg-gray-100',
        title: 'Halaman Belum Dapat Diakses',
        description: 'Anda perlu menyelesaikan tahap Bimbingan & Logbook terlebih dahulu sebelum dapat mengakses fitur Sidang Magang.',
        buttons: (
            <button
                onClick={() => navigate('/dashboard')}
                className="mt-6 w-full rounded-lg bg-primary px-6 py-3 font-bold text-white transition-colors hover:bg-primary-hover sm:w-auto"
            >
                Kembali ke Beranda
            </button>
        )
    };

    return (
        <div className="mx-auto mt-12 w-full max-w-[560px]">
            <div className="rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:p-12">
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
