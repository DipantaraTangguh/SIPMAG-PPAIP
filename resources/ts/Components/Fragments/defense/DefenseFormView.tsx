import React, { useState } from 'react';
import { Check, ArrowRight, AlertCircle, Loader2 } from 'lucide-react';
import { useDefenseWorkflow } from '../../../context/StudentWorkflowContext';
import FileDropInput from '../../Elements/FileDropInput';

// View utama buat upload berkas sidang.
export default function DefenseFormView() {
    const { submitSidang } = useDefenseWorkflow();

    const [files, setFiles] = useState({
        laporanAkhir: null,
        posterPresentasi: null,
        fotoKegiatan1: null,
        fotoKegiatan2: null
    });

    const [checks, setChecks] = useState({
        check1: false,
        check2: false
    });

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);

    const isFormValid = 
        Object.values(files).every(f => f !== null) &&
        checks.check1 && 
        checks.check2;

    const toggleCheck = (key) => {
        setChecks(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const handleSubmit = async () => {
        if (!isFormValid || isSubmitting) return;
        setIsSubmitting(true);
        setSubmitError(null);
        try {
            await submitSidang(files);
        } catch (err) {
            console.error('[Sidang] Submit error:', err);
            setSubmitError(err?.message || 'Gagal mengirim dokumen. Silakan coba lagi.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="animate-in fade-in duration-500">
            <p className="mb-6 text-[14px] text-gray-500">
                Pengajuan verifikasi dokumen akhir dan jadwal sidang.
            </p>

            <div className="flex flex-col items-start gap-8 lg:flex-row">
                <div className="w-full flex-1 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
                    <h2 className="font-bold text-[20px] text-[#1A1A1A] mb-6">
                        Lengkapi Dokumen Sidang
                    </h2>

                    <div className="flex flex-col gap-6">
                        <FileDropInput
                            label="Laporan Magang Akhir (PDF)"
                            hint="PDF (maks. 10MB)"
                            accept=".pdf"
                            allowedTypes={['application/pdf']}
                            maxSizeMB={10}
                            value={files.laporanAkhir}
                            onChange={(file) => setFiles((prev) => ({ ...prev, laporanAkhir: file }))}
                        />
                        <FileDropInput
                            label="Poster Presentasi (PDF)"
                            hint="PDF rasio 3:4 (maks. 5MB)"
                            accept=".pdf"
                            allowedTypes={['application/pdf']}
                            maxSizeMB={5}
                            value={files.posterPresentasi}
                            onChange={(file) => setFiles((prev) => ({ ...prev, posterPresentasi: file }))}
                        />
                        <FileDropInput
                            label="Foto Kegiatan Magang 1 (PDF)"
                            hint="PDF rasio 3:4 (maks. 5MB)"
                            accept=".pdf"
                            allowedTypes={['application/pdf']}
                            maxSizeMB={5}
                            value={files.fotoKegiatan1}
                            onChange={(file) => setFiles((prev) => ({ ...prev, fotoKegiatan1: file }))}
                        />
                        <FileDropInput
                            label="Foto Kegiatan Magang 2 (PDF)"
                            hint="PDF rasio 3:4 (maks. 5MB)"
                            accept=".pdf"
                            allowedTypes={['application/pdf']}
                            maxSizeMB={5}
                            value={files.fotoKegiatan2}
                            onChange={(file) => setFiles((prev) => ({ ...prev, fotoKegiatan2: file }))}
                        />
                    </div>

                    <div className="mt-8 flex flex-col gap-4 border-t border-gray-100 pt-8">
                        <label className="flex items-start gap-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                checked={checks.check1}
                                onChange={() => toggleCheck('check1')}
                                className="sr-only"
                            />
                            <div
                                className={`w-5 h-5 rounded border-2 flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors group-focus-within:ring-2 group-focus-within:ring-primary/20 group-hover:border-primary/50 ${
                                    checks.check1 ? 'border-primary bg-primary' : 'border-gray-300 bg-white'
                                }`}
                            >
                                {checks.check1 && <Check size={12} className="text-white" />}
                            </div>
                            <span 
                                className="text-gray-600 text-[13px] leading-relaxed select-none"
                            >
                                Saya menyatakan bahwa seluruh dokumen yang saya unggah adalah asli dan telah melalui proses bimbingan yang sah. Ketidaksesuaian data dapat membatalkan pengajuan sidang saya.
                            </span>
                        </label>
                        <label className="flex items-start gap-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                checked={checks.check2}
                                onChange={() => toggleCheck('check2')}
                                className="sr-only"
                            />
                            <div
                                className={`w-5 h-5 rounded border-2 flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors group-focus-within:ring-2 group-focus-within:ring-primary/20 group-hover:border-primary/50 ${
                                    checks.check2 ? 'border-primary bg-primary' : 'border-gray-300 bg-white'
                                }`}
                            >
                                {checks.check2 && <Check size={12} className="text-white" />}
                            </div>
                            <span 
                                className="text-gray-600 text-[13px] leading-relaxed select-none"
                            >
                                Saya bersedia memberikan hak publikasi foto dokumentasi magang saya kepada pihak universitas untuk diunggah di Repository Magang Universitas Bakrie sebagai referensi kegiatan bagi civitas akademika dan masyarakat umum. Publikasi tersebut dipastikan tidak mengandung data teknis maupun rincian proyek yang bersifat konfidensial dari perusahaan tempat saya melaksanakan magang.
                            </span>
                        </label>
                    </div>

                    {submitError && (
                        <div className="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3" role="alert">
                            <AlertCircle className="mt-0.5 h-4 w-4 flex-shrink-0 text-red-500" />
                            <p className="text-[13px] font-medium text-red-600">{submitError}</p>
                        </div>
                    )}
                    <button
                        onClick={handleSubmit}
                        disabled={!isFormValid || isSubmitting}
                        className={`mt-4 w-full py-4 rounded-xl font-bold text-[16px] flex items-center justify-center gap-2 transition-colors ${
                            (!isFormValid || isSubmitting)
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed border border-gray-300'
                            : 'bg-primary text-white hover:bg-primary-hover shadow-md hover:shadow-lg'
                        }`}
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="animate-spin h-5 w-5 text-white mr-2" />
                                Mengirim Dokumen...
                            </>
                        ) : (
                            <>
                                Daftar Sidang Magang <ArrowRight size={20} />
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
