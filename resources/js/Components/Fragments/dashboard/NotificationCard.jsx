import React from 'react';
import { Info } from 'lucide-react';

export default function NotificationCard({ notifications = [] }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6">
            <div className="mb-4 flex items-center justify-between">
                <h3 className="text-base font-bold text-gray-900">Notifikasi</h3>
                <button
                    type="button"
                    className="text-[11px] font-bold uppercase tracking-wider text-primary hover:underline"
                >
                    Lihat Semua
                </button>
            </div>
            {notifications.length === 0 ? (
                <p className="py-4 text-center text-sm text-gray-400">
                    Belum ada notifikasi.
                </p>
            ) : (
                <ul className="flex flex-col gap-4">
                    {notifications.map((item, idx) => (
                        <li key={idx} className="flex gap-3">
                            <Info className="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
                            <div className="min-w-0 flex-1">
                                <p className="text-sm leading-relaxed text-gray-700">
                                    {item.message}
                                </p>
                                <span className="mt-1 block text-xs text-gray-400">
                                    {item.time}
                                </span>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
