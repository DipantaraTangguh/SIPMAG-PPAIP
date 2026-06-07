import React, { useState, useRef } from 'react';
import {
    CheckCircle,
    Check,
    FileText,
    Image as ImageIcon,
    ImagePlus,
    FileSpreadsheet,
    FileCheck,
    ArrowRight,
    AlertCircle,
    Loader2,
} from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

// Komponen upload kecil, sengaja lokal ke form sidang.
function UploadField({ label, hint, icon: Icon, fieldKey, files, setFiles, fileErrors, setFileErrors, dragOver, setDragOver, inputRefs, maxSizeMB }) {
    const file = files[fieldKey];
    
    const onClick = () => {
        if (inputRefs[fieldKey]?.current) {
            inputRefs[fieldKey].current.click();
        }
    };

    const handleFile = (selectedFile) => {
        if (!selectedFile) return;
        
        let error = null;
        if (selectedFile.type !== 'application/pdf') {
            error = "Format tidak didukung. Gunakan file PDF.";
        } else if (selectedFile.size > maxSizeMB * 1024 * 1024) {
            error = "Ukuran file melebihi batas maksimal yang diizinkan.";
        }

        if (error) {
            setFileErrors(prev => ({ ...prev, [fieldKey]: error }));
            setFiles(prev => ({ ...prev, [fieldKey]: null }));
        } else {
            setFileErrors(prev => ({ ...prev, [fieldKey]: null }));
            setFiles(prev => ({ ...prev, [fieldKey]: selectedFile }));
        }
    };

    const formatFileSize = (bytes) => {
        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(0)} KB`;
        }
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    return (
        <div>
            <label className="text-[13px] font-semibold text-gray-700 mb-2 block">
                {label}
            </label>
            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragOver(fieldKey);
                }}
                onDragLeave={() => setDragOver(null)}
                onDrop={(e) => {
                    e.preventDefault();
                    setDragOver(null);
                    const droppedFile = e.dataTransfer.files[0];
                    handleFile(droppedFile);
                }}
                onClick={onClick}
                className={`border-2 rounded-xl p-5 text-center cursor-pointer transition-colors duration-200 sm:p-8 ${
                    dragOver === fieldKey 
                    ? 'border-primary bg-primary-pale/50' 
                    : file
                        ? 'border-solid border-green-500 bg-green-50/50'
                        : 'border-dashed border-gray-300 bg-white hover:border-primary hover:bg-primary-pale/30'
                }`}
            >
                {file ? (
                    <div className="flex flex-col items-center">
                        <FileCheck className="text-green-500 w-8 h-8" />
                        <p className="font-bold text-[14px] text-[#1A1A1A] mt-2 max-w-[300px] truncate">
                            {file.name}
                        </p>
                        <p className="text-gray-400 text-[12px] mt-0.5">
                            {formatFileSize(file.size)}
                        </p>
                        <p className="text-primary text-[12px] font-medium underline mt-1.5">
                            Ganti File
                        </p>
                    </div>
                ) : (
                    <div className="flex flex-col items-center">
                        <Icon className="text-primary w-8 h-8" />
                        <p className="font-bold text-[14px] text-[#1A1A1A] mt-3">
                            Klik untuk unggah atau drag & drop
                        </p>
                        <p className="text-gray-400 text-[12px] mt-1">
                            {hint}
                        </p>
                    </div>
                )}
            </div>
            {fileErrors[fieldKey] && (
                <p className="text-red-500 text-[12px] mt-2 flex items-center gap-1.5 font-medium">
                    <AlertCircle size={14} />
                    {fileErrors[fieldKey]}
                </p>
            )}
            <input 
                type="file" 
                ref={inputRefs[fieldKey]} 
                className="hidden" 
                accept=".pdf"
                onChange={(e) => {
                    const selected = e.target.files[0];
                    handleFile(selected);
                    // Reset value supaya file yang sama bisa dipilih ulang.
                    e.target.value = '';
                }}
            />
        </div>
    );
}

// View utama buat upload berkas sidang.
export default function DefenseFormView() {
    const { submitSidang } = useSimulation();

    const [files, setFiles] = useState({
        laporanAkhir: null,
        posterPresentasi: null,
        fotoKegiatan1: null,
        fotoKegiatan2: null,
        krsMataKuliah: null
    });

    const [checks, setChecks] = useState({
        check1: false,
        check2: false
    });

    const [fileErrors, setFileErrors] = useState({});
    const [dragOver, setDragOver] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);

    const inputRefs = {
        laporanAkhir: useRef(null),
        posterPresentasi: useRef(null),
        fotoKegiatan1: useRef(null),
        fotoKegiatan2: useRef(null),
        krsMataKuliah: useRef(null)
    };
    
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
                <div className="w-full flex-shrink-0 rounded-xl border border-l-4 border-gray-200 border-l-primary bg-white p-6 shadow-sm lg:sticky lg:top-6 lg:w-[280px]">
                    <h3 className="font-bold text-[15px] text-primary mb-4">
                        Persyaratan Umum
                    </h3>
                    <div className="flex flex-col gap-3">
                        <div className="flex items-start gap-2.5">
                            <div className="w-[22px] h-[22px] bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <CheckCircle className="text-green-500 w-[14px] h-[14px]" />
                            </div>
                            <p className="text-gray-600 text-[13px] leading-relaxed">
                                Telah menyelesaikan minimal 100 hari kerja magang.
                            </p>
                        </div>
                        <div className="flex items-start gap-2.5">
                            <div className="w-[22px] h-[22px] bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <CheckCircle className="text-green-500 w-[14px] h-[14px]" />
                            </div>
                            <p className="text-gray-600 text-[13px] leading-relaxed">
                                Logbook telah disetujui oleh Pembimbing Magang.
                            </p>
                        </div>
                        <div className="flex items-start gap-2.5">
                            <div className="w-[22px] h-[22px] bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <CheckCircle className="text-green-500 w-[14px] h-[14px]" />
                            </div>
                            <p className="text-gray-600 text-[13px] leading-relaxed">
                                Laporan akhir telah direview oleh Dosen Pembimbing.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="w-full flex-1 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
                    <h2 className="font-bold text-[20px] text-[#1A1A1A] mb-6">
                        Lengkapi Dokumen Sidang
                    </h2>

                    <div className="flex flex-col gap-6">
                        <UploadField
                            label="Laporan Magang Akhir (PDF)"
                            hint="Ukuran maksimal 10MB (Format .pdf)"
                            icon={FileText}
                            fieldKey="laporanAkhir"
                            maxSizeMB={10}
                            files={files} setFiles={setFiles}
                            fileErrors={fileErrors} setFileErrors={setFileErrors}
                            dragOver={dragOver} setDragOver={setDragOver}
                            inputRefs={inputRefs}
                        />
                        <UploadField
                            label="Poster Presentasi (PDF)"
                            hint="Rasio 3:4, Maksimal 5MB (Format .pdf)"
                            icon={ImageIcon}
                            fieldKey="posterPresentasi"
                            maxSizeMB={5}
                            files={files} setFiles={setFiles}
                            fileErrors={fileErrors} setFileErrors={setFileErrors}
                            dragOver={dragOver} setDragOver={setDragOver}
                            inputRefs={inputRefs}
                        />
                        <UploadField
                            label="Foto Kegiatan Magang 1 (PDF)"
                            hint="Rasio 3:4, Maksimal 5MB (Format .pdf)"
                            icon={ImagePlus}
                            fieldKey="fotoKegiatan1"
                            maxSizeMB={5}
                            files={files} setFiles={setFiles}
                            fileErrors={fileErrors} setFileErrors={setFileErrors}
                            dragOver={dragOver} setDragOver={setDragOver}
                            inputRefs={inputRefs}
                        />
                        <UploadField
                            label="Foto Kegiatan Magang 2 (PDF)"
                            hint="Rasio 3:4, Maksimal 5MB (Format .pdf)"
                            icon={ImagePlus}
                            fieldKey="fotoKegiatan2"
                            maxSizeMB={5}
                            files={files} setFiles={setFiles}
                            fileErrors={fileErrors} setFileErrors={setFileErrors}
                            dragOver={dragOver} setDragOver={setDragOver}
                            inputRefs={inputRefs}
                        />
                        <UploadField
                            label="KRS Bukti Pengambilan Mata Kuliah Magang (PDF)"
                            hint="Pastikan nama mata kuliah tertera jelas (Format .pdf)"
                            icon={FileSpreadsheet}
                            fieldKey="krsMataKuliah"
                            maxSizeMB={5}
                            files={files} setFiles={setFiles}
                            fileErrors={fileErrors} setFileErrors={setFileErrors}
                            dragOver={dragOver} setDragOver={setDragOver}
                            inputRefs={inputRefs}
                        />
                    </div>

                    <div className="mt-8 flex flex-col gap-4 border-t border-gray-100 pt-8">
                        <div className="flex items-start gap-3">
                            <div
                                onClick={() => toggleCheck('check1')}
                                className={`w-5 h-5 rounded border-2 flex-shrink-0 mt-0.5 cursor-pointer flex items-center justify-center transition-colors ${
                                    checks.check1 ? 'border-primary bg-primary' : 'border-gray-300 bg-white'
                                }`}
                            >
                                {checks.check1 && <Check size={12} className="text-white" />}
                            </div>
                            <p 
                                onClick={() => toggleCheck('check1')}
                                className="text-gray-600 text-[13px] leading-relaxed cursor-pointer select-none"
                            >
                                Saya menyatakan bahwa seluruh dokumen yang saya unggah adalah asli dan telah melalui proses bimbingan yang sah. Ketidaksesuaian data dapat membatalkan pengajuan sidang saya.
                            </p>
                        </div>
                        <div className="flex items-start gap-3">
                            <div
                                onClick={() => toggleCheck('check2')}
                                className={`w-5 h-5 rounded border-2 flex-shrink-0 mt-0.5 cursor-pointer flex items-center justify-center transition-colors ${
                                    checks.check2 ? 'border-primary bg-primary' : 'border-gray-300 bg-white'
                                }`}
                            >
                                {checks.check2 && <Check size={12} className="text-white" />}
                            </div>
                            <p 
                                onClick={() => toggleCheck('check2')}
                                className="text-gray-600 text-[13px] leading-relaxed cursor-pointer select-none"
                            >
                                Saya bersedia memberikan hak publikasi foto dokumentasi magang saya kepada pihak universitas untuk diunggah di Repository Magang Universitas Bakrie sebagai referensi kegiatan bagi civitas akademika dan masyarakat umum. Publikasi tersebut dipastikan tidak mengandung data teknis maupun rincian proyek yang bersifat konfidensial dari perusahaan tempat saya melaksanakan magang.
                            </p>
                        </div>
                    </div>

                    {submitError && (
                        <div className="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
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
