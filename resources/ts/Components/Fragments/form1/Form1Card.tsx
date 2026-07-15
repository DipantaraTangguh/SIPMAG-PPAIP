import React from "react";
import { Lock, ChevronDown, ArrowRight, Loader2 } from "lucide-react";
function FieldLabel({
    children,
    htmlFor,
    id,
}: {
    children: React.ReactNode;
    htmlFor?: string;
    id?: string;
}) {
    return (
        <label
            id={id}
            htmlFor={htmlFor}
            className="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-black"
        >
            {children}
        </label>
    );
}
function FieldError({ id, message }) {
    if (!message) return null;
    return (
        <p id={id} className="mt-1 text-xs text-red-500" role="alert">
            {message}
        </p>
    );
}
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
function HelperText({ children }) {
    return <p className="mt-1 text-xs italic text-black">{children}</p>;
}

export default function Form1Card({
    readOnlyFields,
    formData,
    errors,
    isSubmitting,
    isFormValid,
    hasCompletedWajib,
    updateField,
    handleSubmit,
    handleCancel,
}) {
    const inputBase =
        "w-full rounded-lg border px-4 py-3 text-sm text-gray-800 outline-none transition-colors duration-150 placeholder:text-gray-400";
    const inputNormal = `${inputBase} border-gray-200 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20`;
    const inputError = `${inputBase} border-red-500 bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20`;

    return (
        <form
            onSubmit={handleSubmit}
            className="rounded-xl border border-gray-200 bg-white p-5 sm:p-8"
        >
            <div className="mb-8">
                <h3 className="text-xl font-bold text-gray-900">
                    Isi Form Magang-01
                </h3>
                <p className="mt-1 text-sm text-gray-500">
                    Lengkapi data di bawah ini untuk mengajukan surat keterangan
                    akademik.
                </p>
            </div>
            {errors.submit && (
                <div
                    className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
                    role="alert"
                >
                    {errors.submit}
                </div>
            )}

            <div className="flex flex-col gap-6">
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <FieldLabel htmlFor="nama">Nama Lengkap</FieldLabel>
                        <ReadOnlyInput id="nama" value={readOnlyFields.nama} />
                    </div>
                    <div>
                        <FieldLabel htmlFor="nim">NIM</FieldLabel>
                        <ReadOnlyInput id="nim" value={readOnlyFields.nim} />
                    </div>
                </div>
                <div>
                    <FieldLabel htmlFor="programStudi">
                        Program Studi
                    </FieldLabel>
                    <ReadOnlyInput
                        id="programStudi"
                        value={readOnlyFields.programStudi}
                    />
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <FieldLabel htmlFor="semester">Semester</FieldLabel>
                        <ReadOnlyInput
                            id="semester"
                            value={readOnlyFields.semester}
                        />
                    </div>
                    <div>
                        <FieldLabel htmlFor="tahunAkademik">
                            Tahun Akademik
                        </FieldLabel>
                        <ReadOnlyInput
                            id="tahunAkademik"
                            value={readOnlyFields.tahunAkademik}
                        />
                    </div>
                </div>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <FieldLabel htmlFor="jumlahSks">Jumlah SKS</FieldLabel>
                        <ReadOnlyInput
                            id="jumlahSks"
                            value={readOnlyFields.jumlahSks}
                        />
                    </div>
                    <div>
                        <FieldLabel htmlFor="ipk">IPK</FieldLabel>
                        <ReadOnlyInput id="ipk" value={readOnlyFields.ipk} />
                    </div>
                </div>
                <div>
                    <FieldLabel id="jenisMagang-label">Jenis Magang</FieldLabel>
                    <div
                        className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:gap-6"
                        role="radiogroup"
                        aria-labelledby="jenisMagang-label"
                        aria-invalid={!!errors.jenisMagang}
                        aria-describedby={
                            errors.jenisMagang ? "jenisMagang-error" : undefined
                        }
                    >
                        {[
                            { value: "wajib", label: "Magang Wajib" },
                            { value: "non_wajib", label: "Magang Non-Wajib" },
                        ].map((opt) => {
                            const locked =
                                opt.value === "wajib" && hasCompletedWajib;
                            return (
                                <label
                                    key={opt.value}
                                    className={`flex items-center gap-2 text-sm group ${
                                        locked
                                            ? "cursor-not-allowed text-gray-400"
                                            : "cursor-pointer text-black"
                                    }`}
                                >
                                    <span
                                        className={`flex h-5 w-5 items-center justify-center rounded-full border-2 transition-colors group-focus-within:ring-2 group-focus-within:ring-primary/20 ${
                                            formData.jenisMagang === opt.value
                                                ? "border-primary"
                                                : errors.jenisMagang
                                                  ? "border-red-500"
                                                  : "border-gray-300"
                                        }`}
                                    >
                                        {formData.jenisMagang === opt.value && (
                                            <span className="h-2.5 w-2.5 rounded-full bg-primary" />
                                        )}
                                    </span>
                                    <input
                                        type="radio"
                                        name="jenisMagang"
                                        value={opt.value}
                                        checked={
                                            formData.jenisMagang === opt.value
                                        }
                                        onChange={() =>
                                            updateField(
                                                "jenisMagang",
                                                opt.value,
                                            )
                                        }
                                        className="sr-only"
                                        disabled={isSubmitting || locked}
                                    />
                                    {opt.label}
                                    {locked && (
                                        <Lock className="h-3.5 w-3.5 text-gray-400" />
                                    )}
                                </label>
                            );
                        })}
                    </div>
                    {hasCompletedWajib && !errors.jenisMagang && (
                        <HelperText>
                            Anda sudah pernah menyelesaikan magang wajib,
                            sehingga hanya dapat memilih magang non-wajib.
                        </HelperText>
                    )}
                    <FieldError
                        id="jenisMagang-error"
                        message={errors.jenisMagang}
                    />
                </div>
                <div className="w-full sm:w-1/2">
                    <FieldLabel htmlFor="rencanaSkema">
                        Rencana Skema Magang
                    </FieldLabel>
                    <div className="relative">
                        <select
                            id="rencanaSkema"
                            value={formData.rencanaSkema}
                            onChange={(e) =>
                                updateField("rencanaSkema", e.target.value)
                            }
                            aria-required="true"
                            aria-invalid={!!errors.rencanaSkema}
                            aria-describedby={
                                errors.rencanaSkema
                                    ? "rencanaSkema-error"
                                    : undefined
                            }
                            className={`${
                                errors.rencanaSkema ? inputError : inputNormal
                            } appearance-none pr-10 ${
                                formData.rencanaSkema === ""
                                    ? "text-gray-400"
                                    : ""
                            }`}
                            disabled={isSubmitting}
                        >
                            <option value="">Pilih skema magang</option>
                            <option value="Magang Perusahaan">
                                Magang Perusahaan
                            </option>
                            <option value="Magang Kewirausahaan">
                                Magang Kewirausahaan
                            </option>
                        </select>
                        <ChevronDown className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    </div>
                    <FieldError
                        id="rencanaSkema-error"
                        message={errors.rencanaSkema}
                    />
                </div>
                <div>
                    <FieldLabel htmlFor="topikTempat">
                        Topik / Lingkup Magang
                    </FieldLabel>
                    <textarea
                        id="topikTempat"
                        rows={4}
                        placeholder="Tuliskan rencana tempat atau topik magang anda... Contoh: Digital Marketing"
                        value={formData.topikTempat}
                        onChange={(e) =>
                            updateField("topikTempat", e.target.value)
                        }
                        aria-required="true"
                        aria-invalid={!!errors.topikTempat}
                        aria-describedby={
                            errors.topikTempat ? "topikTempat-error" : undefined
                        }
                        className={`${
                            errors.topikTempat ? inputError : inputNormal
                        } resize-none`}
                        disabled={isSubmitting}
                    />
                    {!errors.topikTempat && (
                        <HelperText>
                            Untuk Magang Perusahaan tulis nama instansi /
                            perusahaan yang lengkap. Untuk Magang Kewirausahaan
                            tulis topik atau rencana usaha yang akan diajukan.
                        </HelperText>
                    )}
                    <FieldError
                        id="topikTempat-error"
                        message={errors.topikTempat}
                    />
                </div>

                <div>
                    <FieldLabel id="output-label">Output</FieldLabel>
                    <div
                        className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:gap-6"
                        role="radiogroup"
                        aria-labelledby="output-label"
                        aria-invalid={!!errors.output}
                        aria-describedby={
                            errors.output ? "output-error" : undefined
                        }
                    >
                        {["Produk", "Prototype", "Laporan"].map((opt) => (
                            <label
                                key={opt}
                                className="flex cursor-pointer items-center gap-2 text-sm text-black group"
                            >
                                <span
                                    className={`flex h-5 w-5 items-center justify-center rounded-full border-2 transition-colors group-focus-within:ring-2 group-focus-within:ring-primary/20 ${
                                        formData.output === opt
                                            ? "border-primary"
                                            : errors.output
                                              ? "border-red-500"
                                              : "border-gray-300"
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
                                    onChange={() => updateField("output", opt)}
                                    className="sr-only"
                                    disabled={isSubmitting}
                                />
                                {opt}
                            </label>
                        ))}
                    </div>
                    <FieldError id="output-error" message={errors.output} />
                </div>
                <label className="flex cursor-pointer items-start gap-3">
                    <input
                        type="checkbox"
                        checked={formData.declarationChecked}
                        onChange={(e) =>
                            updateField("declarationChecked", e.target.checked)
                        }
                        aria-invalid={!!errors.declarationChecked}
                        aria-describedby={
                            errors.declarationChecked
                                ? "declarationChecked-error"
                                : undefined
                        }
                        className="mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-gray-300 accent-primary focus:ring-primary/20"
                        disabled={isSubmitting}
                    />
                    <span
                        className={`text-[13px] leading-relaxed ${
                            errors.declarationChecked
                                ? "font-medium text-red-500"
                                : "text-primary"
                        }`}
                    >
                        *Saya menyatakan bahwa data di atas adalah benar dan
                        saya telah memenuhi syarat minimum SKS untuk mengambil
                        mata kuliah magang sesuai kurikulum Program Studi.
                    </span>
                </label>
                <FieldError
                    id="declarationChecked-error"
                    message={errors.declarationChecked}
                />
            </div>
            <div className="mt-8 flex flex-col-reverse items-stretch justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:gap-4">
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
                        flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-bold
                        transition-all duration-200
                        ${
                            isFormValid && !isSubmitting
                                ? "bg-primary text-white shadow hover:bg-primary-hover hover:shadow-md"
                                : "cursor-not-allowed bg-gray-300 text-gray-400"
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
