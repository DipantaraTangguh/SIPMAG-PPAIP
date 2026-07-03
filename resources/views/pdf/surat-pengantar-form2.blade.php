<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Magang — {{ $student->nim }}</title>
    <style>
        @page {
            margin: 2.5cm 1cm 2.2cm 1cm;
        }

        body {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
        }

        /* Kop surat (letterhead) — fixed top, lebar asli ~5.88cm */
        .letterhead {
            position: fixed;
            top: -1.7cm;
            left: 0;
        }
        .letterhead img {
            width: 5.88cm;
            height: auto;
            display: block;
        }

        /* Footer (alamat + akreditasi) — fixed bottom, lebar asli ~17.85cm */
        .footer {
            position: fixed;
            bottom: -1.7cm;
            left: 0;
        }
        .footer img {
            width: 17.85cm;
            height: auto;
            display: block;
        }

        /* Blok isi diindent ~1.5cm dari margin (sesuai docx left 850 twip) */
        .content {
            padding-left: 1.5cm;
        }

        p { margin: 0 0 6pt 0; }
        .justify { text-align: justify; }

        table.meta { border-collapse: collapse; margin-bottom: 12pt; }
        table.meta td { vertical-align: top; padding: 0; font-size: 11pt; }
        table.meta td.label { width: 1.9cm; }

        .recipient { margin-bottom: 12pt; line-height: 1.4; }
        .recipient div { margin: 0; }

        table.bio { border-collapse: collapse; margin: 4pt 0 10pt 1.4cm; }
        table.bio td { vertical-align: top; padding: 0; font-size: 11pt; }
        table.bio td.label { width: 2.3cm; }
        table.bio td.sep { width: 0.3cm; }

        /* Blok penutup — kolom kiri sempit, 12pt */
        .closing {
            margin-top: 10pt;
            width: 10.5cm;
            font-size: 12pt;
            line-height: 1.35;
        }
        .closing p { margin: 0; }
        .sig-gap { height: 38pt; }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @if ($letterheadSrc)
        <div class="letterhead"><img src="{{ $letterheadSrc }}" alt="Universitas Bakrie"></div>
    @endif
    @if ($footerSrc)
        <div class="footer"><img src="{{ $footerSrc }}" alt="Universitas Bakrie"></div>
    @endif

    <div class="content">
        <table class="meta">
            <tr><td class="label">No</td><td>: {{ $letterNumber }}</td></tr>
            <tr><td class="label">Lampiran</td><td>: -</td></tr>
            <tr><td class="label">Hal</td><td>: Permohonan Magang/Kerja Praktek</td></tr>
        </table>

        <div class="recipient">
            <div>Kepada Yth.</div>
            <div>{{ $submission->company_name }}</div>
            <div>{{ $submission->nama_pimpinan }} {{ $submission->jabatan_pimpinan }}</div>
            <div>{{ $submission->alamat_perusahaan }}</div>
        </div>

        <p>Dengan hormat,</p>

        <p class="justify">Salah satu program yang wajib dilaksanakan dalam menempuh pendidikan di
        Universitas Bakrie adalah kegiatan Magang/Kerja Praktek yang dilaksanakan oleh mahasiswa.</p>

        <p class="justify">Berdasarkan hal tersebut, kami sangat mengharapkan bantuan Bapak/Ibu dalam
        penyediaan tempat Magang/Kerja Praktek bagi mahasiswa/i kami {{ $durasiMagang }} penuh,
        terhitung dari tanggal {{ $bulanMulai }} &ndash; {{ $bulanSelesai }}. Adapun mahasiswa yang
        akan melaksanakan Magang/Kerja Praktek tersebut adalah sebagai berikut :</p>

        <table class="bio">
            <tr><td class="label">Nama</td><td class="sep">:</td><td>{{ $student->name }}</td></tr>
            <tr><td class="label">NIM</td><td class="sep">:</td><td>{{ $student->nim }}</td></tr>
            <tr><td class="label">Jurusan</td><td class="sep">:</td><td>{{ $student->study_program }}</td></tr>
        </table>

        <p class="justify">Apabila permohonan Magang/Kerja Praktek ini disetujui, kami mohon Bapak/Ibu
        dapat menghubungi mahasiswa yang bersangkutan atau Staf UPT Pusat Pengembangan Akademik dan
        Inovasi Pembelajaran (PPAIP) Up : Ibu Arin Septiarin Hp : 085215931415 / email :
        arin.septiarin@bakrie.ac.id / inovasi.pembelajaran@bakrie.ac.id</p>

        <p class="justify">Demikian permohonan ini kami buat, jika ada hal yang perlu dikomunikasikan
        kami siap membantu, terima kasih atas perhatian dan kesediaannya.</p>

        <div class="closing">
            <p>Jakarta, {{ $tanggalSurat }}</p>
            <p>Hormat Kami,</p>
            @if ($signatureSrc)
                <img src="{{ $signatureSrc }}" alt="Tanda tangan" style="display:block;height:56pt;margin:2pt 0;">
            @else
                <div class="sig-gap"></div>
            @endif
            <p class="sig-name">{{ $signatoryName }}</p>
            <p>{{ $signatoryRole }}</p>
        </div>
    </div>
</body>
</html>
