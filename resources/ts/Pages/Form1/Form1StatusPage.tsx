import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AppContext';
import { useForm1Workflow } from '../../context/StudentWorkflowContext';
import DashboardLayout from '../../Components/Layouts/DashboardLayout';
import Form1SubmittedData from '../../Components/Fragments/form1/Form1SubmittedData';
import Form1StatusPanel from '../../Components/Fragments/form1/Form1StatusPanel';

export default function Form1StatusPage() {
    const { student } = useAuth();
    const { form1Submission } = useForm1Workflow();

    // Redirect balik hanya kalau memang belum pernah submit Form 1.
    // Pakai accessStatus karena setState bisa telat masuk setelah submit.
    // Jadi status page nggak mental balik gara-gara data form belum settle.
    const status = student?.accessStatus;
    const hasNotSubmitted = !status || status === 'Unverified' || status === 'RejectedForm1';
    if (hasNotSubmitted && !form1Submission) {
        return <Navigate to="/form1" replace />;
    }

    // Siklus selesai diarsipkan: form1_data sengaja tidak lagi dihapus, tapi cycle
    // lama bisa punya data null. Tetap render arsipnya dari profil mahasiswa.
    const isCompleted = status === 'CycleCompleted' || status === 'ElectiveCompleted';

    // Kalau status sudah maju tapi data belum nyampe, tahan dulu pakai loader.
    if (!form1Submission && !isCompleted) {
        return (
            <DashboardLayout pageTitle="Status Form Magang 01">
                <div className="flex items-center justify-center py-20 text-gray-400">
                    Memuat data form...
                </div>
            </DashboardLayout>
        );
    }

    // Komponen status butuh shape data yang konsisten.
    // Data bisa datang dari API baru atau simulasi lama, jadi normalize di sini.
    // Fallback ke profil mahasiswa agar arsip siklus selesai tetap lengkap.
    const f = form1Submission ?? {};
    const formData = {
        nama: f.nama ?? student?.name ?? '',
        nim: f.nim ?? student?.nim ?? '',
        programStudi: f.programStudi ?? student?.programStudi ?? '',
        semester: f.semester ?? student?.semester,
        tahunAkademik: f.tahunAkademik ?? student?.tahunAkademik ?? '',
        jumlahSks: f.jumlahSks ?? f.jumlahSKS ?? student?.jumlahSks,
        ipk: f.ipk ?? student?.ipk,
        jenisMagang: f.jenisMagang,
        rencanaSkema: f.rencanaSkema ?? f.skemaMagang,
        topikTempat: f.topikTempat ?? f.topikMagang,
        output: f.output ?? f.outputTarget,
        catatanKhusus: f.catatanKhusus,
        submittedAt: f.submittedAt,
        // Field approval cuma muncul kalau Kaprodi sudah proses.
        approverName: f.approver?.name,
        approverNidn: f.approver?.nidn,
        approverRole: f.approver?.role,
        approvalDate: f.approver?.approvalDate,
        pdfFileName: f.pdfFileName,
        pdfSize: f.pdfSize,
        pdfPath: f.pdfPath,
    };

    return (
        <DashboardLayout
            pageTitle="Status Form Magang 01"
        >
            <p className="mb-6 text-sm text-primary">
                Surat Keterangan Memenuhi Syarat Akademik
            </p>
            <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-[1fr_420px]">
                <Form1SubmittedData formData={formData} />
                <Form1StatusPanel
                    form1_status={form1Submission?.status ?? status}
                    rejectionReason={form1Submission?.rejectionReason}
                    pdfPath={form1Submission?.pdfPath}
                    nim={student?.nim}
                    formData={formData}
                />
            </div>
        </DashboardLayout>
    );
}
