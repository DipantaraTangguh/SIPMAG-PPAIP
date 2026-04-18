/**
 * src/pages/Form2NewPage.jsx
 * Page component for creating a new Form 2 submission (Surat Pengantar Magang).
 */
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    ArrowLeft,
    Lock,
    Info,
    BookOpen,
    ArrowRight,
    Lightbulb,
    Loader2,
} from 'lucide-react';
import DashboardLayout from '../layouts/DashboardLayout';
import { useSimulation } from '../context/SimulationContext';
import { canSubmitForm2 } from '../utils/accessUtils';

export default function Form2NewPage() {
    const navigate = useNavigate();
    const { submitForm2, student } = useSimulation();

    // Hard gate — redirect if Form 1 not approved
    useEffect(() => {
        if (!canSubmitForm2(student.accessStatus)) {
            navigate('/portal', {
                state: {
                    activeTab: 'mandiri',
                    blockedReason: 'form1_required',
                },
            });
        }
    }, [student.accessStatus, navigate]);

    // Local state for form data
    const [formData, setFormData] = useState({
        namaPerusahaan: '',
        alamatPerusahaan: '',
        tanggalMulai: '',
        tanggalSelesai: '',
        lingkupMagang: '',
    });

    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Read-only user data
    const studentName = 'Tangguh Dipantara';
    const studentNim = '2021031045';

    // Validation check
    const isFormValid =
        formData.namaPerusahaan.trim() !== '' &&
        formData.alamatPerusahaan.trim() !== '' &&
        formData.tanggalMulai !== '' &&
        formData.tanggalSelesai !== '' &&
        formData.lingkupMagang.trim() !== '';

    // Handle input changes
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));

        // Clear error when user types
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleSubmit = () => {
        const newErrors = {};
        if (!formData.namaPerusahaan.trim())
            newErrors.namaPerusahaan = 'Nama perusahaan wajib diisi';
        if (!formData.alamatPerusahaan.trim())
            newErrors.alamatPerusahaan = 'Alamat perusahaan wajib diisi';
        if (!formData.tanggalMulai)
            newErrors.tanggalMulai = 'Tanggal mulai wajib diisi';
        if (!formData.tanggalSelesai)
            newErrors.tanggalSelesai = 'Tanggal selesai wajib diisi';
        if (!formData.lingkupMagang.trim())
            newErrors.lingkupMagang = 'Lingkup magang wajib diisi';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setIsSubmitting(true);

        setTimeout(() => {
            submitForm2(formData);
            setIsSubmitting(false);
            navigate('/portal', { state: { activeTab: 'mandiri' } });
        }, 1000);
    };

    return (
        <DashboardLayout pageTitle="Portal Magang">
            {/* Kembali Button */}
            <button
                type="button"
                onClick={() => navigate(-1)}
                className="mb-6 flex items-center gap-2 rounded-lg bg-primary px-[18px] py-2 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover"
            >
                <ArrowLeft className="h-4 w-4" />
                Kembali
            </button>

            {/* Page Heading */}
            <div className="mb-6">
                <h1 className="text-[28px] font-bold text-[#1A1A1A]">
                    Ajukan Form 2 — Surat Pengantar Magang
                </h1>
                <p className="mt-1 text-[14px] leading-relaxed text-gray-500">
                    Isi data perusahaan yang akan Anda tuju untuk permohonan
                    surat pengantar resmi dari Universitas.
                </p>
            </div>

            {/* Two-Column Layout */}
            <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
                {/* LEFT COLUMN: Form Container */}
                <div>
                    <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-8">
                        {/* ROW 1: Read-only student info */}
                        <div className="grid grid-cols-2 gap-5">
                            {/* NAMA LENGKAP */}
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    NAMA LENGKAP
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        readOnly
                                        value={studentName}
                                        className="h-12 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 pr-11 text-gray-700 outline-none"
                                    />
                                    <Lock className="absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                </div>
                            </div>

                            {/* NIM / ID MAHASISWA */}
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    NIM / ID MAHASISWA
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        readOnly
                                        value={studentNim}
                                        className="h-12 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 pr-11 text-gray-700 outline-none"
                                    />
                                    <Lock className="absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        {/* Thin divider */}
                        <div className="my-5 border-b border-gray-100"></div>

                        {/* ROW 2: NAMA PERUSAHAAN */}
                        <div className="mb-5">
                            <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                NAMA PERUSAHAAN / INSTANSI
                            </label>
                            <input
                                type="text"
                                name="namaPerusahaan"
                                value={formData.namaPerusahaan}
                                onChange={handleChange}
                                placeholder="Contoh: PT GoTo Gojek Tokopedia Tbk"
                                className={`h-12 w-full rounded-lg border px-3.5 outline-none transition-all focus:ring-2 focus:ring-primary/10 ${
                                    errors.namaPerusahaan
                                        ? 'border-red-400 focus:border-red-400'
                                        : 'border-gray-200 focus:border-primary'
                                }`}
                            />
                            {errors.namaPerusahaan && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.namaPerusahaan}
                                </p>
                            )}
                        </div>

                        {/* ROW 3: ALAMAT LENGKAP */}
                        <div className="mb-5">
                            <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                ALAMAT LENGKAP PERUSAHAAN DAN KODE POS
                            </label>
                            <textarea
                                name="alamatPerusahaan"
                                rows="3"
                                value={formData.alamatPerusahaan}
                                onChange={handleChange}
                                placeholder="Contoh: PT GoTo Gojek Tokopedia Tbk, Jl. Iskandarsyah II No. 2, Kebayoran Baru, Jakarta Selatan, 12160"
                                className={`w-full resize-none rounded-lg border p-3.5 outline-none transition-all focus:ring-2 focus:ring-primary/10 ${
                                    errors.alamatPerusahaan
                                        ? 'border-red-400 focus:border-red-400'
                                        : 'border-gray-200 focus:border-primary'
                                }`}
                            />
                            {errors.alamatPerusahaan && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.alamatPerusahaan}
                                </p>
                            )}
                        </div>

                        {/* ROW 4: TANGGAL MULAI & TANGGAL SELESAI */}
                        <div className="mb-5 grid grid-cols-2 gap-5">
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    TANGGAL MULAI
                                </label>
                                <input
                                    type="date"
                                    name="tanggalMulai"
                                    value={formData.tanggalMulai}
                                    onChange={handleChange}
                                    className={`h-12 w-full rounded-lg border px-3.5 outline-none transition-colors ${
                                        errors.tanggalMulai
                                            ? 'border-red-400 focus:border-red-400'
                                            : 'border-gray-200 focus:border-primary'
                                    }`}
                                />
                                {errors.tanggalMulai && (
                                    <p className="mt-1.5 text-[12px] text-red-500">
                                        {errors.tanggalMulai}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    TANGGAL SELESAI
                                </label>
                                <input
                                    type="date"
                                    name="tanggalSelesai"
                                    value={formData.tanggalSelesai}
                                    onChange={handleChange}
                                    className={`h-12 w-full rounded-lg border px-3.5 outline-none transition-colors ${
                                        errors.tanggalSelesai
                                            ? 'border-red-400 focus:border-red-400'
                                            : 'border-gray-200 focus:border-primary'
                                    }`}
                                />
                                {errors.tanggalSelesai && (
                                    <p className="mt-1.5 text-[12px] text-red-500">
                                        {errors.tanggalSelesai}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* ROW 5: LINGKUP MAGANG */}
                        <div>
                            <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                LINGKUP MAGANG
                            </label>
                            <textarea
                                name="lingkupMagang"
                                rows="4"
                                value={formData.lingkupMagang}
                                onChange={handleChange}
                                placeholder="Deskripsikan secara singkat rencana divisi dan tugas Anda..."
                                className={`w-full resize-none rounded-lg border p-3.5 outline-none transition-all focus:ring-2 focus:ring-primary/10 ${
                                    errors.lingkupMagang
                                        ? 'border-red-400 focus:border-red-400'
                                        : 'border-gray-200 focus:border-primary'
                                }`}
                            />
                            {errors.lingkupMagang && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.lingkupMagang}
                                </p>
                            )}

                            {/* Helper Text */}
                            <div className="mt-2 flex items-center gap-1.5 text-gray-400">
                                <Info className="h-3 w-3 flex-shrink-0" />
                                <span className="text-[12px] italic text-gray-400">
                                    Pastikan lingkup kerja relevan dengan program
                                    studi Anda.
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Submit Button */}
                    <div className="mt-6 flex justify-end">
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={!isFormValid || isSubmitting}
                            className={`flex items-center gap-2 rounded-xl px-8 py-3.5 text-[15px] font-bold transition-colors ${
                                isSubmitting || !isFormValid
                                    ? 'cursor-not-allowed bg-gray-300 text-gray-400'
                                    : 'bg-primary text-white hover:bg-primary-hover'
                            }`}
                        >
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Mengirim...
                                </>
                            ) : (
                                <>
                                    Kirim Pengajuan
                                    <ArrowRight className="h-4 w-4" />
                                </>
                            )}
                        </button>
                    </div>
                </div>

                {/* RIGHT COLUMN: Side Panel */}
                <div className="sticky top-6 self-start">
                    {/* Card 1 — Alur Form 2 */}
                    <div className="rounded-xl border border-gray-200 bg-white p-5 hover:border-primary-pale hover:shadow-sm transition-all duration-300">
                        <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                            <BookOpen className="h-[18px] w-[18px] text-primary" />
                            <h3 className="text-[15px] font-bold text-[#1A1A1A]">
                                Alur Kerja Form 2
                            </h3>
                        </div>

                        <div className="mt-5 flex flex-col">
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
                                        Lengkapi nama perusahaan, alamat, dan
                                        posisi yang Anda tuju.
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
                                        Admin Prodi akan memverifikasi data
                                        sebelum menandatangani surat.
                                    </p>
                                </div>
                            </div>

                            {/* Step 3 */}
                            <div className="flex">
                                <div className="mr-3 flex flex-col items-center">
                                    <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                        3
                                    </div>
                                </div>
                                <div className="flex-1 pb-1">
                                    <p className="text-[14px] font-bold text-[#1A1A1A]">
                                        Unduh PDF Resmi
                                    </p>
                                    <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                                        Gunakan surat bertanda tangan digital
                                        untuk melamar ke perusahaan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Card 2 — Panduan Pengisian */}
                    <div className="mt-4 rounded-xl border border-primary/20 bg-primary-pale p-5">
                        <div className="mb-5 flex items-center gap-2">
                            <Lightbulb className="h-4 w-4 text-primary" />
                            <h3 className="text-[11px] font-bold uppercase tracking-wider text-primary">
                                PANDUAN PENGISIAN
                            </h3>
                        </div>

                        <div className="flex flex-col gap-2.5">
                            {/* Guide item 1 */}
                            <div className="flex items-start gap-2.5">
                                <div className="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                                    1
                                </div>
                                <p className="text-[12px] leading-relaxed text-gray-600">
                                    Gunakan nama resmi perusahaan sesuai dengan
                                    Akta atau Web Profil mereka.
                                </p>
                            </div>

                            {/* Guide item 2 */}
                            <div className="flex items-start gap-2.5">
                                <div className="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                                    2
                                </div>
                                <p className="text-[12px] leading-relaxed text-gray-600">
                                    Proses verifikasi oleh Prodi memakan waktu 1-3
                                    hari kerja.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
