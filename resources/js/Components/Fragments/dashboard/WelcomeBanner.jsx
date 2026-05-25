/**
 * WelcomeBanner.jsx
 * Full-width welcome card displayed at the top of the Beranda page.
 *
 * @prop {string} name — Student's name shown in the greeting.
 */
import React from 'react';

export default function WelcomeBanner({ name = '—' }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-8">
            <h2 className="mb-2 text-2xl font-bold text-gray-900">
                Selamat datang, {name}!
            </h2>
            <p className="max-w-2xl text-sm leading-relaxed text-gray-500">
                Pantau perkembangan magang Anda dan pastikan semua administrasi
                terpenuhi untuk kelancaran studi di Universitas Bakrie.
            </p>
        </div>
    );
}
