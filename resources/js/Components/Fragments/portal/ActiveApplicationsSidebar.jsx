import React from 'react';
import { ClipboardList } from 'lucide-react';

const badgeColors = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    amber: 'bg-amber-100 text-amber-600',
    red: 'bg-red-100 text-red-600',
};

export default function ActiveApplicationsSidebar({
    applications = [],
    onTrackStatus,
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
                                        {app.status}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
            <div className="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <h4 className="text-sm font-bold text-primary">
                    Tips Mahasiswa
                </h4>
                <p className="mt-2 text-[13px] leading-relaxed text-gray-600">
                    Pastikan CV dan Portofolio Anda sudah diperbarui sebelum
                    melamar ke mitra perusahaan baru.
                </p>
                <button
                    type="button"
                    className="mt-3 text-[13px] font-bold text-primary hover:underline"
                >
                    Lihat Panduan CV →
                </button>
            </div>
        </div>
    );
}
