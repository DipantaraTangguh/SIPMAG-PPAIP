import React from 'react';
import { CheckCircle, BookOpen, Check, Info, Mail } from 'lucide-react';

export default function DpmAssignedView({ dpm, onGoToLogbook }) {
    return (
        <div className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
            <div>
                <div className="rounded-xl border border-gray-200 border-l-4 border-l-green-500 bg-white p-5 sm:p-10">
                    <div className="mx-auto flex h-[72px] w-[72px] items-center justify-center rounded-full bg-green-100">
                        <CheckCircle className="h-10 w-10 text-green-500" />
                    </div>
                    
                    <h2 className="mt-5 text-center text-[24px] font-bold text-[#1A1A1A]">
                        DPM Telah Ditugaskan!
                    </h2>
                    <p className="mx-auto mt-2 max-w-[320px] text-center text-[14px] leading-relaxed text-gray-500">
                        Kaprodi telah menetapkan Dosen Pembimbing Magang untuk Anda. Mulai bimbingan sekarang.
                    </p>

                    <div className="my-6 border-b border-gray-100"></div>
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 p-5 text-center sm:flex-row sm:items-center sm:text-left">
                        <div className="flex h-[52px] w-[52px] flex-shrink-0 items-center justify-center rounded-full bg-primary text-lg font-bold text-white">
                            {dpm.initials}
                        </div>
                        <div className="flex-1">
                            <p className="text-[10px] font-medium tracking-wider text-primary uppercase">
                                DOSEN PEMBIMBING MAGANG
                            </p>
                            <p className="mt-0.5 text-[17px] font-bold text-[#1A1A1A]">
                                {dpm.name}
                            </p>
                            <p className="mt-0.5 text-[12px] text-gray-400">
                                NIDN: {dpm.nidn}
                            </p>
                            <div className="mt-2.5 flex items-center gap-1.5">
                                <Mail className="h-3.5 w-3.5 text-primary" />
                                <a href={`mailto:${dpm.email}`} className="text-[13px] text-primary hover:underline">
                                    {dpm.email}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div className="my-5 border-b border-gray-100"></div>
                    <div className="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                        <Info className="mt-0.5 h-4 w-4 flex-shrink-0 text-green-500" />
                        <p className="text-[13px] leading-relaxed text-green-700">
                            DPM Anda telah ditetapkan. Silakan mulai mengisi logbook bimbingan secara berkala sesuai ketentuan.
                        </p>
                    </div>
                    <div className="mt-5 flex flex-col gap-3">
                        <button
                            type="button"
                            onClick={onGoToLogbook}
                            className="w-full rounded-xl bg-primary py-3.5 text-[15px] font-bold text-white transition-colors hover:bg-primary-hover"
                        >
                            Mulai Isi Logbook
                        </button>
                        <button
                            type="button"
                            onClick={() => window.open(dpm.whatsappGroupLink, '_blank')}
                            className="w-full rounded-xl bg-green-500 py-3.5 text-[15px] font-bold text-white transition-colors hover:bg-green-600"
                        >
                            Join Grup Whatsapp
                        </button>
                    </div>
                </div>
            </div>
            <div className="self-start flex-shrink-0 lg:sticky lg:top-6 lg:w-[320px]">
                <div className="rounded-xl border border-gray-200 bg-white p-5 transition-all duration-300 hover:border-primary-pale hover:shadow-sm">
                    <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                        <BookOpen className="h-[18px] w-[18px] text-primary" />
                        <h3 className="text-[15px] font-bold text-[#1A1A1A]">Cara Kerja Pengajuan</h3>
                    </div>

                    <div className="mt-5 flex flex-col">
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
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-white">
                                    <Check className="h-4 w-4" />
                                </div>
                                <div className="my-1 w-[2px] flex-1 bg-primary"></div>
                            </div>
                            <div className="flex-1 pb-5">
                                <p className="text-[14px] font-bold text-[#1A1A1A]">Kaprodi menugaskan DPM</p>
                                <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">Kepala Program Studi akan meninjau pengajuan dan menentukan Dosen Pembimbing Magang.</p>
                            </div>
                        </div>
                        <div className="flex">
                            <div className="mr-3 flex flex-col items-center">
                                <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary text-white">
                                    <Check className="h-4 w-4" />
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
            </div>
        </div>
    );
}
