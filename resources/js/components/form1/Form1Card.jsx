/**
 * Form1Card.jsx
 * Main form card for "Form Magang-01: Surat Keterangan Memenuhi Syarat Akademik".
 * Renders all form fields, file upload dropzone, radio group, declaration checkbox,
 * and footer action buttons. All state is passed in via props from the useForm1 hook.
 *
 * @prop {object}   readOnlyFields — { nama, nim, programStudi }
 * @prop {object}   formData       — Current form state from useForm1.
 * @prop {object}   errors         — Per-field validation errors.
 * @prop {string}   fileError      — File-specific validation error.
 * @prop {boolean}  isSubmitting   — Whether a submission is in progress.
 * @prop {boolean}  isDragging     — Whether a file is being dragged over the dropzone.
 * @prop {function} setIsDragging  — Setter for isDragging.
 * @prop {boolean}  isFormValid    — Whether all required fields are filled.
 * @prop {function} updateField    — (field, value) => void.
 * @prop {function} handleFileChange — (file) => void.
 * @prop {function} removeFile     — () => void.
 * @prop {function} handleSubmit   — Form submit handler.
 * @prop {function} handleCancel   — Cancel / go-back handler.
 */
import React, { useRef } from 'react';
import {
    Lock,
    CloudUpload,
    ChevronDown,
    ArrowRight,
    X,
    FileText,
    Loader2,
} from 'lucide-react';

/* ── Reusable sub-components ─────────────────────── */

/** Field label (uppercase, small, gray) */
function FieldLabel({ children, htmlFor }) {
    return (
        <label
            htmlFor={htmlFor}
            className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-400"
        >
            {children}
        </label>
    );
}

/** Inline error message shown below a field */
function FieldError({ message }) {
    if (!message) return null;
    return <p className="mt-1 text-xs text-red-500">{message}</p>;
}

/** Read-only text input with lock icon */
function ReadOnlyInput({ id, value }) {
    return (
        <div className="relative">
            <input
                id={id}
                type="text"
                value={value}
                readOnly
                className="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 pr-10 text-sm text-gray-600 outline-none"
            />
            <Lock className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        </div>
    );
}

/** Helper / hint text below a field */
function HelperText({ children }) {
    return (
        <p className="mt-1 text-xs italic text-gray-400">{children}</p>
    );
}

/* ── Format file size for display ────────────────── */
function formatFileSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/* ── Main Component ──────────────────────────────── */
export default function Form1Card({
    readOnlyFields,
    formData,
    errors,
    fileError,
    isSubmitting,
    isDragging,
    setIsDragging,
    isFormValid,
    updateField,
    handleFileChange,
    removeFile,
    handleSubmit,
    handleCancel,
}) {
    const fileInputRef = useRef(null);

    /* ── Drag & drop handlers ────────────────────── */
    const onDragOver = (e) => {
        e.preventDefault();
        setIsDragging(true);
    };
    const onDragLeave = () => setIsDragging(false);
    const onDrop = (e) => {
        e.preventDefault();
        setIsDragging(false);
        const file = e.dataTransfer.files[0];
        if (file) handleFileChange(file);
    };
    const onFileInputChange = (e) => {
        const file = e.target.files[0];
        if (file) handleFileChange(file);
        e.target.value = '';
    };

    /* ── Input base classes ──────────────────────── */
    const inputBase =
        'w-full rounded-lg border px-4 py-3 text-sm text-gray-800 outline-none transition-colors duration-150 placeholder:text-gray-400';
    const inputNormal = `${inputBase} border-gray-200 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20`;
    const inputError = `${inputBase} border-red-500 bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20`;

    return (
        <form onSubmit={handleSubmit} className="rounded-xl border border-gray-200 bg-white p-8">
            {/* ── Card header ──────────────────── */}
            <div className="mb-8">
                <h3 className="text-xl font-bold text-gray-900">
                    Isi Form Magang-01
                </h3>
                <p className="mt-1 text-sm text-gray-500">
                    Lengkapi data di bawah ini untuk mengajukan surat keterangan akademik.
                </p>
            </div>

            {/* ── Submit-level error ────────────── */}
            {errors.submit && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    {errors.submit}
                </div>
            )}

            <div className="flex flex-col gap-6">
                {/* ── ROW 1: Nama + NIM (read-only) ── */}
                <div className="grid grid-cols-2 gap-6">
                    <div>
                        <FieldLabel htmlFor="nama">Nama Lengkap</FieldLabel>
                        <ReadOnlyInput id="nama" value={readOnlyFields.nama} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="nim">NIM</FieldLabel>
                        <ReadOnlyInput id="nim" value={readOnlyFields.nim} />
                    </div>
                </div>

                {/* ── ROW 2: Program Studi (read-only) ── */}
                <div>
                    <FieldLabel htmlFor="programStudi">Program Studi</FieldLabel>
                    <ReadOnlyInput id="programStudi" value={readOnlyFields.programStudi} />
                </div>

                {/* ── ROW 3: Semester + Tahun Akademik (read-only) ── */}
                <div className="grid grid-cols-2 gap-6">
                    <div>
                        <FieldLabel htmlFor="semester">Semester</FieldLabel>
                        <ReadOnlyInput id="semester" value={readOnlyFields.semester} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="tahunAkademik">Tahun Akademik</FieldLabel>
                        <ReadOnlyInput id="tahunAkademik" value={readOnlyFields.tahunAkademik} />
                    </div>
                </div>

                {/* ── ROW 4: Jumlah SKS + IPK (read-only) ── */}
                <div className="grid grid-cols-2 gap-6">
                    <div>
                        <FieldLabel htmlFor="jumlahSks">Jumlah SKS</FieldLabel>
                        <ReadOnlyInput id="jumlahSks" value={readOnlyFields.jumlahSks} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="ipk">IPK</FieldLabel>
                        <ReadOnlyInput id="ipk" value={readOnlyFields.ipk} />
                    </div>
                </div>

                {/* ── ROW 5: Rencana Skema Magang ── */}
                <div className="w-1/2">
                    <FieldLabel htmlFor="rencanaSkema">Rencana Skema Magang</FieldLabel>
                    <div className="relative">
                        <select
                            id="rencanaSkema"
                            value={formData.rencanaSkema}
                            onChange={(e) => updateField('rencanaSkema', e.target.value)}
                            className={`${
                                errors.rencanaSkema ? inputError : inputNormal
                            } appearance-none pr-10 ${
                                formData.rencanaSkema === '' ? 'text-gray-400' : ''
                            }`}
                            disabled={isSubmitting}
                        >
                            <option value="">Pilih skema magang</option>
                            <option value="Mitra">Magang Mitra (Perusahaan)</option>
                            <option value="Mandiri">Magang Mandiri</option>
                            <option value="Kewirausahaan">Magang Kewirausahaan</option>
                        </select>
                        <ChevronDown className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    </div>
                    <FieldError message={errors.rencanaSkema} />
                </div>

                {/* ── ROW 6: Topik / Tempat Magang ── */}
                <div>
                    <FieldLabel htmlFor="topikTempat">Topik / Tempat Magang</FieldLabel>
                    <textarea
                        id="topikTempat"
                        rows={4}
                        placeholder="Tuliskan rencana tempat atau topik magang anda..."
                        value={formData.topikTempat}
                        onChange={(e) => updateField('topikTempat', e.target.value)}
                        className={`${
                            errors.topikTempat ? inputError : inputNormal
                        } resize-none`}
                        disabled={isSubmitting}
                    />
                    {!errors.topikTempat && (
                        <HelperText>
                            Untuk Magang skema mandiri tulis topik yang akan diajukan. Untuk
                            magang skema perusahaan tulis nama instansi / perusahaan yang
                            lengkap.
                        </HelperText>
                    )}
                    <FieldError message={errors.topikTempat} />
                </div>

                {/* ── ROW 7: Upload Transkrip ── */}
                <div>
                    <FieldLabel>Upload Transkrip Nilai Terbaru</FieldLabel>

                    {formData.transkripFile ? (
                        /* File selected state */
                        <div className="flex items-center gap-3 rounded-xl border border-primary bg-primary-pale px-5 py-4">
                            <FileText className="h-6 w-6 flex-shrink-0 text-primary" />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium text-gray-800">
                                    {formData.transkripFile.name}
                                </p>
                                <p className="text-xs text-gray-500">
                                    {formatFileSize(formData.transkripFile.size)}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={removeFile}
                                className="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-600"
                                aria-label="Hapus file"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                    ) : (
                        /* Dropzone */
                        <div
                            onDragOver={onDragOver}
                            onDragLeave={onDragLeave}
                            onDrop={onDrop}
                            onClick={() => fileInputRef.current?.click()}
                            className={`
                                flex cursor-pointer flex-col items-center justify-center rounded-xl
                                border-2 border-dashed px-6 py-10 transition-colors duration-150
                                ${isDragging
                                    ? 'border-primary bg-primary-pale'
                                    : errors.transkripFile
                                        ? 'border-red-400 bg-red-50 hover:border-red-500'
                                        : 'border-gray-300 hover:border-primary hover:bg-primary-pale'
                                }
                            `}
                        >
                            <CloudUpload
                                className={`mb-3 h-10 w-10 ${
                                    isDragging ? 'text-primary' : 'text-primary'
                                }`}
                            />
                            <p className="text-sm font-medium text-gray-800">
                                Klik untuk unggah atau drag and drop
                            </p>
                            <p className="mt-1 text-xs text-gray-400">
                                PDF, JPG, atau PNG (Maks. 5MB)
                            </p>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={onFileInputChange}
                                className="hidden"
                            />
                        </div>
                    )}

                    {fileError && (
                        <p className="mt-1 text-xs text-red-500">{fileError}</p>
                    )}
                    <FieldError message={errors.transkripFile} />
                </div>

                {/* ── ROW 8: Output (radio group) ── */}
                <div>
                    <FieldLabel>Output</FieldLabel>
                    <div className="flex items-center gap-6">
                        {['Produk', 'Prototype', 'Laporan'].map((opt) => (
                            <label
                                key={opt}
                                className="flex cursor-pointer items-center gap-2 text-sm text-gray-700"
                            >
                                <span
                                    className={`flex h-5 w-5 items-center justify-center rounded-full border-2 transition-colors ${
                                        formData.output === opt
                                            ? 'border-primary'
                                            : errors.output
                                                ? 'border-red-500'
                                                : 'border-gray-300'
                                    }`}
                                >
                                    {formData.output === opt && (
                                        <span className="h-2.5 w-2.5 rounded-full bg-primary" />
                                    )}
                                </span>
                                <input
                                    type="radio"
                                    name="output"
                                    value={opt}
                                    checked={formData.output === opt}
                                    onChange={() => updateField('output', opt)}
                                    className="sr-only"
                                    disabled={isSubmitting}
                                />
                                {opt}
                            </label>
                        ))}
                    </div>
                    <FieldError message={errors.output} />
                </div>

                {/* ── ROW 9: Declaration checkbox ── */}
                <label className="flex cursor-pointer items-start gap-3">
                    <input
                        type="checkbox"
                        checked={formData.declarationChecked}
                        onChange={(e) =>
                            updateField('declarationChecked', e.target.checked)
                        }
                        className="mt-0.5 h-4 w-4 flex-shrink-0 cursor-pointer rounded border-gray-300 accent-primary"
                        disabled={isSubmitting}
                    />
                    <span
                        className={`text-[13px] leading-relaxed ${
                            errors.declarationChecked
                                ? 'font-medium text-red-500'
                                : 'text-primary'
                        }`}
                    >
                        *Saya menyatakan bahwa data di atas adalah benar dan saya telah
                        memenuhi syarat minimum SKS untuk mengambil mata kuliah magang
                        sesuai kurikulum Program Studi.
                    </span>
                </label>
            </div>

            {/* ── Form footer ────────────────────── */}
            <div className="mt-8 flex items-center justify-end gap-4 border-t border-gray-100 pt-6">
                <button
                    type="button"
                    onClick={handleCancel}
                    className="px-5 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900"
                    disabled={isSubmitting}
                >
                    Batal
                </button>
                <button
                    type="submit"
                    disabled={!isFormValid || isSubmitting}
                    className={`
                        flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-bold
                        transition-all duration-200
                        ${isFormValid && !isSubmitting
                            ? 'bg-primary text-white shadow hover:bg-primary-hover hover:shadow-md'
                            : 'cursor-not-allowed bg-gray-300 text-gray-400'
                        }
                    `}
                >
                    {isSubmitting ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            Mengirim…
                        </>
                    ) : (
                        <>
                            Kirim Pengajuan
                            <ArrowRight className="h-4 w-4" />
                        </>
                    )}
                </button>
            </div>
        </form>
    );
}
