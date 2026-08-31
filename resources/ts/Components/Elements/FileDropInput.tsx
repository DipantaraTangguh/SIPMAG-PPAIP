import React, { useId, useRef, useState } from "react";
import { AlertCircle, CloudUpload, FileCheck, X } from "lucide-react";

/**
 * Satu kotak unggah berkas untuk seluruh portal mahasiswa.
 *
 * Sebelumnya tiap formulir menulis kotaknya sendiri-sendiri: dua mendukung
 * drag & drop, dua tidak, dan panel konfirmasi LoA bahkan tidak memeriksa tipe
 * maupun ukuran berkas sama sekali -- mahasiswa baru tahu berkasnya ditolak
 * setelah unggahan gagal di server. Perilaku yang berbeda-beda di layar yang
 * mirip itu yang bikin ragu, jadi semuanya disatukan di sini.
 *
 * Komponen ini terkendali: induk memegang File-nya, komponen memegang keadaan
 * seret dan pesan galat validasinya sendiri. Galat dari luar (misalnya
 * penolakan server) dioper lewat prop `error` dan tampil di tempat yang sama.
 */

interface FileDropInputProps {
    /** Teks di atas kotak. Dilewati kalau induknya sudah punya label sendiri. */
    label?: string;
    /** Keterangan singkat, misalnya "PDF, maks 5MB". */
    hint: string;
    /** Nilai atribut accept, misalnya ".pdf,.jpg". */
    accept: string;
    /** Tipe MIME yang diterima. Kosong berarti tipe apa pun lolos. */
    allowedTypes?: string[];
    maxSizeMB: number;
    value: File | null;
    onChange: (file: File | null) => void;
    /** Galat dari luar; ditampilkan mendahului galat validasi internal. */
    error?: string | null;
    disabled?: boolean;
}

export function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(0)} KB`;
    }
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function FileDropInput({
    label,
    hint,
    accept,
    allowedTypes = [],
    maxSizeMB,
    value,
    onChange,
    error = null,
    disabled = false,
}: FileDropInputProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragOver, setDragOver] = useState(false);
    const [validationError, setValidationError] = useState<string | null>(null);
    const errorId = useId();

    // Galat validasi didahulukan karena ia buah dari tindakan terakhir
    // pengguna; galat luar hanya muncul selama tidak ada yang lebih baru.
    const shownError = validationError ?? error;

    const openPicker = () => {
        if (!disabled) {
            inputRef.current?.click();
        }
    };

    const accepted = (file: File): string | null => {
        if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            return `Format tidak didukung. Gunakan ${hint.split("(")[0].trim()}.`;
        }
        if (file.size > maxSizeMB * 1024 * 1024) {
            return `Ukuran berkas melebihi ${maxSizeMB}MB.`;
        }
        return null;
    };

    const handleFile = (file: File | undefined | null) => {
        if (!file) {
            return;
        }

        const problem = accepted(file);
        setValidationError(problem);

        // Berkas yang ditolak tidak menghapus pilihan yang sudah benar.
        // Salah seret satu kali seharusnya tidak memaksa memilih ulang.
        if (!problem) {
            onChange(file);
        }
    };

    // Handler seret dipasang di pembungkus, bukan di tombolnya, supaya kotak
    // tetap menerima jatuhan berkas saat sudah ada berkas terpilih -- keadaan
    // itu bukan tombol lagi karena memuat tombol hapus di dalamnya.
    const dragProps = disabled
        ? {}
        : {
              onDragOver: (event: React.DragEvent) => {
                  event.preventDefault();
                  setDragOver(true);
              },
              onDragLeave: () => setDragOver(false),
              onDrop: (event: React.DragEvent) => {
                  event.preventDefault();
                  setDragOver(false);
                  handleFile(event.dataTransfer.files?.[0]);
              },
          };

    const frame = `rounded-xl border-2 transition-colors ${
        dragOver
            ? "border-primary bg-primary-pale/60"
            : shownError
              ? "border-dashed border-red-300 bg-red-50/40"
              : value
                ? "border-solid border-primary bg-primary-pale/50"
                : "border-dashed border-gray-300 bg-white"
    }`;

    return (
        <div>
            {label && (
                <span className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    {label}
                </span>
            )}

            <div {...dragProps} className={frame}>
                {value ? (
                    <div className="flex items-center justify-between gap-3 p-4">
                        <div className="flex min-w-0 items-center gap-3">
                            <FileCheck className="h-6 w-6 shrink-0 text-primary" />
                            <div className="min-w-0">
                                <p className="truncate text-[13px] font-bold text-[#1A1A1A]">
                                    {value.name}
                                </p>
                                <p className="text-[11px] text-gray-500">
                                    {formatFileSize(value.size)}
                                </p>
                            </div>
                        </div>
                        <div className="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                onClick={openPicker}
                                disabled={disabled}
                                className="rounded-lg px-2.5 py-1.5 text-[12px] font-semibold text-primary transition-colors hover:bg-white disabled:opacity-50"
                            >
                                Ganti
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setValidationError(null);
                                    onChange(null);
                                }}
                                disabled={disabled}
                                aria-label={`Hapus berkas ${value.name}`}
                                className="rounded-full p-1.5 text-gray-400 transition-colors hover:bg-white hover:text-gray-600 disabled:opacity-50"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                ) : (
                    // Tombol sungguhan, bukan div ber-role: Enter, Space, dan
                    // fokusnya datang dari peramban, tidak perlu ditiru.
                    <button
                        type="button"
                        onClick={openPicker}
                        disabled={disabled}
                        aria-invalid={!!shownError}
                        aria-describedby={shownError ? errorId : undefined}
                        className="flex w-full flex-col items-center rounded-[10px] px-4 py-6 text-center transition-colors hover:bg-primary-pale/40 focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-60 sm:py-8"
                    >
                        <CloudUpload className="h-8 w-8 text-primary" />
                        <span className="mt-3 text-[14px] font-bold text-[#1A1A1A]">
                            Klik untuk unggah atau seret berkas ke sini
                        </span>
                        <span className="mt-1 text-[12px] text-gray-400">
                            {hint}
                        </span>
                    </button>
                )}
            </div>

            {shownError && (
                <p
                    id={errorId}
                    role="alert"
                    className="mt-2 flex items-center gap-1.5 text-[12px] font-medium text-red-500"
                >
                    <AlertCircle className="h-3.5 w-3.5 shrink-0" />
                    {shownError}
                </p>
            )}

            <input
                ref={inputRef}
                type="file"
                accept={accept}
                disabled={disabled}
                className="sr-only"
                onChange={(event) => {
                    handleFile(event.target.files?.[0]);
                    // Direset supaya berkas yang sama bisa dipilih lagi setelah
                    // dihapus -- tanpa ini onChange tidak menyala kedua kalinya.
                    event.target.value = "";
                }}
            />
        </div>
    );
}
