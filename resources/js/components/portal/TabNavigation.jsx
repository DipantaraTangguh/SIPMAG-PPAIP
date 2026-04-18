/**
 * TabNavigation.jsx
 * Pill-style tab switcher between Mitra and Mandiri.
 *
 * @prop {string}   activeTab   — "mitra" | "mandiri"
 * @prop {function} onTabChange — Callback with new tab key.
 */
import React from 'react';

const tabs = [
    { key: 'mitra', label: 'Mitra' },
    { key: 'mandiri', label: 'Mandiri' },
];

export default function TabNavigation({ activeTab, onTabChange }) {
    return (
        <div className="inline-flex w-fit rounded-full border border-gray-200 bg-gray-100 p-1 shadow-md">
            {tabs.map(({ key, label }) => {
                const active = activeTab === key;
                return (
                    <button
                        key={key}
                        type="button"
                        onClick={() => onTabChange(key)}
                        className={`rounded-full px-6 py-2 text-sm font-semibold transition-colors ${
                            active
                                ? 'bg-primary text-white'
                                : 'text-gray-600 hover:bg-gray-200'
                        }`}
                    >
                        {label}
                    </button>
                );
            })}
        </div>
    );
}
