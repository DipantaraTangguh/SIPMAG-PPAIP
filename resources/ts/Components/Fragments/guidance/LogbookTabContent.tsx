import React, { useRef, useState } from "react";
import { createPortal } from "react-dom";
import { useNavigate } from "react-router-dom";
import { BookOpen, GraduationCap, Info, X } from "lucide-react";
import { useLogbookWorkflow } from "../../../context/StudentWorkflowContext";

const formatLogbookDate = (dateStr) => {
    if (!dateStr) return "";
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
        });
    } catch {
        return dateStr;
    }
};

const getLogbookDayName = (dateStr) => {
    if (!dateStr) return "";
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return "";
        return date.toLocaleDateString("id-ID", { weekday: "long" });
    } catch {
        return "";
    }
};

const toDateInputValue = (dateStr) =>
    dateStr ? String(dateStr).slice(0, 10) : "";

export default function LogbookTabContent() {
    const navigate = useNavigate();
    const {
        logbookEntries,
        logbookPeriod,
        addLogbookEntry,
        updateLogbookEntry,
    } = useLogbookWorkflow();

    const [showAddModal, setShowAddModal] = useState(false);
    const [editEntry, setEditEntry] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [modalError, setModalError] = useState(null);
    const [newEntry, setNewEntry] = useState({
        tanggal: "",
        kegiatanHarian: "",
        hasil: "",
    });
    const dateInputRef = useRef<HTMLInputElement | null>(null);

    const TOTAL_REQUIRED = 6;
    const approvedCount = logbookEntries.filter(
        (e) => e.status === "Disetujui",
    ).length;
    const isComplete = approvedCount >= TOTAL_REQUIRED;
    const logbookStartDate = toDateInputValue(logbookPeriod?.start_date);
    const logbookMaxDate = toDateInputValue(logbookPeriod?.maximum_date);
    const hasSelectableLogbookDate =
        !logbookStartDate ||
        !logbookMaxDate ||
        logbookStartDate <= logbookMaxDate;
    const unavailableDateMessage = hasSelectableLogbookDate
        ? null
        : `Logbook baru dapat diisi mulai ${formatLogbookDate(logbookStartDate)}.`;

    const handleAddEntry = async () => {
        if (
            !newEntry.tanggal ||
            !newEntry.kegiatanHarian.trim() ||
            !newEntry.hasil.trim()
        )
            return;
        setIsSubmitting(true);
        setModalError(null);
        try {
            await addLogbookEntry(newEntry);
            setNewEntry({ tanggal: "", kegiatanHarian: "", hasil: "" });
            setShowAddModal(false);
        } catch (err) {
            setModalError(
                err?.message || "Gagal menyimpan entri. Silakan coba lagi.",
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleEditSave = async () => {
        if (!editEntry.kegiatanHarian.trim() || !editEntry.hasil.trim()) return;
        setIsSubmitting(true);
        setModalError(null);
        try {
            await updateLogbookEntry(editEntry.id, {
                kegiatanHarian: editEntry.kegiatanHarian,
                hasil: editEntry.hasil,
            });
            setEditEntry(null);
        } catch (err) {
            setModalError(
                err?.message || "Gagal memperbarui entri. Silakan coba lagi.",
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    // Helper kecil biar buka/tutup modal nggak nyebar ke JSX.
    const isEdit = editEntry !== null;
    const isModalOpen = showAddModal || isEdit;
    const modalTitle = isEdit
        ? "Perbaiki Entri Logbook"
        : "Tambah Entri Logbook";
    const activeData = isEdit ? editEntry : newEntry;
    const updateActiveData = (updates) =>
        isEdit
            ? setEditEntry({ ...editEntry, ...updates })
            : setNewEntry({ ...newEntry, ...updates });
    const handleModalSave = isEdit ? handleEditSave : handleAddEntry;
    const closeAllModals = () => {
        setShowAddModal(false);
        setEditEntry(null);
        setModalError(null);
    };
    const openDatePicker = () => {
        const input = dateInputRef.current;

        if (!input) return;

        input.focus();

        if (typeof input.showPicker === "function") {
            try {
                input.showPicker();
            } catch {
                // Some browsers only allow showPicker directly from trusted clicks.
            }
        }
    };

    return (
        <div className="mt-8 animate-in fade-in duration-500">
            <div className="flex flex-col items-stretch gap-5 xl:flex-row">
                <div
                    className={`flex flex-1 flex-col gap-5 rounded-xl border border-l-4 bg-white px-5 py-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-7 ${isComplete ? "border-green-200 border-l-green-500" : "border-gray-200 border-l-primary"}`}
                >
                    <div>
                        <p className="mb-2.5 text-[12px] font-bold uppercase tracking-wider text-gray-500">
                            PROGRESS PERSETUJUAN
                        </p>
                        <div className="flex flex-wrap items-baseline gap-3">
                            <span
                                className={`text-[36px] font-bold leading-none ${isComplete ? "text-green-500" : "text-primary"}`}
                            >
                                {approvedCount}/{TOTAL_REQUIRED}
                            </span>
                            <span className="text-[14px] font-medium text-gray-600">
                                Entri Disetujui
                            </span>
                        </div>
                        <div className="mt-3.5 h-2.5 w-60 max-w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                className={`h-full rounded-full transition-all duration-500 ${isComplete ? "bg-green-500" : "bg-primary"}`}
                                style={{
                                    width: `${Math.min((approvedCount / TOTAL_REQUIRED) * 100, 100)}%`,
                                }}
                            />
                        </div>
                    </div>

                    {isComplete ? (
                        <button
                            onClick={() => navigate("/defense")}
                            className="flex w-full items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-[14px] font-bold text-white transition-colors hover:bg-green-600 sm:ml-4 sm:w-auto"
                        >
                            <GraduationCap className="h-4.5 w-4.5" />
                            <span>Daftar Sidang →</span>
                        </button>
                    ) : unavailableDateMessage ? (
                        <div className="w-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] font-semibold text-amber-700 sm:ml-4 sm:w-auto">
                            {unavailableDateMessage}
                        </div>
                    ) : (
                        <button
                            onClick={() => setShowAddModal(true)}
                            className="flex w-full items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-[14px] font-bold text-white transition-colors hover:bg-primary-hover sm:ml-4 sm:w-auto"
                        >
                            <span>+ Tambah Entri</span>
                        </button>
                    )}
                </div>
                <div className="relative w-full overflow-hidden rounded-xl bg-primary p-6 shadow-sm xl:w-70 xl:shrink-0">
                    <h3 className="text-[15px] font-bold text-white">
                        Informasi Penting
                    </h3>
                    <p className="mt-2 text-[13px] leading-relaxed text-white opacity-90">
                        Pastikan logbook diisi setiap hari kerja. Review
                        pembimbing dilakukan setiap akhir pekan.
                    </p>
                    <Info className="absolute -bottom-4 -right-2 h-25 w-25 text-white opacity-10" />
                </div>
            </div>
            {logbookEntries.length === 0 ? (
                <div className="mt-6 flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center sm:p-12">
                    <BookOpen className="text-gray-300" size={48} />
                    <h3 className="mt-3 text-[16px] font-bold text-gray-400">
                        Belum Ada Entri Logbook
                    </h3>
                    <p className="mt-1.5 text-[13px] text-gray-400">
                        {unavailableDateMessage ||
                            "Klik '+ Tambah Entri' untuk mulai mencatat kegiatan magang harian Anda."}
                    </p>
                </div>
            ) : (
                <div className="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white">
                    <table className="min-w-180 w-full text-left">
                        <thead>
                            <tr className="border-b border-gray-200 bg-gray-50">
                                <th className="w-35 px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    Tanggal
                                </th>
                                <th className="px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    Kegiatan Harian
                                </th>
                                <th className="w-45 px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    Hasil
                                </th>
                                <th className="w-35 px-5 py-3.5 text-center text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {logbookEntries.map((entry, idx) => (
                                <tr
                                    key={entry.id}
                                    className={`group bg-white hover:bg-gray-50/50 ${idx !== logbookEntries.length - 1 ? "border-b border-gray-100" : ""}`}
                                >
                                    <td className="px-5 py-4 align-top">
                                        <p className="text-[14px] font-bold text-[#1A1A1A]">
                                            {formatLogbookDate(entry.tanggal)}
                                        </p>
                                        <p className="mt-0.5 text-[12px] text-gray-400">
                                            {getLogbookDayName(entry.tanggal)}
                                        </p>
                                    </td>
                                    <td className="px-5 py-4 align-top">
                                        <p className="max-w-full truncate text-[14px] font-bold text-[#1A1A1A]">
                                            {entry.kegiatanHarian}
                                        </p>
                                        {entry.dpmNote && (
                                            <p className="mt-2 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-[12px] leading-relaxed text-red-600">
                                                <span className="font-bold">
                                                    Catatan DPM:
                                                </span>{" "}
                                                {entry.dpmNote}
                                            </p>
                                        )}
                                    </td>
                                    <td className="px-5 py-4 align-top">
                                        <p
                                            className={`text-[13px] ${entry.hasil === "-" ? "text-gray-300" : "text-gray-600"}`}
                                        >
                                            {entry.hasil}
                                        </p>
                                    </td>
                                    <td className="w-35 px-5 py-4 align-top text-center">
                                        <span
                                            className={`inline-block w-full whitespace-normal rounded-full px-3.5 py-1 text-center text-[12px] font-bold ${
                                                entry.status === "Disetujui"
                                                    ? "bg-green-100 text-green-600"
                                                    : entry.status ===
                                                        "Menunggu Review"
                                                      ? "bg-amber-100 text-amber-600"
                                                      : "bg-red-100 text-red-500"
                                            }`}
                                        >
                                            {entry.status}
                                        </span>
                                        {/* Entri yang ditolak percuma diberi
                                            catatan kalau mahasiswa tidak
                                            punya jalan memperbaikinya.
                                            Server menerima perubahan selama
                                            status masih PendingReview atau
                                            Rejected, jadi tombolnya muncul
                                            di kedua keadaan itu. */}
                                        {(entry.status === "Ditolak" ||
                                            entry.status ===
                                                "Menunggu Review") && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setEditEntry({
                                                        id: entry.id,
                                                        tanggal: entry.tanggal,
                                                        kegiatanHarian:
                                                            entry.kegiatanHarian,
                                                        hasil: entry.hasil,
                                                    })
                                                }
                                                className="mt-2 block w-full text-[12px] font-semibold text-primary underline-offset-2 hover:underline"
                                            >
                                                Perbaiki
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            {isModalOpen &&
                typeof document !== "undefined" &&
                createPortal(
                    <div
                        className="fixed inset-0 z-99 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/45 p-4 sm:p-0"
                        onClick={closeAllModals}
                    >
                        <div
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="logbook-modal-title"
                            className="relative w-full max-w-140 transform rounded-xl bg-white text-left shadow-xl transition-all sm:my-8"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-5 sm:px-6">
                                <h2
                                    id="logbook-modal-title"
                                    className="text-[17px] font-bold text-[#1A1A1A]"
                                >
                                    {modalTitle}
                                </h2>
                                <button
                                    type="button"
                                    onClick={closeAllModals}
                                    className="rounded-full p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="max-h-[calc(100vh-160px)] overflow-y-auto px-5 py-5 sm:px-6">
                                <div className="flex flex-col gap-5">
                                    <div>
                                        <label
                                            htmlFor="logbook-tanggal"
                                            className="mb-2 block text-[13px] font-bold text-[#1A1A1A]"
                                        >
                                            Tanggal
                                        </label>
                                        <div>
                                            <input
                                                ref={dateInputRef}
                                                id="logbook-tanggal"
                                                type="date"
                                                min={
                                                    hasSelectableLogbookDate &&
                                                    logbookStartDate
                                                        ? logbookStartDate
                                                        : undefined
                                                }
                                                max={
                                                    hasSelectableLogbookDate &&
                                                    logbookMaxDate
                                                        ? logbookMaxDate
                                                        : undefined
                                                }
                                                value={
                                                    activeData.tanggal
                                                        ? activeData.tanggal.slice(
                                                              0,
                                                              10,
                                                          )
                                                        : ""
                                                }
                                                onClick={openDatePicker}
                                                // Saat memperbaiki entri,
                                                // tanggalnya tidak ikut
                                                // dikirim ke server, jadi
                                                // field-nya dikunci daripada
                                                // terlihat bisa diubah lalu
                                                // diam-diam diabaikan.
                                                disabled={
                                                    !hasSelectableLogbookDate ||
                                                    isEdit
                                                }
                                                onChange={(e) =>
                                                    updateActiveData({
                                                        tanggal: e.target.value,
                                                    })
                                                }
                                                aria-required="true"
                                                className="h-11 w-full cursor-pointer rounded-lg border border-gray-200 px-3 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400"
                                            />
                                        </div>
                                        {unavailableDateMessage && (
                                            <p className="mt-2 text-[12px] font-medium text-amber-600">
                                                {unavailableDateMessage}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label
                                            htmlFor="logbook-kegiatan"
                                            className="mb-2 flex justify-between text-[13px] font-bold text-[#1A1A1A]"
                                        >
                                            <span>
                                                Kegiatan Harian{" "}
                                                <span className="text-red-500">
                                                    *
                                                </span>
                                            </span>
                                            <span className="font-normal text-gray-400">
                                                Maks. 500 karakter
                                            </span>
                                        </label>
                                        <textarea
                                            id="logbook-kegiatan"
                                            rows={4}
                                            maxLength={500}
                                            value={activeData.kegiatanHarian}
                                            onChange={(e) =>
                                                updateActiveData({
                                                    kegiatanHarian:
                                                        e.target.value,
                                                })
                                            }
                                            placeholder="Deskripsikan kegiatan yang Anda lakukan hari ini..."
                                            aria-required="true"
                                            className="w-full resize-none rounded-lg border border-gray-200 p-3 text-[14px] leading-relaxed outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                                        />
                                        <div className="mt-1 text-right text-[11px] font-medium text-gray-400">
                                            {activeData.kegiatanHarian
                                                ?.length || 0}{" "}
                                            / 500
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            htmlFor="logbook-hasil"
                                            className="mb-2 flex justify-between text-[13px] font-bold text-[#1A1A1A]"
                                        >
                                            <span>
                                                Hasil{" "}
                                                <span className="text-red-500">
                                                    *
                                                </span>
                                            </span>
                                            <span className="font-normal text-gray-400">
                                                Maks. 300 karakter
                                            </span>
                                        </label>
                                        <textarea
                                            id="logbook-hasil"
                                            rows={3}
                                            maxLength={300}
                                            value={activeData.hasil}
                                            onChange={(e) =>
                                                updateActiveData({
                                                    hasil: e.target.value,
                                                })
                                            }
                                            placeholder="Tuliskan output atau hasil dari kegiatan Anda..."
                                            aria-required="true"
                                            className="w-full resize-none rounded-lg border border-gray-200 p-3 text-[14px] leading-relaxed outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                                        />
                                        <div className="mt-1 text-right text-[11px] font-medium text-gray-400">
                                            {activeData.hasil?.length || 0} /
                                            300
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col-reverse items-stretch justify-end gap-2 rounded-b-xl border-t border-gray-100 bg-white px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                                {modalError && (
                                    <p
                                        className="flex-1 text-[12px] font-medium text-red-500"
                                        role="alert"
                                    >
                                        {modalError}
                                    </p>
                                )}
                                <button
                                    type="button"
                                    onClick={closeAllModals}
                                    disabled={isSubmitting}
                                    className="rounded-lg border border-gray-200 bg-white px-4.5 py-2.25 text-[13px] font-bold text-gray-600 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 disabled:opacity-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    disabled={
                                        !activeData.tanggal ||
                                        !activeData.kegiatanHarian?.trim() ||
                                        !activeData.hasil?.trim() ||
                                        isSubmitting
                                    }
                                    onClick={handleModalSave}
                                    className="flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-2.25 text-[13px] font-bold text-white transition-colors hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none"
                                >
                                    {isSubmitting ? (
                                        <>
                                            <svg
                                                className="h-4 w-4 animate-spin"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                            >
                                                <circle
                                                    className="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    strokeWidth="4"
                                                />
                                                <path
                                                    className="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8H4z"
                                                />
                                            </svg>
                                            Menyimpan...
                                        </>
                                    ) : (
                                        "Simpan Entri"
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>,
                    document.body,
                )}
        </div>
    );
}
