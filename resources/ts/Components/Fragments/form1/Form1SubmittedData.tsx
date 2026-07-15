import React from "react";
import { FileText, Calendar } from "lucide-react";
function DataField({ label, value, valueClassName = "" }) {
    return (
        <div>
            <p className="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                {label}
            </p>
            <p
                className={`mt-1 text-sm font-semibold text-gray-900 ${valueClassName}`}
            >
                {value || "—"}
            </p>
        </div>
    );
}

export default function Form1SubmittedData({ formData }) {
    return (
        <div className="flex flex-col rounded-xl border border-gray-200 bg-white p-5 sm:p-8">
            <div className="mb-6 flex items-center gap-2">
                <FileText className="h-5 w-5 text-primary" />
                <h3 className="text-lg font-bold text-gray-900">
                    Data Pengajuan Mahasiswa
                </h3>
            </div>
            <div className="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                <DataField label="Nama Lengkap" value={formData.nama} />
                <DataField label="NIM" value={formData.nim} />
                <DataField
                    label="Program Studi"
                    value={formData.programStudi}
                />
                <DataField label="Semester" value={formData.semester} />
                <DataField
                    label="Tahun Akademik"
                    value={formData.tahunAkademik}
                />
                <DataField label="Jumlah SKS" value={formData.jumlahSks} />
                <DataField
                    label="IPK Terakhir"
                    value={formData.ipk}
                    valueClassName="text-primary"
                />
                <DataField
                    label="Jenis Magang"
                    value={
                        formData.jenisMagang === "wajib"
                            ? "Magang Wajib"
                            : formData.jenisMagang === "non_wajib"
                              ? "Magang Non-Wajib"
                              : formData.jenisMagang
                    }
                />
                <DataField
                    label="Rencana Skema Magang"
                    value={formData.rencanaSkema}
                />
                <DataField
                    label="Topik / Lingkup"
                    value={formData.topikTempat}
                />
                <DataField label="Target Output" value={formData.output} />
            </div>
            <div className="mt-6 border-t border-gray-100 pt-4">
                <div className="flex items-center gap-1.5 text-[13px] italic text-gray-500">
                    <Calendar className="h-3.5 w-3.5 text-gray-400" />
                    <span>Diajukan pada {formData.submittedAt || "—"}</span>
                </div>
            </div>
        </div>
    );
}
