import React from "react";
import {
    ClipboardCheck,
    FileText,
    UserCheck,
    BookOpen,
    Award,
    BadgeCheck,
    Check,
} from "lucide-react";

const WAJIB_STEPS = [
    { key: "syarat_akademik", label: "Syarat Akademik", icon: ClipboardCheck },
    { key: "lamaran", label: "Lamaran", icon: FileText },
    {
        key: "pengajuan_pembimbing",
        label: "Pengajuan Pembimbing",
        icon: UserCheck,
    },
    { key: "logbook", label: "Logbook", icon: BookOpen },
    { key: "sidang", label: "Sidang", icon: Award },
];

// Magang non-wajib berhenti setelah konfirmasi LoA — tanpa DPM/logbook/sidang.
const NON_WAJIB_STEPS = [
    { key: "syarat_akademik", label: "Syarat Akademik", icon: ClipboardCheck },
    { key: "pengantar_lamaran", label: "Pengantar / Lamaran", icon: FileText },
    { key: "konfirmasi", label: "Konfirmasi Magang", icon: BadgeCheck },
];

export default function CycleStepper({ currentStep = 1, variant = "wajib" }) {
    const steps = variant === "non_wajib" ? NON_WAJIB_STEPS : WAJIB_STEPS;

    return (
        <div className="rounded-xl border border-gray-200 bg-white px-4 py-5 sm:px-8 sm:py-7">
            <div className="flex items-start justify-between gap-2 overflow-x-auto pb-1 sm:items-center">
                {steps.map((step, idx) => {
                    const stepNum = idx + 1;
                    const isCompleted = stepNum < currentStep;
                    const isActive = stepNum === currentStep;
                    const isUpcoming = stepNum > currentStep;
                    const Icon = step.icon;

                    return (
                        <React.Fragment key={step.key}>
                            <div className="flex min-w-18 flex-col items-center gap-2 sm:min-w-0">
                                <div
                                    className={`
                                        flex h-10 w-10 items-center justify-center rounded-full sm:h-11 sm:w-11
                                        transition-colors duration-200
                                        ${
                                            isCompleted
                                                ? "bg-primary text-white"
                                                : isActive
                                                  ? "bg-primary text-white"
                                                  : "border-2 border-gray-300 bg-white text-gray-400"
                                        }
                                    `}
                                >
                                    {isCompleted ? (
                                        <Check className="h-5 w-5" />
                                    ) : (
                                        <Icon className="h-5 w-5" />
                                    )}
                                </div>
                                <span
                                    className={`
                                        max-w-27.5 text-center text-xs
                                        ${
                                            isActive
                                                ? "font-semibold text-primary"
                                                : isCompleted
                                                  ? "font-medium text-primary"
                                                  : "font-normal text-gray-400"
                                        }
                                    `}
                                >
                                    {step.label}
                                </span>
                            </div>
                            {idx < steps.length - 1 && (
                                <div
                                    className={`
                                        mx-1 mt-5 h-0.5 min-w-6 flex-1 sm:mx-2 sm:mb-6 sm:mt-0
                                        ${
                                            stepNum < currentStep
                                                ? "bg-primary"
                                                : "bg-gray-200"
                                        }
                                    `}
                                />
                            )}
                        </React.Fragment>
                    );
                })}
            </div>
        </div>
    );
}
