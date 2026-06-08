export function mapStudent(user) {
    if (!user || !user.student) return null;

    const s = user.student;

    return {
        id: s.id,
        name: s.name,
        nim: s.nim,
        programStudi: s.study_program,
        email: s.email,
        semester: s.semester,
        tahunAkademik: s.tahun_akademik,
        jumlahSks: s.jumlah_sks,
        ipk: s.ipk,
        accessStatus: s.access_status,
        approvedLogbookCount: s.approved_logbook_count,
        dpm: s.dpm
            ? {
                name: s.dpm.name,
                nidn: s.dpm.nidn,
                email: s.dpm.contact,
                initials: s.dpm.name
                    .split(' ')
                    .map((w) => w[0])
                    .join('')
                    .slice(0, 2)
                    .toUpperCase(),
            }
            : null,
    };
}

export function mapForm2Submission(s) {
    if (!s) return s;

    const statusMap = {
        PendingReview: 'Menunggu Review',
        ApprovedForm2: 'Disetujui',
        RejectedForm2: 'Ditolak',
    };
    const submittedAt = s.submitted_at
        ? new Date(s.submitted_at).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        })
        : null;

    return {
        id: s.id,
        companyName: s.company_name,
        position: s.lingkup_magang,
        alamatPerusahaan: s.alamat_perusahaan,
        tanggalMulai: s.tanggal_mulai,
        tanggalSelesai: s.tanggal_selesai,
        status: statusMap[s.status] || s.status,
        submittedAt,
        rejectionReason: s.rejection_reason,
        pdfPath: s.pdf_path,
    };
}
