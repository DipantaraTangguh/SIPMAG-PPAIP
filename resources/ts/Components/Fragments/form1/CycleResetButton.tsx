import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { RotateCcw, Loader2 } from "lucide-react";
import { useForm1Workflow } from "../../../context/StudentWorkflowContext";

/**
 * Tombol reset siklus mandiri. Muncul di panel SiklusSelesai dan
 * SelesaiNonWajib. Riwayat magang tetap tersimpan setelah reset.
 */
export default function CycleResetButton() {
    const { resetCycle } = useForm1Workflow();
    const navigate = useNavigate();
    const [confirming, setConfirming] = useState(false);
    const [isResetting, setIsResetting] = useState(false);
    const [error, setError] = useState(null);

    const handleReset = async () => {
        setIsResetting(true);
        setError(null);
        try {
            await resetCycle();
            navigate("/form1", { replace: true });
        } catch (err) {
            setError(err?.message || "Gagal mereset siklus magang.");
            setIsResetting(false);
            setConfirming(false);
        }
    };

    if (!confirming) {
        return (
            <div className="mt-5">
                <button
                    type="button"
                    onClick={() => setConfirming(true)}
                    className="flex w-full items-center justify-center gap-2 rounded-lg border border-primary px-5 py-3 text-sm font-bold text-primary transition-colors hover:bg-primary hover:text-white"
                >
                    <RotateCcw className="h-4 w-4" />
                    Reset Siklus & Daftar Magang Lagi
                </button>
                {error && (
                    <p className="mt-2 text-xs text-red-500" role="alert">
                        {error}
                    </p>
                )}
            </div>
        );
    }

    return (
        <div className="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p className="text-sm text-amber-800">
                Siklus aktif akan diakhiri dan Anda kembali ke pengisian Form 1.
                Riwayat magang yang sudah selesai tetap tersimpan. Lanjutkan?
            </p>
            <div className="mt-3 flex gap-3">
                <button
                    type="button"
                    onClick={handleReset}
                    disabled={isResetting}
                    className="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-primary-hover disabled:cursor-not-allowed disabled:bg-gray-300"
                >
                    {isResetting ? (
                        <>
                            <Loader2 className="h-4 w-4 animate-spin" />
                            Mereset…
                        </>
                    ) : (
                        "Ya, Reset Siklus"
                    )}
                </button>
                <button
                    type="button"
                    onClick={() => setConfirming(false)}
                    disabled={isResetting}
                    className="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900"
                >
                    Batal
                </button>
            </div>
        </div>
    );
}
