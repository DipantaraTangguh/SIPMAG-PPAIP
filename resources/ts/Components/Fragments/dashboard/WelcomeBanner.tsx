import React from 'react';

export default function WelcomeBanner({ name = '-' }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5 sm:p-8">
            <h2 className="mb-2 text-xl font-bold leading-tight text-gray-900 sm:text-2xl">
                Selamat datang, {name}!
            </h2>
            <p className="max-w-2xl text-sm leading-relaxed text-gray-500">
                Pantau perkembangan magang Anda dan pastikan semua administrasi
                terpenuhi untuk kelancaran studi di Universitas Bakrie.
            </p>
        </div>
    );
}
