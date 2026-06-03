import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Lock, CloudUpload, ArrowRight, Info, BookOpen, FileSearch, X } from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

export default function SupervisorRequestForm({ onSubmit }) {
    const { student } = useSimulation();
    const navigate = useNavigate();

    const [formData, setFormData] = useState({
        namaPerusahaan: '',
        namaPraktisi: '',
        jabatanPraktisi: '',
        noTelepon: '',
        email: '',
        mulaiMagang: '',
        selesaiMagang: '',
        loaFile: null
    });

    const [fileError, setFileError] = useState(null);
    const [isDragging, setIsDragging] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const isFormValid =
        formData.namaPerusahaan.trim() !== '' &&
        formData.namaPraktisi.trim() !== '' &&
        formData.jabatanPraktisi.trim() !== '' &&
        formData.noTelepon.trim() !== '' &&
        formData.email.trim() !== '' &&
        formData.mulaiMagang !== '' &&
        formData.selesaiMagang !== '' &&
        formData.loaFile !== null;

    const handleFileChange = (file) => {
        if (!file) return;
        const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowed.includes(file.type)) {
            setFileError('Format tidak didukung. Gunakan PDF, JPG, atau PNG.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setFileError('Ukuran file melebihi 5MB.');
            return;
        }
        setFormData((prev) => ({ ...prev, loaFile: file }));
        setFileError(null);
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setIsDragging(false);
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFileChange(e.dataTransfer.files[0]);
        }
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (e) => {
        e.preventDefault();
        setIsDragging(false);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = () => {
        if (!isFormValid) return;
        setIsSubmitting(true);
        setTimeout(() => {
            onSubmit(formData);
            setIsSubmitting(false);
        }, 1000);
    };

    return (
        <div className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
            <div>
                <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-5 sm:p-8">
                    <h2 className="text-[20px] font-bold text-[#1A1A1A]">
                        Form Pengajuan Pembimbing Magang
                    </h2>
                    <p className="mt-1 text-[13px] text-gray-500">
                        Lengkapi data perusahaan magang Anda
                    </p>

                    <div className="mt-8 flex flex-col gap-5">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    NAMA LENGKAP
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        readOnly
                                        value={student?.name}
                                        className="h-11 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-[#F3F4F6] px-3 pr-10 text-gray-600 outline-none"
                                    />
                                    <Lock className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                </div>
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    NIM
                                </label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        readOnly
                                        value={student?.nim}
                                        className="h-11 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-[#F3F4F6] px-3 pr-10 text-gray-600 outline-none"
                                    />
                                    <Lock className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                </div>
                            </div>
                        </div>
                        <div className="border-b border-gray-100"></div>
                        <div>
                            <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NAMA PERUSAHAAN
                            </label>
                            <input
                                type="text"
                                name="namaPerusahaan"
                                value={formData.namaPerusahaan}
                                onChange={handleChange}
                                placeholder="contoh: PT Gojek Tokopedia TBK"
                                className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    NAMA PRAKTISI PEMBIMBING MAGANG
                                </label>
                                <input
                                    type="text"
                                    name="namaPraktisi"
                                    value={formData.namaPraktisi}
                                    placeholder="contoh: Budi Santoso"
                                    onChange={handleChange}
                                    className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    JABATAN PRAKTISI PEMBIMBING MAGANG
                                </label>
                                <input
                                    type="text"
                                    name="jabatanPraktisi"
                                    value={formData.jabatanPraktisi}
                                    placeholder="contoh: Supervisor"
                                    onChange={handleChange}
                                    className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NOMOR TELEPON PRAKTISI PEMBIMBING MAGANG
                            </label>
                            <input
                                type="text"
                                name="noTelepon"
                                value={formData.noTelepon}
                                placeholder="contoh: 08123456789"
                                onChange={handleChange}
                                className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />
                        </div>
                        <div>
                            <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                EMAIL PRAKTISI PEMBIMBING MAGANG
                            </label>
                            <input
                                type="email"
                                name="email"
                                value={formData.email}
                                onChange={handleChange}
                                placeholder="contoh: budisantoso@gmail.com"
                                className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />
                        </div>
                         <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    MULAI MAGANG
                                </label>
                                <input
                                    type="date"
                                    name="mulaiMagang"
                                    value={formData.mulaiMagang}
                                    onChange={handleChange}
                                    className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                            <div>
                                <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    SELESAI MAGANG
                                </label>
                                <input
                                    type="date"
                                    name="selesaiMagang"
                                    value={formData.selesaiMagang}
                                    onChange={handleChange}
                                    className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="mb-1.5 block text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                UPLOAD LOA (LETTER OF ACCEPTANCE)
                            </label>
                            {!formData.loaFile ? (
                                <div>
                                    <label
                                        onDrop={handleDrop}
                                        onDragOver={handleDragOver}
                                        onDragLeave={handleDragLeave}
                                        className={`flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 text-center transition-colors sm:p-8 ${
                                            isDragging ? 'border-primary bg-primary-pale' : 'border-gray-300 bg-white hover:border-primary hover:bg-primary-pale'
                                        }`}
                                    >
                                        <CloudUpload className="h-9 w-9 text-primary" />
                                        <p className="mt-3 text-[14px] font-bold text-[#1A1A1A]">
                                            Klik untuk unggah atau drag and drop
                                        </p>
                                        <p className="mt-1 text-[12px] text-gray-400">
                                            PDF, JPG, atau PNG (Maks. 5MB)
                                        </p>
                                        <input
                                            type="file"
                                            className="hidden"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            onChange={(e) => handleFileChange(e.target.files[0])}
                                        />
                                    </label>
                                    {fileError && <p className="mt-2 text-xs text-red-500">{fileError}</p>}
                                </div>
                            ) : (
                                <div className="flex items-center justify-between rounded-xl border border-primary bg-primary-pale p-4">
                                    <div className="flex items-center gap-3">
                                        <FileSearch className="h-6 w-6 text-primary" />
                                        <div>
                                            <p className="text-[13px] font-bold text-[#1A1A1A] line-clamp-1 break-all">
                                                {formData.loaFile.name}
                                            </p>
                                            <p className="text-[11px] text-gray-500">
                                                {(formData.loaFile.size / 1024 / 1024).toFixed(2)} MB
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setFormData((prev) => ({ ...prev, loaFile: null }))}
                                        className="rounded-full p-1.5 text-gray-400 hover:bg-white hover:text-gray-600"
                                    >
                                        <X className="h-4 w-4" />
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                <div className="mt-6 flex justify-stretch sm:justify-end">
                    <div className="flex w-full flex-col-reverse gap-3 sm:w-auto sm:flex-row sm:gap-4">
                        <button
                            type="button"
                            onClick={() => navigate('/')}
                            className="rounded-xl px-5 py-3 font-bold text-gray-600 hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={!isFormValid || isSubmitting}
                            className={`flex items-center justify-center gap-2 rounded-xl px-7 py-3 font-bold transition-all ${
                                isFormValid && !isSubmitting
                                    ? 'bg-primary text-white hover:bg-primary-hover'
                                    : 'cursor-not-allowed bg-gray-300 text-gray-400'
                            }`}
                        >
                            {isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan'}
                            {!isSubmitting && <ArrowRight className="h-4 w-4" />}
                        </button>
                    </div>
                </div>
            </div>
            <div className="self-start lg:sticky lg:top-6">
                <div className="rounded-xl border border-gray-200 bg-white p-5 hover:border-primary-pale hover:shadow-sm transition-all duration-300">
                    <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                        <BookOpen className="h-[18px] w-[18px] text-primary" />
                        <h3 className="text-[15px] font-bold text-[#1A1A1A]">Cara Kerja Pengajuan</h3>
                    </div>

                    <div className="mt-5 flex flex-col">
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                    1
                                </div>
                                <div className="my-1 w-[2px] flex-1 bg-gray-200"></div>
                            </div>
                            <div className="flex-1 pb-5">
                                <p className="text-[14px] font-bold text-[#1A1A1A]">Isi & kirim form</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Lengkapi semua informasi perusahaan dan unggah bukti penerimaan magang (LoA).</p>
                            </div>
                        </div>
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-500">
                                    2
                                </div>
                                <div className="my-1 w-[2px] flex-1 bg-gray-200"></div>
                            </div>
                            <div className="flex-1 pb-5">
                                <p className="text-[14px] font-bold text-[#1A1A1A]">Kaprodi menugaskan DPM</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Kepala Program Studi akan meninjau pengajuan dan menentukan Dosen Pembimbing Magang.</p>
                            </div>
                        </div>
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-500">
                                    3
                                </div>
                            </div>
                            <div className="flex-1 pb-1">
                                <p className="text-[14px] font-bold text-[#1A1A1A]">DPM Ditugaskan</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Nama DPM akan muncul di dashboard Anda setelah proses penugasan selesai.</p>
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 flex items-start gap-3 rounded-lg border border-primary/20 bg-primary-pale p-3 shadow-sm">
                        <Info className="mt-0.5 h-[14px] w-[14px] flex-shrink-0 text-primary" />
                        <p className="text-[12px] font-bold leading-relaxed text-primary">
                            Pastikan data perusahaan sudah benar. Perubahan data setelah pengajuan dikirim memerlukan persetujuan admin Prodi.
                        </p>
                    </div>
                </div>
                <div className="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <h3 className="text-[14px] font-bold text-[#1A1A1A]">Tips Mahasiswa</h3>
                    <p className="mt-2 text-[13px] text-gray-600">
                        Pastikan CV dan Portofolio Anda sudah diperbarui sebelum melamar ke mitra perusahaan baru.
                    </p>
                </div>
            </div>
        </div>
    );
}
