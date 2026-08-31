import React from 'react';
import { BookOpen } from 'lucide-react';

/**
 * Panduan tahap berikutnya, dipakai di dua keadaan halaman Bimbingan:
 * saat formulir pengajuan pembimbing masih diisi, dan saat pengajuannya
 * sudah dikirim dan menunggu Kaprodi.
 *
 * Sebelumnya kedua tempat itu memuat kartu "Tips Mahasiswa" yang isinya
 * mengingatkan memperbarui CV sebelum melamar mitra -- nasihat untuk halaman
 * portal, bukan untuk halaman ini, dan mahasiswa yang sampai di sini justru
 * sudah lewat tahap melamar. Isinya diganti dengan aturan logbook yang
 * memang berlaku setelah DPM ditunjuk, karena angka-angkanya tidak
 * disebutkan di mana pun sampai mahasiswa membuka tab Logbook.
 */
const LOGBOOK_RULES = [
    {
        judul: 'Isi setiap hari kerja',
        isi: 'Satu entri untuk satu tanggal, di dalam rentang periode magang. Tanggal yang belum terjadi tidak bisa diisi.',
    },
    {
        judul: 'DPM meninjau tiap akhir pekan',
        isi: 'Entri bisa disetujui atau ditolak disertai catatan. Entri yang ditolak tidak disunting -- kirim entri baru untuk tanggal yang sama sesuai catatan DPM.',
    },
    {
        judul: 'Enam entri disetujui',
        isi: 'Setelah enam entri Anda disetujui DPM, menu Sidang Magang terbuka.',
    },
];

export default function LogbookGuideCard() {
    return (
        <div className="mt-4 rounded-xl border border-gray-200 bg-white p-5">
            <div className="flex items-center gap-2 border-b-2 border-primary/10 pb-4">
                <BookOpen className="h-[18px] w-[18px] text-primary" />
                <h3 className="text-[14px] font-bold text-[#1A1A1A]">
                    Panduan Bimbingan &amp; Logbook
                </h3>
            </div>
            <div className="mt-4 flex flex-col gap-3.5">
                {LOGBOOK_RULES.map((aturan) => (
                    <div key={aturan.judul}>
                        <p className="text-[13px] font-bold text-[#1A1A1A]">
                            {aturan.judul}
                        </p>
                        <p className="mt-0.5 text-[12px] leading-relaxed text-gray-500">
                            {aturan.isi}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );
}
