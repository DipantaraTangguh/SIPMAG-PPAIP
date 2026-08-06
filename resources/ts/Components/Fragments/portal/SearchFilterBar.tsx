import React, { useState, useRef, useEffect } from 'react';
import { Search, SlidersHorizontal, X, ChevronDown } from 'lucide-react';

function FilterDropdown({ label, value, options, onChange }) {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                {label}
            </label>
            <div className="relative">
                <select
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className="h-10 w-full appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-8 text-[13px] text-gray-700 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                >
                    <option value="">Semua</option>
                    {options.map((opt) => (
                        <option key={opt} value={opt}>
                            {opt}
                        </option>
                    ))}
                </select>
                <ChevronDown className="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
            </div>
        </div>
    );
}

export default function SearchFilterBar({
    searchQuery,
    onSearchChange,
    sortBy,
    onSortChange,
    filters,
    onFilterChange,
    filterOptions,
    activeFilterCount,
}) {
    const [panelOpen, setPanelOpen] = useState(false);
    const panelRef = useRef<HTMLDivElement>(null);

    // Tutup panel saat klik di luar.
    useEffect(() => {
        function handleClickOutside(e: MouseEvent) {
            if (panelRef.current && !panelRef.current.contains(e.target as Node)) {
                setPanelOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const hasActiveFilters = activeFilterCount > 0;

    const clearAllFilters = () => {
        onFilterChange('studyProgram', '');
        onFilterChange('sistemKerja', '');
        onFilterChange('location', '');
    };

    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                {/* Search input */}
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

                {/* Filter button */}
                <div className="relative" ref={panelRef}>
                    <button
                        type="button"
                        aria-label="Filter lowongan"
                        aria-expanded={panelOpen}
                        onClick={() => setPanelOpen((prev) => !prev)}
                        className={`flex h-12 w-full items-center justify-center gap-2 rounded-xl border px-5 text-sm font-medium transition-colors sm:w-auto ${
                            hasActiveFilters
                                ? 'border-primary bg-primary-pale text-primary'
                                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                        }`}
                    >
                        <SlidersHorizontal className="h-4 w-4" />
                        Filter
                        {hasActiveFilters && (
                            <span className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                                {activeFilterCount}
                            </span>
                        )}
                    </button>

                    {/* Dropdown panel */}
                    {panelOpen && (
                        <div className="absolute right-0 top-[calc(100%+8px)] z-50 w-72 rounded-xl border border-gray-200 bg-white p-4 shadow-lg">
                            <div className="mb-3 flex items-center justify-between">
                                <span className="text-[13px] font-bold text-gray-800">
                                    Filter Lowongan
                                </span>
                                {hasActiveFilters && (
                                    <button
                                        type="button"
                                        onClick={clearAllFilters}
                                        className="flex items-center gap-1 text-[12px] font-medium text-red-500 hover:text-red-700"
                                    >
                                        <X className="h-3 w-3" />
                                        Hapus semua
                                    </button>
                                )}
                            </div>
                            <div className="flex flex-col gap-3">
                                <FilterDropdown
                                    label="Program Studi"
                                    value={filters.studyProgram}
                                    options={filterOptions.studyProgram}
                                    onChange={(v) => onFilterChange('studyProgram', v)}
                                />
                                <FilterDropdown
                                    label="Sistem Kerja"
                                    value={filters.sistemKerja}
                                    options={filterOptions.sistemKerja}
                                    onChange={(v) => onFilterChange('sistemKerja', v)}
                                />
                                <FilterDropdown
                                    label="Lokasi"
                                    value={filters.location}
                                    options={filterOptions.location}
                                    onChange={(v) => onFilterChange('location', v)}
                                />
                            </div>
                            <button
                                type="button"
                                onClick={() => setPanelOpen(false)}
                                className="mt-4 w-full rounded-lg bg-primary py-2 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover"
                            >
                                Terapkan
                            </button>
                        </div>
                    )}
                </div>

                {/* Sort button */}
                <button
                    type="button"
                    onClick={() =>
                        onSortChange(sortBy === 'terbaru' ? 'deadline' : 'terbaru')
                    }
                    aria-label={`Ganti urutan ke ${sortBy === 'terbaru' ? 'deadline terdekat' : 'terbaru'}`}
                    className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 text-sm font-semibold text-white transition-colors hover:bg-primary-hover sm:w-auto"
                >
                    <SlidersHorizontal className="h-4 w-4" />
                    Urut: {sortBy === 'terbaru' ? 'Terbaru ↓' : 'Deadline ↑'}
                </button>
            </div>

            {/* Active filter chips */}
            {hasActiveFilters && (
                <div className="flex flex-wrap gap-2">
                    {filters.studyProgram && (
                        <span className="flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary-pale px-3 py-1 text-[12px] font-medium text-primary">
                            Prodi: {filters.studyProgram}
                            <button
                                type="button"
                                onClick={() => onFilterChange('studyProgram', '')}
                                aria-label="Hapus filter program studi"
                                className="cursor-pointer"
                            >
                                <X className="h-3 w-3" />
                            </button>
                        </span>
                    )}
                    {filters.sistemKerja && (
                        <span className="flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary-pale px-3 py-1 text-[12px] font-medium text-primary">
                            Sistem Kerja: {filters.sistemKerja}
                            <button
                                type="button"
                                onClick={() => onFilterChange('sistemKerja', '')}
                                aria-label="Hapus filter sistem kerja"
                                className="cursor-pointer"
                            >
                                <X className="h-3 w-3" />
                            </button>
                        </span>
                    )}
                    {filters.location && (
                        <span className="flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary-pale px-3 py-1 text-[12px] font-medium text-primary">
                            Lokasi: {filters.location}
                            <button
                                type="button"
                                onClick={() => onFilterChange('location', '')}
                                aria-label="Hapus filter lokasi"
                                className="cursor-pointer"
                            >
                                <X className="h-3 w-3" />
                            </button>
                        </span>
                    )}
                </div>
            )}
        </div>
    );
}
