/**
 * Form1BackButton.jsx
 * "Kembali ke Beranda" navigation card — always shown below the status panel.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

export default function Form1BackButton() {
    const navigate = useNavigate();

    return (
        <div className="rounded-xl border border-gray-200 bg-white p-4">
            <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
            >
                <ArrowLeft className="h-4 w-4" />
                Kembali ke Beranda
            </button>
        </div>
    );
}
