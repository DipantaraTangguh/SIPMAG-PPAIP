/**
 * components/simulation/panels/LogbookControls.jsx
 * Logbook approve/reject + demo "complete 6" shortcut.
 */
import React from 'react';

export default function LogbookControls({ sim, status }) {
    const logbookEntries = sim.logbookEntries || [];
    const pendingEntries = logbookEntries.filter((e) => e.status === 'Menunggu Review');
    const approvedCount = logbookEntries.filter((e) => e.status === 'Disetujui').length;

    if (status !== 'HasDPM') return null;

    return (
        <div className="mt-1 border-t border-gray-700 pt-3">
            <p className="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Logbook Controls
            </p>

            {/* Progress indicator */}
            <div className="mb-2 rounded-lg bg-gray-800 p-2">
                <p className="text-[11px] font-bold text-white">
                    {approvedCount}/6 Disetujui
                </p>
                <div className="mt-1 h-1.5 w-full rounded-full bg-gray-700">
                    <div
                        className="h-1.5 rounded-full bg-green-500 transition-all"
                        style={{ width: `${(approvedCount / 6) * 100}%` }}
                    />
                </div>
            </div>

            {/* List pending entries */}
            {pendingEntries.length === 0 ? (
                <p className="text-[10px] italic text-gray-500">
                    Tidak ada entri menunggu review.
                </p>
            ) : (
                pendingEntries.map((entry) => (
                    <div key={entry.id} className="mb-2 rounded-lg bg-gray-800 p-2">
                        <p className="mb-1 truncate text-[11px] font-bold text-white">
                            {entry.kegiatanHarian.substring(0, 24)}{entry.kegiatanHarian.length > 24 ? '...' : ''}
                        </p>
                        <p className="mb-1 text-[10px] text-gray-400">
                            {entry.tanggal}
                        </p>
                        <div className="flex gap-1">
                            <button
                                type="button"
                                onClick={() => sim.approveLogbookEntry(entry.id)}
                                className="flex-1 rounded bg-green-600 py-1 text-[10px] font-bold text-white hover:bg-green-700"
                            >
                                ✅ ACC
                            </button>
                            <button
                                type="button"
                                onClick={() => sim.rejectLogbookEntry(entry.id)}
                                className="flex-1 rounded bg-red-600 py-1 text-[10px] font-bold text-white hover:bg-red-700"
                            >
                                ❌ Tolak
                            </button>
                        </div>
                    </div>
                ))
            )}

            {/* Quick fill 6 button for demo speed */}
            {approvedCount < 6 && (
                <button
                    type="button"
                    onClick={sim.completeSixLogbooks}
                    className="mt-1 w-full rounded-lg bg-purple-600 py-1.5 text-[10px] font-bold text-white hover:bg-purple-700"
                >
                    ⚡ Demo: Selesaikan 6 Logbook
                </button>
            )}
        </div>
    );
}
