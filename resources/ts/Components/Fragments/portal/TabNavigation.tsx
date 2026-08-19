import React from 'react';

const tabs = [
    { key: 'mitra', label: 'Mitra' },
    { key: 'mandiri', label: 'Mandiri' },
];

export default function TabNavigation({ activeTab, onTabChange }) {
    return (
        <div className="inline-flex w-full rounded-full border border-gray-200 bg-gray-100 p-1 shadow-md sm:w-fit">
            {tabs.map(({ key, label }) => {
                const active = activeTab === key;
                return (
                    <button
                        key={key}
                        type="button"
                        onClick={() => onTabChange(key)}
                        className={`flex-1 rounded-full px-6 py-2 text-sm font-semibold transition-colors sm:flex-none ${
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
