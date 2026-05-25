/**
 * components/guidance/RequestSubmittedView.jsx
 * Submitted pengajuan view — shows readonly data + side panel.
 * Extracted from GuidancePage.jsx lines 256–466.
 */
import React from 'react';
import { BookOpen, Check, Info, Eye, Calendar } from 'lucide-react';

export default function RequestSubmittedView({ data, studentName, studentNim }) {
    return (
        <div className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
            {/* LEFT COLUMN: Data Pengajuan Card */}
            <div>
                <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-8">
                    <div className="mb-6">
                        <h2 className="text-[20px] font-bold text-[#1A1A1A]">
                            Data Pengajuan
                        </h2>
                        <p className="mt-1 text-[13px] text-gray-500">
                            Rincian permohonan magang mahasiswa
                        </p>
                    </div>

                    {/* ROW 1: Nama & NIM */}
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NAMA LENGKAP
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {studentName}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NIM
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {studentNim}
                            </p>
                        </div>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>

                    {/* ROW 2: Nama Perusahaan */}
                    <div>
                        <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            NAMA PERUSAHAAN
                        </p>
                        <p className="text-[15px] font-semibold text-gray-900">
                            {data.namaPerusahaan}
                        </p>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>

                    {/* ROW 3: Praktisi & Jabatan */}
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NAMA PRAKTISI PEMBING MAGANG
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {data.namaPraktisi}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                JABATAN PRAKTISI PEMBIMBING MAGANG
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {data.jabatanPraktisi}
                            </p>
                        </div>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>

                    {/* ROW 4: Telepon & Email */}
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                NOMOR TELEPON PRAKTISI PEMBIMBING<br />MAGANG
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {data.noTelepon}
                            </p>
                        </div>
                        <div>
                            <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                EMAIL PRAKTISI PEMBIMBING MAGANG
                            </p>
                            <p className="text-[15px] font-semibold text-gray-900">
                                {data.email}
                            </p>
                        </div>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>

                    {/* ROW 5: Periode */}
                    <div>
                        <p className="mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            PERIODE MAGANG
                        </p>
                        <p className="text-[15px] font-semibold text-gray-900">
                            {data.mulaiMagang} - {data.selesaiMagang}
                        </p>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>

                    {/* ROW 6: LoA File */}
                    <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5">
                        <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-red-100">
                            <span className="text-[11px] font-bold text-red-600">PDF</span>
                        </div>
                        <div className="flex-1">
                            <p className="text-[14px] font-bold text-[#1A1A1A]">
                                {data.loaFileName}
                            </p>
                            <p className="mt-0.5 text-[12px] text-gray-400">
                                Letter of Acceptance • {data.loaFileSize}
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={() => console.log("view LoA")}
                            className="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-white hover:text-primary"
                        >
                            <Eye className="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div className="mt-4 flex items-center gap-1.5 px-1">
                    <Calendar className="h-3.5 w-3.5 text-gray-400" />
                    <p className="text-[13px] italic text-gray-400">
                        Diajukan pada {data.submittedAt}
                    </p>
                </div>
            </div>

            {/* RIGHT COLUMN: Side Panel */}
            <div className="sticky top-6 self-start">
                <div className="rounded-xl border border-gray-200 bg-white p-5 transition-all duration-300 hover:border-primary-pale hover:shadow-sm">
                    <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                        <BookOpen className="h-[18px] w-[18px] text-primary" />
                        <h3 className="text-[15px] font-bold text-[#1A1A1A]">Cara Kerja Pengajuan</h3>
                    </div>

                    <div className="mt-5 flex flex-col">
                        {/* Step 1 */}
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-white">
                                    <Check className="h-4 w-4" />
                                </div>
                                <div className="my-1 w-[2px] flex-1 bg-primary"></div>
                            </div>
                            <div className="flex-1 pb-5">
                                <p className="text-[14px] font-bold text-[#1A1A1A]">Isi & kirim form</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Lengkapi semua informasi perusahaan dan unggah bukti penerimaan magang (LoA).</p>
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
                                <p className="text-[14px] font-bold text-[#1A1A1A]">Kaprodi menugaskan DPM</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Kepala Program Studi akan meninjau pengajuan dan menentukan Dosen Pembimbing Magang.</p>
                            </div>
                        </div>

                        {/* Step 3 */}
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-500">
                                    3
                                </div>
                            </div>
                            <div className="flex-1 pb-1">
                                <p className="text-[14px] font-bold text-gray-400">DPM Ditugaskan</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-400">Nama DPM akan muncul di dashboard Anda setelah proses penugasan selesai.</p>
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
                    <button
                        onClick={() => console.log('Placeholder: Lihat Panduan CV')}
                        className="mt-2 text-[13px] font-bold text-primary hover:underline hover:cursor-pointer"
                    >
                        Lihat Panduan CV →
                    </button>
                </div>
            </div>
        </div>
    );
}
