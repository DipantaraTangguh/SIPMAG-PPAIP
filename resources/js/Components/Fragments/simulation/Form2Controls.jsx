/**
 * components/simulation/panels/Form2Controls.jsx
 * Form 2 approval/rejection controls per submission.
 */
import React from 'react';

export default function Form2Controls({ sim }) {
    if (!sim.form2Submissions || sim.form2Submissions.length === 0) return null;

    return (
        <div className="mt-1 border-t border-gray-700 pt-3">
            <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Form 2 Controls
            </p>
            <p className="mb-2 text-[10px] italic text-gray-500">
                ACC = progres ke Pengajuan Pembimbing. Tolak = status tetap ApprovedForm1.
            </p>
            <div className="flex flex-col gap-2">
                {sim.form2Submissions.map((s) => (
                    <div key={s.id} className="rounded-lg bg-gray-800 p-2 text-xs">
                        <p className="mb-1 truncate font-bold text-white" title={s.companyName}>
                            {s.companyName.substring(0, 20)}{s.companyName.length > 20 ? '...' : ''}
                        </p>
                        <p className="mb-1 text-[10px] text-gray-400">
                            {s.status}
                        </p>
                        {s.status === 'Menunggu Review' && (
                            <div className="flex gap-1">
                                <button
                                    type="button"
                                    onClick={() => sim.simulateApproveForm2(s.id)}
                                    className="flex-1 rounded bg-green-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-green-700"
                                >
                                    ✅ ACC
                                </button>
                                <button
                                    type="button"
                                    onClick={() => sim.simulateRejectForm2(s.id)}
                                    className="flex-1 rounded bg-red-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-red-700"
                                >
                                    ❌ Tolak
                                </button>
                            </div>
                        )}
                        {s.status === 'Disetujui' && (
                            <p className="text-[10px] text-green-400">✅ Disetujui</p>
                        )}
                        {s.status === 'Ditolak' && (
                            <p className="text-[10px] text-red-400">❌ Ditolak</p>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
