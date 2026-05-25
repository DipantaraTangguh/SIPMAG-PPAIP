/**
 * components/guidance/LogbookTabContent.jsx
 * Logbook tab content — table, progress, add/edit modals.
 * Extracted from GuidancePage.jsx lines 859–1116.
 */
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import { BookOpen, Info, Plus, GraduationCap, X } from 'lucide-react';
import { useSimulation } from '../../../context/SimulationContext';

export default function LogbookTabContent() {
    const navigate = useNavigate();
    const {
        logbookEntries,
        addLogbookEntry,
        updateLogbookEntry,
    } = useSimulation();

    const [showAddModal, setShowAddModal] = useState(false);
    const [editEntry, setEditEntry] = useState(null);
    const [newEntry, setNewEntry] = useState({
        tanggal: '',
        kegiatanHarian: '',
        hasil: ''
    });

    const TOTAL_REQUIRED = 6;
    const approvedCount = logbookEntries.filter((e) => e.status === 'Disetujui').length;
    const isComplete = approvedCount >= TOTAL_REQUIRED;

    const handleAddEntry = () => {
        if (!newEntry.kegiatanHarian.trim() || !newEntry.hasil.trim()) return;
        addLogbookEntry(newEntry);
        setNewEntry({ tanggal: '', kegiatanHarian: '', hasil: '' });
        setShowAddModal(false);
    };

    const handleEditSave = () => {
        if (!editEntry.kegiatanHarian.trim() || !editEntry.hasil.trim()) return;
        updateLogbookEntry(editEntry.id, {
            kegiatanHarian: editEntry.kegiatanHarian,
            hasil: editEntry.hasil,
        });
        setEditEntry(null);
    };

    // Modal state helpers
    const isEdit = editEntry !== null;
    const isModalOpen = showAddModal || isEdit;
    const modalTitle = isEdit ? 'Perbaiki Entri Logbook' : 'Tambah Entri Logbook';
    const activeData = isEdit ? editEntry : newEntry;
    const updateActiveData = (updates) => isEdit ? setEditEntry({ ...editEntry, ...updates }) : setNewEntry({ ...newEntry, ...updates });
    const handleModalSave = isEdit ? handleEditSave : handleAddEntry;
    const closeAllModals = () => { setShowAddModal(false); setEditEntry(null); };

    return (
        <div className="mt-8 animate-in fade-in duration-500">
            {/* TOP ROW */}
            <div className="flex flex-col items-stretch gap-5 xl:flex-row">
                {/* Left Card: Progress Info & Add Button */}
                <div className={`flex flex-1 items-center justify-between rounded-xl border border-l-4 bg-white px-7 py-6 shadow-sm ${isComplete ? 'border-green-200 border-l-green-500' : 'border-gray-200 border-l-primary'}`}>
                    <div>
                        <p className="mb-2.5 text-[12px] font-bold uppercase tracking-wider text-gray-500">
                            PROGRESS PERSETUJUAN
                        </p>
                        <div className="flex flex-wrap items-baseline gap-3">
                            <span className={`text-[36px] font-bold leading-none ${isComplete ? 'text-green-500' : 'text-primary'}`}>
                                {approvedCount}/{TOTAL_REQUIRED}
                            </span>
                            <span className="text-[14px] font-medium text-gray-600">
                                Entri Disetujui
                            </span>
                        </div>
                        <div className="mt-3.5 h-[10px] w-[240px] max-w-full overflow-hidden rounded-full bg-gray-100">
                            <div
                                className={`h-full rounded-full transition-all duration-500 ${isComplete ? 'bg-green-500' : 'bg-primary'}`}
                                style={{ width: `${Math.min((approvedCount / TOTAL_REQUIRED) * 100, 100)}%` }}
                            />
                        </div>
                    </div>

                    {isComplete ? (
                        <button
                            onClick={() => navigate('/defense')}
                            className="ml-4 flex items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-[14px] font-bold text-white transition-colors hover:bg-green-600"
                        >
                            <GraduationCap className="h-[18px] w-[18px]" />
                            <span>Daftar Sidang →</span>
                        </button>
                    ) : (
                        <button
                            onClick={() => setShowAddModal(true)}
                            className="ml-4 flex items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-[14px] font-bold text-white transition-colors hover:bg-primary-hover"
                        >
                            <span>+ Tambah Entri</span>
                        </button>
                    )}
                </div>

                {/* Right Card: Informasi Penting */}
                <div className="relative w-full overflow-hidden rounded-xl bg-primary p-6 shadow-sm xl:w-[280px] xl:flex-shrink-0">
                    <h3 className="text-[15px] font-bold text-white">Informasi Penting</h3>
                    <p className="mt-2 text-[13px] leading-relaxed text-white opacity-90">
                        Pastikan logbook diisi setiap hari kerja. Review pembimbing dilakukan setiap akhir pekan.
                    </p>
                    <Info className="absolute -bottom-4 -right-2 h-[100px] w-[100px] text-white opacity-10" />
                </div>
            </div>

            {/* LOGBOOK TABLE OR EMPTY STATE */}
            {logbookEntries.length === 0 ? (
                <div className="mt-6 flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-12 text-center">
                    <BookOpen className="text-gray-300" size={48} />
                    <h3 className="mt-3 text-[16px] font-bold text-gray-400">
                        Belum Ada Entri Logbook
                    </h3>
                    <p className="mt-1.5 text-[13px] text-gray-400">
                        Klik '+ Tambah Entri' untuk mulai mencatat kegiatan magang harian Anda.
                    </p>
                </div>
            ) : (
                <div className="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b border-gray-200 bg-gray-50">
                                <th className="w-[140px] px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">Tanggal</th>
                                <th className="px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">Kegiatan Harian</th>
                                <th className="w-[180px] px-5 py-3.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">Hasil</th>
                                <th className="w-[140px] px-5 py-3.5 text-center text-[11px] font-medium uppercase tracking-wider text-gray-400">Status</th>
                                <th className="w-[80px] px-5 py-3.5 text-right text-[11px] font-medium uppercase tracking-wider text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logbookEntries.map((entry, idx) => (
                                <tr key={entry.id} className={`group bg-white hover:bg-gray-50/50 ${idx !== logbookEntries.length - 1 ? 'border-b border-gray-100' : ''}`}>
                                    <td className="px-5 py-4 align-top">
                                        <p className="text-[14px] font-bold text-[#1A1A1A]">{entry.tanggal}</p>
                                        <p className="mt-0.5 text-[12px] text-gray-400">{entry.hari}</p>
                                    </td>
                                    <td className="relative px-5 py-4 align-top">
                                        <p className="max-w-full truncate text-[14px] font-bold text-[#1A1A1A]">{entry.kegiatanHarian}</p>
                                        <p className="mt-0.5 line-clamp-2 text-[12px] text-gray-500">{entry.kegiatanDetail}</p>
                                    </td>
                                    <td className="px-5 py-4 align-top">
                                        <p className={`text-[13px] ${entry.hasil === '-' ? 'text-gray-300' : 'text-gray-600'}`}>{entry.hasil}</p>
                                    </td>
                                    <td className="w-[140px] px-5 py-4 align-top text-center">
                                        <span className={`inline-block w-full whitespace-normal rounded-full px-3.5 py-1 text-center text-[12px] font-bold ${
                                            entry.status === 'Disetujui' ? 'bg-green-100 text-green-600' :
                                            entry.status === 'Menunggu Review' ? 'bg-amber-100 text-amber-600' :
                                            'bg-red-100 text-red-500'
                                        }`}>
                                            {entry.status}
                                        </span>
                                    </td>
                                    <td className="px-5 py-4 text-right align-top">
                                        {entry.status === 'Disetujui' && (
                                            <button onClick={() => console.log('view detail', entry.id)} className="text-[13px] font-bold text-primary hover:underline">
                                                Detail
                                            </button>
                                        )}
                                        {entry.status === 'Ditolak' && (
                                            <button onClick={() => setEditEntry(entry)} className="text-[13px] font-bold text-primary hover:underline">
                                                Perbaiki
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* MODAL */}
            {/* MODAL */}
            {isModalOpen && typeof document !== 'undefined' && createPortal(
                <div
                    className="fixed inset-0 z-[99] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/45 p-4 sm:p-0"
                    onClick={closeAllModals}
                >
                    <div
                        className="relative w-full max-w-[560px] transform rounded-xl bg-white text-left shadow-xl transition-all sm:my-8"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <div className="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                            <h2 className="text-[17px] font-bold text-[#1A1A1A]">{modalTitle}</h2>
                            <button
                                type="button"
                                onClick={closeAllModals}
                                className="rounded-full p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/20"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>
                        
                        <div className="max-h-[calc(100vh-160px)] overflow-y-auto px-6 py-5">
                            <div className="flex flex-col gap-5">
                                <div>
                                    <label className="mb-2 block text-[13px] font-bold text-[#1A1A1A]">Tanggal</label>
                                    <input
                                        type="date"
                                        value={activeData.tanggal}
                                        onChange={(e) => updateActiveData({ tanggal: e.target.value })}
                                        className="h-11 w-full rounded-lg border border-gray-200 px-3 outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                </div>
                                <div>
                                    <label className="mb-2 flex justify-between text-[13px] font-bold text-[#1A1A1A]">
                                        <span>Kegiatan Harian <span className="text-red-500">*</span></span>
                                        <span className="font-normal text-gray-400">Maks. 500 karakter</span>
                                    </label>
                                    <textarea
                                        rows="4"
                                        maxLength={500}
                                        value={activeData.kegiatanHarian}
                                        onChange={(e) => updateActiveData({ kegiatanHarian: e.target.value })}
                                        placeholder="Deskripsikan kegiatan yang Anda lakukan hari ini..."
                                        className="w-full resize-none rounded-lg border border-gray-200 p-3 text-[14px] leading-relaxed outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                    <div className="mt-1 text-right text-[11px] font-medium text-gray-400">
                                        {activeData.kegiatanHarian?.length || 0} / 500
                                    </div>
                                </div>
                                <div>
                                    <label className="mb-2 flex justify-between text-[13px] font-bold text-[#1A1A1A]">
                                        <span>Hasil <span className="text-red-500">*</span></span>
                                        <span className="font-normal text-gray-400">Maks. 300 karakter</span>
                                    </label>
                                    <textarea
                                        rows="3"
                                        maxLength={300}
                                        value={activeData.hasil}
                                        onChange={(e) => updateActiveData({ hasil: e.target.value })}
                                        placeholder="Tuliskan output atau hasil dari kegiatan Anda..."
                                        className="w-full resize-none rounded-lg border border-gray-200 p-3 text-[14px] leading-relaxed outline-none transition-colors focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                    <div className="mt-1 text-right text-[11px] font-medium text-gray-400">
                                        {activeData.hasil?.length || 0} / 300
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-2 rounded-b-xl border-t border-gray-100 bg-white px-6 py-4">
                            <button
                                type="button"
                                onClick={closeAllModals}
                                className="rounded-lg border border-gray-200 bg-white px-[18px] py-[9px] text-[13px] font-bold text-gray-600 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                disabled={!activeData.kegiatanHarian?.trim() || !activeData.hasil?.trim()}
                                onClick={handleModalSave}
                                className="rounded-lg bg-primary px-5 py-[9px] text-[13px] font-bold text-white transition-colors hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none"
                            >
                                Simpan Entri
                            </button>
                        </div>
                    </div>
                </div>,
                document.body
            )}
        </div>
    );
}
