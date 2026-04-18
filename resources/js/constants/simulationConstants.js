/**
 * constants/simulationConstants.js
 * Mock data constants used across simulation panel and context actions.
 * Centralizes all dummy data in one place.
 */
export const MOCK_DPM = {
    initials: 'RH',
    name: 'Dita Nurmadewi, S.Kom.',
    nidn: '0412058801',
    email: 'dita.numadewi@bakrie.ac.id',
    whatsappGroupLink: '#',
};

export const MOCK_SIDANG_SCHEDULE = {
    tanggal: 'Selasa, 25 Juli 2024',
    waktu: '10:00 - 11:30 WIB',
    ruangan: 'Ruang Garuda (Bakrie Tower Lt. 42)',
    dosenPenguji1: 'Dr. Ahmad Fauzi, M.T.',
    dosenPenguji2: 'Siti Aminah, M.Kom.',
    panduanLink: '#',
};

export const MOCK_LOGBOOK_ACTIVITIES = [
    { title: 'Analisis kebutuhan sistem', hasil: 'Dokumen analisis kebutuhan' },
    { title: 'Perancangan database ERD', hasil: 'Diagram ERD selesai' },
    { title: 'Implementasi fitur autentikasi', hasil: 'Kode fitur login' },
    { title: 'Pengujian unit testing', hasil: 'Laporan test coverage 80%' },
    { title: 'Code review bersama tim', hasil: 'Pull request disetujui' },
    { title: 'Presentasi progress magang', hasil: 'Slide presentasi minggu ke-8' },
];

export const MOCK_LOGBOOK_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Senin'];

export const MOCK_LOGBOOK_DATES = ['15 Jun 2024', '16 Jun 2024', '17 Jun 2024', '18 Jun 2024', '19 Jun 2024', '22 Jun 2024'];

export const MOCK_FORM1_APPROVER = {
    name: 'Prof. Siti Wati',
    nidn: '0422117502',
    role: 'Kaprodi Informatika',
    approvalDate: '10/03/2026',
};

export const MOCK_FORM1_REJECTION_REASON =
    'IPK yang dimasukkan (3.84) tidak sesuai dengan data transkrip resmi. Mohon periksa kembali IPK Anda dan ajukan ulang dengan data yang benar.';

export const MOCK_FORM2_REJECTION_REASON =
    'Data alamat perusahaan tidak lengkap. Mohon cantumkan gedung dan nomor lantai yang spesifik. Silahkan ajukan form baru untuk merevisi.';

export const STATUS_BADGE_COLORS = {
    Unverified: 'bg-gray-600',
    PendingReview: 'bg-amber-500',
    ApprovedForm1: 'bg-green-600',
    RejectedForm1: 'bg-red-600',
    HasApplication: 'bg-blue-500',
    HasDPM: 'bg-purple-500',
    LogbookComplete: 'bg-emerald-500',
    MenungguSidang: 'bg-orange-500',
    SiklusSelesai: 'bg-emerald-600',
};
