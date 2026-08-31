import React from 'react';
import { BookOpen, ClipboardList } from 'lucide-react';

const badgeColors = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    amber: 'bg-amber-100 text-amber-600',
    red: 'bg-red-100 text-red-600',
};

/**
 * Langkahnya mengikuti alur yang benar-benar berlaku di sistem, bukan saran
 * umum: melamar lewat portal tidak otomatis mencatat mahasiswa diterima --
 * penerimaan terjadi di luar sistem, dan yang mencatatnya adalah pengajuan
 * pembimbing magang (wajib) atau konfirmasi hasil magang (non-wajib).
 */
const PORTAL_STEPS = [
    {
        judul: 'Pilih jalurnya',
        isi: 'Tab Mitra berisi lowongan yang sudah bekerja sama dengan PPAIP. Tab Mandiri untuk perusahaan yang Anda cari sendiri, lewat pengajuan Form 2.',
    },
    {
        judul: 'Kirim lamaran',
        isi: 'Jalur Mitra: unggah CV berformat PDF maksimal 5MB. Anda boleh melamar ke lebih dari satu lowongan sekaligus.',
    },
    {
        judul: 'Tunggu kabar perusahaan',
        isi: 'Hasil seleksi disampaikan perusahaan langsung kepada Anda, di luar portal ini. Simpan surat penerimaan (LoA) yang Anda terima.',
    },
    {
        judul: 'Laporkan penerimaan',
        isi: 'Magang wajib: isi pengajuan pembimbing di menu Bimbingan & Logbook. Magang non-wajib: konfirmasi di menu Profil. Setelah DPM ditunjuk, portal tertutup untuk lamaran baru.',
    },
];

export default function ActiveApplicationsSidebar({
    applications = [],
    onTrackStatus: _onTrackStatus,
}: {
    applications?: any[];
    onTrackStatus?: (application: any) => void;
}) {
    return (
        <div className="flex flex-col gap-4">
            <div className="rounded-xl border border-gray-200 bg-white p-5">
                <h3 className="mb-4 text-base font-bold text-gray-900">
                    Lamaran Aktif
                </h3>

                {applications.length === 0 ? (
                    <div className="flex flex-col items-center py-6">
                        <ClipboardList className="h-8 w-8 text-gray-300" />
                        <p className="mt-2 text-[13px] text-gray-400">
                            Belum ada lamaran aktif
                        </p>
                    </div>
                ) : (
                    <div className="flex flex-col">
                        {applications.map((app, idx) => (
                            <div
                                key={app.id}
                                className={`pb-4 ${
                                    idx < applications.length - 1
                                        ? 'mb-4 border-b border-gray-100'
                                        : ''
                                }`}
                            >
                                <div className="flex items-start justify-between gap-2">
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-bold text-gray-900">
                                            {app.companyName}
                                        </p>
                                        <p className="mt-0.5 text-xs text-gray-500">
                                            {app.position}
                                        </p>
                                    </div>
                                    <span
                                        className={`flex-shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${
                                            badgeColors[app.statusColor] ||
                                            badgeColors.blue
                                        }`}
                                    >
                                        {app.statusLabel || app.status}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
            <div className="rounded-xl border border-gray-200 bg-white p-5">
                <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                    <BookOpen className="h-[18px] w-[18px] text-primary" />
                    <h3 className="text-[15px] font-bold text-[#1A1A1A]">
                        Panduan Portal Magang
                    </h3>
                </div>

                <div className="mt-5 flex flex-col">
                    {PORTAL_STEPS.map((step, idx) => (
                        <div key={step.judul} className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-500">
                                    {idx + 1}
                                </div>
                                {idx !== PORTAL_STEPS.length - 1 && (
                                    <div className="my-1 w-[2px] flex-1 bg-gray-200"></div>
                                )}
                            </div>
                            <div
                                className={
                                    idx === PORTAL_STEPS.length - 1
                                        ? 'flex-1'
                                        : 'flex-1 pb-5'
                                }
                            >
                                <p className="text-[14px] font-bold text-[#1A1A1A]">
                                    {step.judul}
                                </p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                                    {step.isi}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
