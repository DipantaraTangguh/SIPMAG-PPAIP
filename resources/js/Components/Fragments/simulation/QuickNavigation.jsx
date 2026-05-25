/**
 * components/simulation/panels/QuickNavigation.jsx
 * Quick navigate links section.
 */
import React from 'react';

export default function QuickNavigation({ navigate }) {
    return (
        <div>
            <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Navigate
            </p>
            <div className="flex flex-col gap-1">
                {[
                    ['→ Beranda', '/dashboard'],
                    ['→ Form 1', '/form1'],
                    ['→ Status Form 1', '/form1/status'],
                    ['→ Portal Magang', '/portal'],
                    ['→ Bimbingan', '/bimbingan'],
                    ['→ Sidang', '/sidang'],
                ].map(([label, path]) => (
                    <button
                        key={path}
                        type="button"
                        onClick={() => navigate(path)}
                        className="w-full rounded-lg bg-gray-700 px-3 py-1.5 text-left text-[11px] font-medium text-white hover:bg-gray-600"
                    >
                        {label}
                    </button>
                ))}
            </div>
        </div>
    );
}
