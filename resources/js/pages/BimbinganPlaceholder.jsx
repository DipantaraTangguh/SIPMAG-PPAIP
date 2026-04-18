/**
 * BimbinganPlaceholder.jsx
 * Temporary placeholder page for the Bimbingan & Logbook module.
 * Will be replaced with the full implementation when sliced.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { BookOpen } from 'lucide-react';
import DashboardLayout from '../layouts/DashboardLayout';

export default function BimbinganPlaceholder() {
    const navigate = useNavigate();

    return (
        <DashboardLayout pageTitle="Bimbingan & Logbook">
            <div className="flex flex-col items-center justify-center h-96 text-center">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary-pale mb-4">
                    <BookOpen className="h-7 w-7 text-primary" />
                </div>
                <h2 className="text-xl font-bold text-gray-800 mb-2">
                    Bimbingan & Logbook
                </h2>
                <p className="text-gray-500 text-sm max-w-xs">
                    Halaman ini sedang dalam pengembangan.
                    Fitur pengajuan pembimbing akan segera tersedia.
                </p>
                <button
                    type="button"
                    onClick={() => navigate('/dashboard')}
                    className="mt-6 rounded-lg bg-primary px-6 py-2 text-sm font-bold text-white hover:bg-primary-hover"
                >
                    Kembali ke Beranda
                </button>
            </div>
        </DashboardLayout>
    );
}
