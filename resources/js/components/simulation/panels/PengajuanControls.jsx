/**
 * components/simulation/panels/PengajuanControls.jsx
 * DPM assignment and pengajuan status display.
 */
import React from 'react';

export default function PengajuanControls({ sim, status }) {
    return (
        <>
            {sim.pengajuanPembimbing && !sim.student.dpm && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Pengajuan Pembimbing
                    </p>
                    <div className="mb-2 rounded-lg bg-gray-800 p-2">
                        <p className="truncate text-[11px] font-bold text-white">
                            {sim.pengajuanPembimbing.namaPerusahaan}
                        </p>
                        <p className="text-[10px] text-gray-400">
                            Menunggu penugasan DPM
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={sim.assignDPM}
                        className="w-full rounded-lg bg-green-600 py-2 px-2 text-[11px] font-bold text-white hover:bg-green-700"
                    >
                        Simulasi: Kaprodi Tugaskan DPM
                    </button>
                </div>
            )}

            {sim.student.dpm && !['LogbookComplete', 'MenungguSidang', 'SiklusSelesai'].includes(status) && (
                <div className="mt-1 border-t border-gray-700 pt-3">
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Pengajuan Pembimbing
                    </p>
                    <p className="text-[11px] font-bold text-green-400">
                        ✅ DPM: {sim.student.dpm.name.substring(0, 22)}{sim.student.dpm.name.length > 22 ? '...' : ''}
                    </p>
                </div>
            )}
        </>
    );
}
