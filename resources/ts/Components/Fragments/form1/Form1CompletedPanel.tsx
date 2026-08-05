import React, { useEffect, useState } from "react";
import { CheckCircle, Archive, Award } from "lucide-react";
import CycleResetButton from "./CycleResetButton";
import { api } from "../../../lib/api";

export default function Form1CompletedPanel() {
    // Nilai terbit otomatis setelah DPM + 2 penguji menilai, jadi tampilkan
    // langsung di panel ini tanpa mahasiswa harus buka halaman Riwayat.
    const [grade, setGrade] = useState(null);

    useEffect(() => {
        let active = true;

        api.get("/student/cycle/history")
            .then((res) => {
                if (!active) return;
                // Sudah terurut cycle_number desc dari server.
                const cycles = res?.cycles ?? [];
                const latest = cycles.find((c) => c.final_score != null);
                if (latest) setGrade(latest);
            })
            .catch(() => {});

        return () => {
            active = false;
        };
    }, []);

    return (
        <div className="rounded-xl border border-gray-200 border-l-4 border-l-green-600 bg-white p-6">
            <div className="flex flex-col items-center text-center">
                <div className="flex h-18 w-18 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle className="h-10 w-10 text-green-600" />
                </div>
                <h3 className="mt-3 text-xl font-bold text-green-700">
                    Siklus Magang Selesai
                </h3>
                <p className="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">
                    Seluruh tahapan magang telah diselesaikan. Data Form
                    Magang-01 tetap tersimpan sebagai riwayat akademik.
                </p>
            </div>
            {grade && (
                <div className="mt-5 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4">
                    <Award className="h-5 w-5 shrink-0 text-green-600" />
                    <div>
                        <p className="text-[11px] font-medium uppercase tracking-wider text-green-700">
                            Nilai Akhir Magang
                        </p>
                        <p className="mt-0.5 text-lg font-bold text-green-800">
                            {grade.final_score}
                            {grade.letter_grade
                                ? ` (${grade.letter_grade})`
                                : ""}
                        </p>
                    </div>
                </div>
            )}
            <div className="mt-5 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <Archive className="h-5 w-5 shrink-0 text-primary" />
                <p className="text-sm text-gray-600">
                    Profil ini merupakan arsip dari siklus magang yang telah
                    selesai. Anda dapat mereset siklus untuk mendaftar magang
                    non-wajib.
                </p>
            </div>
            <CycleResetButton />
        </div>
    );
}
