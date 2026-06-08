import React from 'react';
import { Search, SlidersHorizontal } from 'lucide-react';

export default function SearchFilterBar({
    searchQuery,
    onSearchChange,
    sortBy,
    onSortChange,
}) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="relative flex-1">
                <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Cari lowongan magang..."
                    aria-label="Cari lowongan magang"
                    className="h-12 w-full rounded-xl border border-gray-200 bg-white pl-10 pr-4 text-sm text-gray-900 outline-none transition-colors placeholder:text-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/10"
                />
            </div>
            <button
                type="button"
                aria-label="Filter lowongan"
                className="flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50 sm:w-auto"
            >
                <SlidersHorizontal className="h-4 w-4" />
                Filter
            </button>
            <button
                type="button"
                onClick={() =>
                    onSortChange(sortBy === 'terbaru' ? 'deadline' : 'terbaru')
                }
                aria-label={`Urutkan berdasarkan ${sortBy === 'terbaru' ? 'terbaru' : 'deadline'}`}
                className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-semibold text-white transition-colors hover:bg-primary-hover sm:w-auto"
            >
                <SlidersHorizontal className="h-4 w-4" />
                {sortBy === 'terbaru' ? 'Terbaru' : 'Deadline'}
            </button>
        </div>
    );
}
