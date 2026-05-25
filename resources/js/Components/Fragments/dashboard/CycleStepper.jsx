/**
 * CycleStepper.jsx
 * Horizontal 5-step progress indicator for the internship lifecycle.
 *
 * Steps: Syarat Akademik → Lamaran → Pengajuan Pembimbing → Logbook → Sidang
 *
 * @prop {number} currentStep — 1-based index of the active step (1–5).
 */
import React from 'react';
import {
    ClipboardCheck,
    FileText,
    UserCheck,
    BookOpen,
    Award,
    Check,
} from 'lucide-react';

const steps = [
    { key: 'syarat_akademik', label: 'Syarat Akademik', icon: ClipboardCheck },
    { key: 'lamaran', label: 'Lamaran', icon: FileText },
    { key: 'pengajuan_pembimbing', label: 'Pengajuan Pembimbing', icon: UserCheck },
    { key: 'logbook', label: 'Logbook', icon: BookOpen },
    { key: 'sidang', label: 'Sidang', icon: Award },
];

export default function CycleStepper({ currentStep = 1 }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white px-8 py-7">
            <div className="flex items-center justify-between">
                {steps.map((step, idx) => {
                    const stepNum = idx + 1;
                    const isCompleted = stepNum < currentStep;
                    const isActive = stepNum === currentStep;
                    const isUpcoming = stepNum > currentStep;
                    const Icon = step.icon;

                    return (
                        <React.Fragment key={step.key}>
                            {/* Step node */}
                            <div className="flex flex-col items-center gap-2">
                                <div
                                    className={`
                                        flex h-11 w-11 items-center justify-center rounded-full
                                        transition-colors duration-200
                                        ${isCompleted
                                            ? 'bg-primary text-white'
                                            : isActive
                                                ? 'bg-primary text-white'
                                                : 'border-2 border-gray-300 bg-white text-gray-400'
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
                                        max-w-[110px] text-center text-xs
                                        ${isActive
                                            ? 'font-semibold text-primary'
                                            : isCompleted
                                                ? 'font-medium text-primary'
                                                : 'font-normal text-gray-400'
                                        }
                                    `}
                                >
                                    {step.label}
                                </span>
                            </div>

                            {/* Connector line (not after last step) */}
                            {idx < steps.length - 1 && (
                                <div
                                    className={`
                                        mx-2 mb-6 h-[2px] flex-1
                                        ${stepNum < currentStep
                                            ? 'bg-primary'
                                            : 'bg-gray-200'
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
