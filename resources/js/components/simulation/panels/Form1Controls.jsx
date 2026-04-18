/**
 * components/simulation/panels/Form1Controls.jsx
 * Form 1 approval/rejection/reset buttons.
 */
import React from 'react';

export default function Form1Controls({ sim, status }) {
    if (!sim.form1Submission) return null;

    return (
        <div>
            <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Form 1
            </p>
            <div className="flex flex-col gap-1.5">
                <button
                    type="button"
                    onClick={sim.simulateApproveForm1}
                    disabled={status !== 'PendingReview'}
                    className="w-full rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    ✅ Kaprodi ACC
                </button>
                <button
                    type="button"
                    onClick={sim.simulateRejectForm1}
                    disabled={status !== 'PendingReview'}
                    className="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    ❌ Kaprodi Tolak
                </button>
                <button
                    type="button"
                    onClick={sim.resetForm1}
                    className="w-full rounded-lg bg-gray-600 px-3 py-2 text-xs font-bold text-white hover:bg-gray-500"
                >
                    🔄 Reset Form 1
                </button>
            </div>
        </div>
    );
}
