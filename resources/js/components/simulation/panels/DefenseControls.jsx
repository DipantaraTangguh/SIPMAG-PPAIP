/**
 * components/simulation/panels/SidangControls.jsx
 * Sidang schedule assignment, cycle completion, and reset controls.
 */
import React from 'react';

export default function SidangControls({ sim, status }) {
    return (
        <>
            {/* Show when logbook complete */}
            {status === 'LogbookComplete' && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="text-[11px] font-bold text-green-400">
                        🎓 6/6 Logbook Selesai
                    </p>
                    <p className="mt-1 text-[10px] text-gray-500">
                        Sidang Magang telah terbuka.
                    </p>
                </div>
            )}

            {/* Awaiting schedule */}
            {status === 'MenungguSidang' && !sim.sidangSchedule && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Sidang Controls
                    </p>
                    <div className="mb-2 rounded-lg bg-gray-800 p-2">
                        <p className="text-[11px] font-bold text-white">Dokumen Terkirim</p>
                        <p className="text-[10px] text-gray-400">Menunggu penetapan jadwal PPAIP</p>
                    </div>
                    <button
                        type="button"
                        onClick={sim.assignSidangSchedule}
                        className="w-full rounded-lg bg-blue-600 px-2 py-2 text-[11px] font-bold text-white hover:bg-blue-700"
                    >
                        📅 Tetapkan Jadwal Sidang
                    </button>
                </div>
            )}

            {/* Schedule assigned */}
            {status === 'MenungguSidang' && sim.sidangSchedule && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Sidang Controls
                    </p>
                    <p className="mb-2 text-[11px] font-bold text-blue-400">
                        📅 Jadwal: {sim.sidangSchedule.tanggal.substring(0, 16)}
                    </p>
                    <button
                        type="button"
                        onClick={sim.completeSidangCycle}
                        className="w-full rounded-lg bg-green-600 px-2 py-2 text-[11px] font-bold text-white hover:bg-green-700"
                    >
                        Selesaikan Siklus Magang
                    </button>
                </div>
            )}

            {/* Cycle complete */}
            {status === 'SiklusSelesai' && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="mb-1 text-[11px] font-bold text-green-400">
                        🎓 Siklus Magang Selesai
                    </p>
                    <p className="mb-2 text-[10px] italic text-gray-500">
                        Reset untuk memulai siklus baru
                    </p>
                    <button
                        type="button"
                        onClick={sim.resetFullCycle}
                        className="w-full rounded-lg bg-orange-600 px-2 py-2 text-[11px] font-bold text-white hover:bg-orange-700"
                    >
                        🔄 Reset Siklus (PPAIP)
                    </button>
                </div>
            )}
        </>
    );
}
