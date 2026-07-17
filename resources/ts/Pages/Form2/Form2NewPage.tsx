import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import {
    ArrowLeft,
    Lock,
    Info,
    CheckCircle,
    BookOpen,
    ArrowRight,
    Lightbulb,
    Loader2,
} from "lucide-react";
import DashboardLayout from "../../Components/Layouts/DashboardLayout";
import { useAuth } from "../../context/AppContext";
import { useForm2Workflow } from "../../context/StudentWorkflowContext";
import {
    canAccessPortal,
    hasSecuredInternship,
    FORM2_LOCKED_MESSAGE,
} from "../../utils/accessUtils";

export default function Form2NewPage() {
    const navigate = useNavigate();
    const { student } = useAuth();
    const { submitForm2 } = useForm2Workflow();

    const internshipSecured = hasSecuredInternship(student?.accessStatus);

    // Guard keras: Form 2 jangan kebuka sebelum Form 1 approved.
    useEffect(() => {
        if (!canAccessPortal(student?.accessStatus)) {
            navigate("/portal", {
                state: {
                    activeTab: "mandiri",
                    blockedReason: "form1_required",
                },
            });
        }
    }, [student?.accessStatus, navigate]);

    // Draft form disimpan lokal sampai submit.
    const [formData, setFormData] = useState({
        namaPerusahaan: "",
        namaPimpinan: "",
        jabatanPimpinan: "",
        alamatPerusahaan: "",
        tanggalMulai: "",
        tanggalSelesai: "",
        lingkupMagang: "",
    });

    const [errors, setErrors] = useState<
        Partial<Record<keyof typeof formData, string>>
    >({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState<string | null>(null);

    // Data profil dikunci dan selalu berasal dari user yang sedang login.
    const studentName = student?.name ?? "—";
    const studentNim = student?.nim ?? "—";

    // Validasi ringan sebelum data dilempar ke context/API.
    const isFormValid =
        formData.namaPerusahaan.trim() !== "" &&
        formData.alamatPerusahaan.trim() !== "" &&
        formData.tanggalMulai !== "" &&
        formData.tanggalSelesai !== "" &&
        formData.lingkupMagang.trim() !== "";

    // Satu handler cukup buat semua field text/date.
    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ) => {
        const { name, value } = e.target;
        const field = name as keyof typeof formData;
        setFormData((prev) => ({ ...prev, [field]: value }));

        // Begitu user edit, error field itu kita bersihin.
        if (errors[field]) {
            setErrors((prev) => ({ ...prev, [field]: undefined }));
        }
    };

    const handleSubmit = async () => {
        if (internshipSecured) {
            setSubmitError(FORM2_LOCKED_MESSAGE);
            return;
        }

        const newErrors: Partial<Record<keyof typeof formData, string>> = {};
        if (!formData.namaPerusahaan.trim())
            newErrors.namaPerusahaan = "Nama perusahaan wajib diisi";
        if (!formData.alamatPerusahaan.trim())
            newErrors.alamatPerusahaan = "Alamat perusahaan wajib diisi";
        if (!formData.tanggalMulai)
            newErrors.tanggalMulai = "Bulan mulai wajib diisi";
        if (!formData.tanggalSelesai)
            newErrors.tanggalSelesai = "Bulan selesai wajib diisi";
        if (!formData.lingkupMagang.trim())
            newErrors.lingkupMagang = "Lingkup magang wajib diisi";

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setIsSubmitting(true);
        setSubmitError(null);

        try {
            await submitForm2(formData);
            navigate("/portal", { state: { activeTab: "mandiri" } });
        } catch (err) {
            setSubmitError(
                err instanceof Error
                    ? err.message
                    : "Gagal mengirim pengajuan. Silakan coba lagi.",
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    if (internshipSecured) {
        return (
            <DashboardLayout pageTitle="Portal Magang">
                <button
                    type="button"
                    onClick={() =>
                        navigate("/portal", { state: { activeTab: "mandiri" } })
                    }
                    className="mb-6 flex items-center gap-2 rounded-lg bg-primary px-4.5 py-2 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Kembali
                </button>
                <div className="max-w-3xl rounded-xl border border-green-200 bg-green-50 p-6 sm:p-8">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-100">
                            <CheckCircle className="h-6 w-6 text-green-600" />
                        </div>
                        <div className="flex-1">
                            <h1 className="text-xl font-bold text-green-800">
                                Form 2 Tidak Dapat Diajukan Lagi
                            </h1>
                            <p className="mt-2 text-[14px] leading-relaxed text-green-700">
                                {FORM2_LOCKED_MESSAGE}
                            </p>
                            <button
                                type="button"
                                onClick={() => navigate("/guidance")}
                                className="mt-5 w-full rounded-xl bg-primary px-5 py-3 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover sm:w-auto"
                            >
                                Buka Bimbingan &amp; Logbook
                            </button>
                        </div>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout pageTitle="Portal Magang">
            <button
                type="button"
                onClick={() => navigate(-1)}
                className="mb-6 flex items-center gap-2 rounded-lg bg-primary px-4.5 py-2 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover"
            >
                <ArrowLeft className="h-4 w-4" />
                Kembali
            </button>
            <div className="mb-6">
                <h1 className="text-2xl font-bold leading-tight text-[#1A1A1A] sm:text-[28px]">
                    Ajukan Form 2 — Surat Pengantar Magang
                </h1>
                <p className="mt-1 text-[14px] leading-relaxed text-gray-500">
                    Isi data perusahaan yang akan Anda tuju untuk permohonan
                    surat pengantar resmi dari Universitas.
                </p>
            </div>
            <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
                <div>
                    <div className="rounded-xl border border-gray-200 border-l-4 border-l-primary bg-white p-5 sm:p-8">
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
                        <div className="my-5 border-b border-gray-100"></div>
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
                                        ? "border-red-400 focus:border-red-400"
                                        : "border-gray-200 focus:border-primary"
                                }`}
                            />
                            {errors.namaPerusahaan && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.namaPerusahaan}
                                </p>
                            )}
                        </div>
                        <div className="mb-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    NAMA PIMPINAN{" "}
                                    <span className="font-medium normal-case text-gray-400">
                                        (optional)
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="namaPimpinan"
                                    value={formData.namaPimpinan}
                                    onChange={handleChange}
                                    placeholder="Contoh: Budi Santoso"
                                    className="h-12 w-full rounded-lg border border-gray-200 px-3.5 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    JABATAN{" "}
                                    <span className="font-medium normal-case text-gray-400">
                                        (optional)
                                    </span>
                                </label>
                                <input
                                    type="text"
                                    name="jabatanPimpinan"
                                    value={formData.jabatanPimpinan}
                                    onChange={handleChange}
                                    placeholder="Contoh: Direktur Utama"
                                    className="h-12 w-full rounded-lg border border-gray-200 px-3.5 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                        </div>
                        <div className="mb-5">
                            <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                ALAMAT LENGKAP PERUSAHAAN DAN KODE POS
                            </label>
                            <textarea
                                name="alamatPerusahaan"
                                rows={3}
                                value={formData.alamatPerusahaan}
                                onChange={handleChange}
                                placeholder="Contoh: PT GoTo Gojek Tokopedia Tbk, Jl. Iskandarsyah II No. 2, Kebayoran Baru, Jakarta Selatan, 12160"
                                className={`w-full resize-none rounded-lg border p-3.5 outline-none transition-all focus:ring-2 focus:ring-primary/10 ${
                                    errors.alamatPerusahaan
                                        ? "border-red-400 focus:border-red-400"
                                        : "border-gray-200 focus:border-primary"
                                }`}
                            />
                            {errors.alamatPerusahaan && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.alamatPerusahaan}
                                </p>
                            )}
                        </div>
                        <div className="mb-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    BULAN MULAI (PERKIRAAN)
                                </label>
                                <input
                                    type="month"
                                    name="tanggalMulai"
                                    value={formData.tanggalMulai}
                                    onChange={handleChange}
                                    className={`h-12 w-full rounded-lg border px-3.5 outline-none transition-colors ${
                                        errors.tanggalMulai
                                            ? "border-red-400 focus:border-red-400"
                                            : "border-gray-200 focus:border-primary"
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
                                    BULAN SELESAI (PERKIRAAN)
                                </label>
                                <input
                                    type="month"
                                    name="tanggalSelesai"
                                    value={formData.tanggalSelesai}
                                    onChange={handleChange}
                                    className={`h-12 w-full rounded-lg border px-3.5 outline-none transition-colors ${
                                        errors.tanggalSelesai
                                            ? "border-red-400 focus:border-red-400"
                                            : "border-gray-200 focus:border-primary"
                                    }`}
                                />
                                {errors.tanggalSelesai && (
                                    <p className="mt-1.5 text-[12px] text-red-500">
                                        {errors.tanggalSelesai}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div>
                            <label className="mb-2 block text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                LINGKUP MAGANG
                            </label>
                            <textarea
                                name="lingkupMagang"
                                rows={4}
                                value={formData.lingkupMagang}
                                onChange={handleChange}
                                placeholder="Deskripsikan secara singkat rencana divisi dan tugas Anda..."
                                className={`w-full resize-none rounded-lg border p-3.5 outline-none transition-all focus:ring-2 focus:ring-primary/10 ${
                                    errors.lingkupMagang
                                        ? "border-red-400 focus:border-red-400"
                                        : "border-gray-200 focus:border-primary"
                                }`}
                            />
                            {errors.lingkupMagang && (
                                <p className="mt-1.5 text-[12px] text-red-500">
                                    {errors.lingkupMagang}
                                </p>
                            )}
                            <div className="mt-2 flex items-center gap-1.5 text-gray-400">
                                <Info className="h-3 w-3 shrink-0" />
                                <span className="text-[12px] italic text-gray-400">
                                    Pastikan lingkup kerja relevan dengan
                                    program studi Anda.
                                </span>
                            </div>
                        </div>
                    </div>
                    <div className="mt-6">
                        {submitError && (
                            <div className="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                                <Info className="mt-0.5 h-4 w-4 shrink-0 text-red-500" />
                                <p className="text-[13px] font-medium text-red-600">
                                    {submitError}
                                </p>
                            </div>
                        )}
                        <div className="flex justify-stretch sm:justify-end">
                            <button
                                type="button"
                                onClick={handleSubmit}
                                disabled={
                                    !isFormValid ||
                                    isSubmitting ||
                                    internshipSecured
                                }
                                className={`flex w-full items-center justify-center gap-2 rounded-xl px-8 py-3.5 text-[15px] font-bold transition-colors sm:w-auto ${
                                    isSubmitting ||
                                    !isFormValid ||
                                    internshipSecured
                                        ? "cursor-not-allowed bg-gray-300 text-gray-400"
                                        : "bg-primary text-white hover:bg-primary-hover"
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
                </div>
                <div className="self-start lg:sticky lg:top-6">
                    <div className="rounded-xl border border-gray-200 bg-white p-5 hover:border-primary-pale hover:shadow-sm transition-all duration-300">
                        <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                            <BookOpen className="h-4.5 w-4.5 text-primary" />
                            <h3 className="text-[15px] font-bold text-[#1A1A1A]">
                                Alur Kerja Form 2
                            </h3>
                        </div>

                        <div className="mt-5 flex flex-col">
                            <div className="flex">
                                <div className="mr-3 flex flex-col items-center">
                                    <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                        1
                                    </div>
                                    <div className="my-1 w-0.5 flex-1 bg-gray-200"></div>
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
                            <div className="flex">
                                <div className="mr-3 flex flex-col items-center">
                                    <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                                        2
                                    </div>
                                    <div className="my-1 w-0.5 flex-1 bg-gray-200"></div>
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
                            <div className="flex">
                                <div className="mr-3 flex flex-col items-center">
                                    <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
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
                    <div className="mt-4 rounded-xl border border-primary/20 bg-primary-pale p-5">
                        <div className="mb-5 flex items-center gap-2">
                            <Lightbulb className="h-4 w-4 text-primary" />
                            <h3 className="text-[11px] font-bold uppercase tracking-wider text-primary">
                                PANDUAN PENGISIAN
                            </h3>
                        </div>

                        <div className="flex flex-col gap-2.5">
                            <div className="flex items-start gap-2.5">
                                <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                                    1
                                </div>
                                <p className="text-[12px] leading-relaxed text-gray-600">
                                    Gunakan nama resmi perusahaan sesuai dengan
                                    Akta atau Web Profil mereka.
                                </p>
                            </div>
                            <div className="flex items-start gap-2.5">
                                <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                                    2
                                </div>
                                <p className="text-[12px] leading-relaxed text-gray-600">
                                    Proses verifikasi oleh Prodi memakan waktu
                                    1-3 hari kerja.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
