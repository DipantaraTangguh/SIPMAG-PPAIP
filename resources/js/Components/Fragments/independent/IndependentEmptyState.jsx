/**
 * IndependentEmptyState.jsx
 * Displayed when user has no Form 2 submissions in the Independent tab.
 *
 * @prop {function} onCreateNew - Callback to navigate to Form 2 fill page.
 * @prop {boolean}  isLocked    - When true, disable the CTA button (Form 1 not approved).
 */
import React from 'react';
import { FileText, Plus } from 'lucide-react';

export default function IndependentEmptyState({ onCreateNew, isLocked = false }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-6 text-center sm:p-12">
            <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
                <FileText className="h-9 w-9 text-gray-400" />
            </div>

            <h3 className="mt-5 text-[18px] font-bold text-[#1A1A1A]">
                Belum Ada Pengajuan Form 2
            </h3>

            <p className="mx-auto mt-2 max-w-[340px] text-[13px] leading-relaxed text-gray-400">
                Daftar perusahaan yang Anda lamar akan muncul di sini. Silakan
                mulai dengan membuat pengajuan baru untuk mendapatkan surat
                pengantar resmi.
            </p>

            <button
                type="button"
                onClick={isLocked ? undefined : onCreateNew}
                disabled={isLocked}
                className={`mx-auto mt-6 flex w-full items-center justify-center gap-2 rounded-lg px-6 py-2.5 font-bold transition-colors sm:w-auto ${
                    isLocked
                        ? 'cursor-not-allowed bg-gray-200 text-gray-400'
                        : 'bg-primary-pale text-primary hover:bg-primary/20'
                }`}
            >
                <Plus className="h-5 w-5 flex-shrink-0" />
                <span className="text-[13px]">Buat Pengajuan Pertama</span>
            </button>
        </div>
    );
}
