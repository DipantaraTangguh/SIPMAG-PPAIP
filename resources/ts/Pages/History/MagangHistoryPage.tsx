import React, { useEffect, useState } from "react";
import {
    History,
    GraduationCap,
    Briefcase,
    ChevronRight,
    Building2,
    CalendarRange,
    Award,
    FileText,
    Loader2,
} from "lucide-react";
import DashboardLayout from "../../Components/Layouts/DashboardLayout";
import { api } from "../../lib/api";

function formatPeriod(start, end) {
    const fmt = (d) =>
        d
            ? new Date(d).toLocaleDateString("id-ID", {
                  month: "long",
                  year: "numeric",
              })
            : null;
    const a = fmt(start);
    const b = fmt(end);
    if (a && b) return `${a} – ${b}`;
    return a || b || "-";
}

function formatCompletedAt(value) {
    return value
        ? new Date(value).toLocaleDateString("id-ID", {
              day: "numeric",
              month: "long",
              year: "numeric",
          })
        : "-";
}

function jenisLabel(jenis) {
    return jenis === "wajib" ? "Magang Wajib" : "Magang Non-Wajib";
}

function DetailField({ label, value, valueClassName = "" }) {
    return (
        <div>
            <p className="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                {label}
            </p>
            <p
                className={`mt-1 text-sm font-semibold text-gray-900 ${valueClassName}`}
            >
                {value ?? "-"}
            </p>
        </div>
    );
}

function CycleDetail({ cycle }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5 sm:p-8">
            <div className="mb-6 flex flex-wrap items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                    {cycle.jenis_magang === "wajib" ? (
                        <GraduationCap className="h-5 w-5 text-primary" />
                    ) : (
                        <Briefcase className="h-5 w-5 text-primary" />
                    )}
                </span>
                <div>
                    <h3 className="text-lg font-bold text-gray-900">
                        Magang #{cycle.cycle_number} -{" "}
                        {jenisLabel(cycle.jenis_magang)}
                    </h3>
                    <p className="text-xs text-gray-400">
                        Selesai pada {formatCompletedAt(cycle.completed_at)}
                    </p>
                </div>
                {cycle.letter_grade && (
                    <span className="ml-auto rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-700">
                        {cycle.final_score} ({cycle.letter_grade})
                    </span>
                )}
            </div>

            <div className="mb-2 flex items-center gap-2">
                <FileText className="h-4 w-4 text-primary" />
                <h4 className="text-sm font-bold text-gray-900">
                    Data Pengajuan
                </h4>
            </div>
            <div className="mb-6 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <DetailField label="Nama" value={cycle.nama} />
                <DetailField label="NIM" value={cycle.nim} />
                <DetailField
                    label="Program Studi"
                    value={cycle.study_program}
                />
                <DetailField label="Semester" value={cycle.semester} />
                <DetailField
                    label="IPK Saat Itu"
                    value={cycle.ipk}
                    valueClassName="text-primary"
                />
                <DetailField
                    label="Skema Magang"
                    value={cycle.skema_magang}
                />
                <DetailField
                    label="Topik / Lingkup"
                    value={cycle.topik_magang}
                />
                <DetailField
                    label="Target Output"
                    value={cycle.output_target}
                />
            </div>

            <div className="mb-2 flex items-center gap-2">
                <Building2 className="h-4 w-4 text-primary" />
                <h4 className="text-sm font-bold text-gray-900">
                    Tempat & Periode Magang
                </h4>
            </div>
            <div className="mb-6 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <DetailField
                    label="Perusahaan / Instansi"
                    value={cycle.company_name}
                />
                <DetailField
                    label="Pimpinan"
                    value={cycle.nama_pimpinan}
                />
                <div className="sm:col-span-2">
                    <DetailField
                        label="Alamat"
                        value={cycle.alamat_perusahaan}
                    />
                </div>
                <DetailField
                    label="Periode"
                    value={formatPeriod(
                        cycle.tanggal_mulai,
                        cycle.tanggal_selesai,
                    )}
                />
            </div>

            <div className="mb-2 flex items-center gap-2">
                <Award className="h-4 w-4 text-primary" />
                <h4 className="text-sm font-bold text-gray-900">Hasil</h4>
            </div>
            <div className="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <DetailField
                    label="Status Akhir"
                    value={
                        cycle.outcome_status === "CycleCompleted"
                            ? "Siklus Selesai (Sidang & Penilaian)"
                            : "Selesai Non-Wajib (Surat Pengantar)"
                    }
                />
                <DetailField
                    label="Nilai Akhir"
                    value={
                        cycle.final_score != null
                            ? `${cycle.final_score} (${cycle.letter_grade})`
                            : "- (magang non-wajib tidak dinilai)"
                    }
                    valueClassName={
                        cycle.final_score != null ? "text-green-700" : ""
                    }
                />
            </div>
        </div>
    );
}

export default function MagangHistoryPage() {
    const [cycles, setCycles] = useState([]);
    const [selectedId, setSelectedId] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;
        api.get("/student/cycle/history")
            .then((res) => {
                if (!active) return;
                const list = res.cycles ?? [];
                setCycles(list);
                setSelectedId(list[0]?.id ?? null);
            })
            .catch(() => {
                /* biarkan kosong bila gagal */
            })
            .finally(() => {
                if (active) setIsLoading(false);
            });
        return () => {
            active = false;
        };
    }, []);

    const selected = cycles.find((c) => c.id === selectedId) ?? null;

    return (
        <DashboardLayout pageTitle="Riwayat Magang">
            <p className="mb-6 text-sm text-primary">
                Daftar siklus magang yang telah Anda selesaikan. Riwayat tetap
                tersimpan walaupun siklus magang sudah direset.
            </p>

            {isLoading ? (
                <div className="flex items-center justify-center gap-2 py-20 text-gray-400">
                    <Loader2 className="h-5 w-5 animate-spin" />
                    Memuat riwayat magang...
                </div>
            ) : cycles.length === 0 ? (
                <div className="flex flex-col items-center rounded-xl border border-gray-200 bg-white px-6 py-16 text-center">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <History className="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 className="mt-4 text-lg font-bold text-gray-900">
                        Belum Ada Riwayat Magang
                    </h3>
                    <p className="mt-2 max-w-sm text-sm text-gray-500">
                        Riwayat akan muncul setelah Anda menyelesaikan satu
                        siklus magang, baik wajib maupun non-wajib.
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-[380px_1fr]">
                    <ul className="flex flex-col gap-3">
                        {cycles.map((c) => {
                            const active = c.id === selectedId;
                            return (
                                <li key={c.id}>
                                    <button
                                        type="button"
                                        onClick={() => setSelectedId(c.id)}
                                        aria-current={
                                            active ? "true" : undefined
                                        }
                                        className={`flex w-full items-start gap-3 rounded-xl border p-4 text-left transition-colors ${
                                            active
                                                ? "border-primary bg-primary/5"
                                                : "border-gray-200 bg-white hover:border-primary/40"
                                        }`}
                                    >
                                        <span className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                            {c.jenis_magang === "wajib" ? (
                                                <GraduationCap className="h-4 w-4 text-primary" />
                                            ) : (
                                                <Briefcase className="h-4 w-4 text-primary" />
                                            )}
                                        </span>
                                        <span className="min-w-0 flex-1">
                                            <span className="flex flex-wrap items-center gap-2">
                                                <span className="text-sm font-semibold text-gray-900">
                                                    Magang #{c.cycle_number} -{" "}
                                                    {jenisLabel(c.jenis_magang)}
                                                </span>
                                                {c.letter_grade && (
                                                    <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs font-bold text-green-700">
                                                        {c.letter_grade}
                                                    </span>
                                                )}
                                            </span>
                                            <span className="mt-0.5 block truncate text-sm text-gray-600">
                                                {c.company_name ??
                                                    c.topik_magang ??
                                                    "-"}
                                            </span>
                                            <span className="mt-0.5 flex items-center gap-1 text-xs text-gray-400">
                                                <CalendarRange className="h-3 w-3" />
                                                {formatPeriod(
                                                    c.tanggal_mulai,
                                                    c.tanggal_selesai,
                                                )}
                                            </span>
                                        </span>
                                        <ChevronRight
                                            className={`mt-3 h-4 w-4 shrink-0 ${
                                                active
                                                    ? "text-primary"
                                                    : "text-gray-300"
                                            }`}
                                        />
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                    {selected && <CycleDetail cycle={selected} />}
                </div>
            )}
        </DashboardLayout>
    );
}
