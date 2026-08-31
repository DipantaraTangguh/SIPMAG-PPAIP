import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { BadgeCheck, Loader2, XCircle } from "lucide-react";
import { useForm1Workflow } from "../../../context/StudentWorkflowContext";
import FileDropInput from "../../Elements/FileDropInput";

/**
 * Panel state AwaitingConfirmation (magang non-wajib jalur Form 2), dipakai juga
 * di HasApplication buat jalur mitra yang lapor sendiri. Mahasiswa melaporkan
 * hasilnya: diterima (upload LoA + tempat & periode aktual) atau ditolak
 * (kembali bisa mengajukan Form 2 ke perusahaan lain).
 *
 * allowDecline=false dipakai di jalur mitra: kalau lamarannya belum tembus,
 * mahasiswa tinggal melamar lowongan lain lewat portal, tanpa lapor apa pun.
 */
export default function Form1KonfirmasiPanel({ allowDecline = true }) {
    const { confirmCycle } = useForm1Workflow();
    const navigate = useNavigate();

    const [companyName, setCompanyName] = useState("");
    const [alamat, setAlamat] = useState("");
    const [mulai, setMulai] = useState("");
    const [selesai, setSelesai] = useState("");
    const [loaFile, setLoaFile] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const [decliningConfirm, setDecliningConfirm] = useState(false);

    const isValid =
        companyName.trim() !== "" && mulai !== "" && selesai !== "" && loaFile;

    const inputClass =
        "w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/20 placeholder:text-gray-400";

    const submit = async (hasil) => {
        setIsSubmitting(true);
        setError(null);
        try {
            const fd = new FormData();
            fd.append("hasil", hasil);
            if (hasil === "diterima") {
                fd.append("company_name", companyName);
                if (alamat.trim()) fd.append("alamat_perusahaan", alamat);
                fd.append("tanggal_mulai", mulai);
                fd.append("tanggal_selesai", selesai);
                fd.append("loa_file", loaFile);
            }
            await confirmCycle(fd);
            navigate("/form1/status", { replace: true });
        } catch (err) {
            setError(err?.message || "Gagal mengirim konfirmasi.");
        } finally {
            setIsSubmitting(false);
            setDecliningConfirm(false);
        }
    };

    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-amber-500 bg-white p-6">
            <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <BadgeCheck className="h-5 w-5 text-amber-600" />
                </div>
                <div>
                    <h3 className="text-lg font-bold text-gray-900">
                        Konfirmasi Hasil Magang Non-Wajib
                    </h3>
                    <p className="mt-1 text-sm leading-relaxed text-gray-600">
                        {allowDecline
                            ? 'Surat pengantar Anda disetujui atau lamaran mitra Anda diterima. Laporkan hasilnya: bila diterima, unggah bukti penerimaan (LoA) beserta tempat dan periode magang yang sebenarnya.'
                            : 'Sudah diterima di tempat magang? Laporkan di sini dengan mengunggah bukti penerimaan (LoA) beserta tempat dan periode magang yang sebenarnya. Kalau belum, Anda masih bisa melamar lowongan lain lewat portal.'}
                    </p>
                </div>
            </div>

            {error && (
                <div
                    className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
                    role="alert"
                >
                    {error}
                </div>
            )}

            <div className="mt-5 flex flex-col gap-4">
                <div>
                    <label
                        htmlFor="konfirmasi-company"
                        className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-black"
                    >
                        Perusahaan / Instansi Penerima
                    </label>
                    <input
                        id="konfirmasi-company"
                        type="text"
                        value={companyName}
                        onChange={(e) => setCompanyName(e.target.value)}
                        placeholder="Nama perusahaan tempat Anda diterima"
                        className={inputClass}
                        disabled={isSubmitting}
                    />
                </div>
                <div>
                    <label
                        htmlFor="konfirmasi-alamat"
                        className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-black"
                    >
                        Alamat (opsional)
                    </label>
                    <input
                        id="konfirmasi-alamat"
                        type="text"
                        value={alamat}
                        onChange={(e) => setAlamat(e.target.value)}
                        placeholder="Alamat perusahaan"
                        className={inputClass}
                        disabled={isSubmitting}
                    />
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            htmlFor="konfirmasi-mulai"
                            className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-black"
                        >
                            Mulai Magang
                        </label>
                        <input
                            id="konfirmasi-mulai"
                            type="month"
                            value={mulai}
                            onChange={(e) => setMulai(e.target.value)}
                            className={inputClass}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="konfirmasi-selesai"
                            className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-black"
                        >
                            Selesai Magang
                        </label>
                        <input
                            id="konfirmasi-selesai"
                            type="month"
                            value={selesai}
                            min={mulai || undefined}
                            onChange={(e) => setSelesai(e.target.value)}
                            className={inputClass}
                            disabled={isSubmitting}
                        />
                    </div>
                </div>
                <FileDropInput
                    label="Bukti Diterima / LoA"
                    hint="PDF, JPG, atau PNG (maks. 5MB)"
                    accept=".pdf,.jpg,.jpeg,.png"
                    allowedTypes={["application/pdf", "image/jpeg", "image/png"]}
                    maxSizeMB={5}
                    value={loaFile}
                    onChange={setLoaFile}
                    disabled={isSubmitting}
                />

                <button
                    type="button"
                    onClick={() => submit("diterima")}
                    disabled={!isValid || isSubmitting}
                    className={`flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-bold transition-all ${
                        isValid && !isSubmitting
                            ? "bg-primary text-white shadow hover:bg-primary-hover"
                            : "cursor-not-allowed bg-gray-300 text-gray-400"
                    }`}
                >
                    {isSubmitting ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            Mengirim…
                        </>
                    ) : (
                        "Saya Diterima - Kirim Konfirmasi"
                    )}
                </button>
            </div>

            {allowDecline && (
            <div className="mt-5 border-t border-gray-100 pt-4">
                {!decliningConfirm ? (
                    <button
                        type="button"
                        onClick={() => setDecliningConfirm(true)}
                        disabled={isSubmitting}
                        className="flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-red-600"
                    >
                        <XCircle className="h-4 w-4" />
                        Saya belum/tidak diterima di perusahaan ini
                    </button>
                ) : (
                    <div className="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p className="text-sm text-amber-800">
                            Anda akan dikembalikan ke tahap sebelumnya dan dapat
                            mengajukan Form 2 ke perusahaan lain. Lanjutkan?
                        </p>
                        <div className="mt-3 flex gap-3">
                            <button
                                type="button"
                                onClick={() => submit("ditolak")}
                                disabled={isSubmitting}
                                className="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-hover disabled:bg-gray-300"
                            >
                                Ya, Ajukan Ulang
                            </button>
                            <button
                                type="button"
                                onClick={() => setDecliningConfirm(false)}
                                disabled={isSubmitting}
                                className="px-4 py-2 text-sm text-gray-500 hover:text-gray-900"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                )}
            </div>
            )}
        </div>
    );
}
