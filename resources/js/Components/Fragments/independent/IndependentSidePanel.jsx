/**
 * IndependentSidePanel.jsx
 * The right side sticky panel displaying instructions and important notes
 * for the Independent track.
 */
import React from 'react';
import { useNavigate } from 'react-router-dom';
import { BookOpen, CheckSquare } from 'lucide-react';

export default function IndependentSidePanel() {
    const navigate = useNavigate();

    return (
        <div className="sticky top-6 self-start">
            {/* Card 1 — Cara Kerja Form 2 */}
            <div className="rounded-xl border border-gray-200 bg-white p-5">
                <div className="mb-4 flex items-center gap-2">
                    <BookOpen className="h-[18px] w-[18px] text-primary" />
                    <h3 className="text-[15px] font-bold text-[#1A1A1A]">
                        Alur Kerja Form 2
                    </h3>
                </div>

                <div className="flex flex-col">
                    {/* Step 1 */}
                    <div className="flex">
                        <div className="mr-3 flex flex-col items-center">
                            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                1
                            </div>
                            <div className="my-1 w-[2px] flex-1 bg-gray-200"></div>
                        </div>
                        <div className="flex-1 pb-5">
                            <p className="text-[14px] font-bold text-[#1A1A1A]">
                                Isi Data Perusahaan
                            </p>
                            <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                                Lengkapi nama perusahaan, alamat, dan posisi
                                yang Anda tuju.
                            </p>
                        </div>
                    </div>

                    {/* Step 2 */}
                    <div className="flex">
                        <div className="mr-3 flex flex-col items-center">
                            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                2
                            </div>
                            <div className="my-1 w-[2px] flex-1 bg-gray-200"></div>
                        </div>
                        <div className="flex-1 pb-5">
                            <p className="text-[14px] font-bold text-[#1A1A1A]">
                                Tunggu Persetujuan
                            </p>
                            <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                                Admin Prodi akan memverifikasi data sebelum
                                menandatangani surat.
                            </p>
                        </div>
                    </div>

                    {/* Step 3 */}
                    <div className="flex">
                        <div className="mr-3 flex flex-col items-center">
                            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                3
                            </div>
                            {/* No line after the last step */}
                        </div>
                        <div className="flex-1 pb-1">
                            <p className="text-[14px] font-bold text-[#1A1A1A]">
                                Unduh PDF Resmi
                            </p>
                            <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                                Gunakan surat bertanda tangan digital untuk
                                melamar ke perusahaan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 2 — Catatan Penting */}
            <div className="mt-4 rounded-xl border border-primary/20 bg-primary-pale p-5">
                <div className="mb-2.5 flex items-center gap-2">
                    <CheckSquare className="h-4 w-4 text-primary" />
                    <h3 className="text-[11px] font-bold uppercase tracking-wider text-primary">
                        CATATAN PENTING
                    </h3>
                </div>

                <p className="text-[13px] leading-relaxed text-gray-700">
                    Setelah diterima magang, Anda wajib mengunggah{' '}
                    <span className="font-bold text-primary">
                        Letter of Acceptance (LoA)
                    </span>{' '}
                    secara terpisah melalui menu{' '}
                    <span
                        className="cursor-pointer font-bold text-primary underline hover:text-primary-hover"
                        onClick={() => navigate('/guidance')}
                    >
                        Bimbingan &amp; Logbook
                    </span>{' '}
                    untuk memulai periode magang resmi.
                </p>
            </div>
        </div>
    );
}
