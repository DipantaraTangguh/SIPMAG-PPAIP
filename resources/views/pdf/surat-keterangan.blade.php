<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Memenuhi Syarat Akademik — {{ $student->nim }}</title>
    <style>
        @page { margin: 1.4cm 2cm 1.2cm 2cm; }

        body {
            font-family: 'Tahoma', 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.2;
            margin: 0;
        }
        .doc-code {
            position: fixed;
            bottom: -0.8cm;
            right: 0;
            font-size: 8pt;
            color: #555;
        }

        .letterhead {
            margin-bottom: 8pt;
        }
        .letterhead img {
            height: 30pt;
            display: block;
        }

        h1.title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 8pt 0;
        }

        h2.section-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 10pt 0 5pt 0;
        }

        p { margin: 0 0 3pt 0; }
        .tight p { margin: 0; }
        .justify p { text-align: justify; margin: 0 0 6pt 0; }

        table.fields {
            width: 100%;
            border-collapse: collapse;
            margin: 3pt 0 6pt 0;
        }
        table.fields td {
            vertical-align: top;
            padding: 0.5pt 0;
            font-size: 9pt;
        }
        table.fields td.label {
            width: 43%;
            padding-left: 20pt;
        }
        table.fields td.value { width: 57%; }
        table.fields td.value::before { content: ":  "; }

        .verif-subtitle {
            font-weight: bold;
            margin: 0 0 4pt 0;
        }
        .sig-student {
            margin-top: 8pt;
        }
        .sig-student p { margin: 0; }
        .sig-kaprodi {
            margin-top: 8pt;
            padding-left: 28pt;
        }
        .sig-kaprodi p { margin: 0; }

        .sig-space { height: 46pt; }
        .sig-img {
            display: block;
            height: 40pt;
            margin: 2pt 0;
        }
        .sig-name {
            margin-top: 2pt;
        }
    </style>
</head>
<body>
    <div class="doc-code">F-EDU-PRC-01-03.01/r0</div>

    @if ($logoSrc)
        <div class="letterhead">
            <img src="{{ $logoSrc }}" alt="Universitas Bakrie">
        </div>
    @endif

    <h1 class="title">SURAT KETERANGAN MEMENUHI SYARAT AKADEMIK</h1>

    <div class="tight">
        <p>Kepada Yth.</p>
        <p>Bapak/Ibu {{ $kaprodiName }}</p>
        <p>Ketua Program Studi {{ $student->study_program }}</p>
        <p>Universitas Bakrie</p>
    </div>

    <p style="margin-top: 6pt;">Dengan hormat,</p>
    <p>Saya yang bertandatangan di bawah ini:</p>

    <table class="fields">
        <tr>
            <td class="label">Nama</td>
            <td class="value">{{ $student->name }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="value">{{ $student->nim }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td class="value">{{ $student->study_program }}</td>
        </tr>
        <tr>
            <td class="label">Semester / Tahun Akademik</td>
            <td class="value">{{ $form1['semester'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah SKS yang telah diselesaikan</td>
            <td class="value">{{ $form1['jumlahSKS'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">IPK</td>
            <td class="value">{{ $form1['ipk'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Rencana skema magang</td>
            <td class="value">{{ $form1['skemaMagang'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Topik / Tempat Magang</td>
            <td class="value">{{ $form1['topikMagang'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Output</td>
            <td class="value">{{ $form1['outputTarget'] ?? '—' }}</td>
        </tr>
    </table>

    <div class="justify">
        <p>Menyatakan bahwa sampai dengan akhir semester tersebut, saya telah memenuhi syarat akademik
        pelaksanaan magang sebagaimana tercantum dalam Lampiran Transkrip Resmi. Berdasarkan hal tersebut,
        saya bermaksud untuk mengajukan diri sebagai peserta kegiatan Magang.</p>

        <p>Demikian permohonan ini saya buat dengan sebenar-benarnya. Apabila di kemudian hari terbukti bahwa
        saya tidak jujur dalam memberikan pernyataan, saya bersedia ditindak sesuai ketentuan akademik yang berlaku.</p>
    </div>

    <div class="sig-student">
        <p>Jakarta, {{ $submittedDate }}</p>
        <p>Hormat saya,</p>
        @if ($studentSignatureSrc)
            <img class="sig-img" src="{{ $studentSignatureSrc }}" alt="Tanda tangan mahasiswa">
        @else
            <div class="sig-space"></div>
        @endif
        <p class="sig-name">{{ $student->name }}</p>
        <p>NIM {{ $student->nim }}</p>
    </div>

    <h2 class="section-title">VERIFIKASI DAN PERSETUJUAN</h2>

    <p class="verif-subtitle">Verifikasi Akademik oleh Ketua Program Studi dan Dosen Pembimbing yang Ditunjuk</p>

    <p>Saya yang bertandatangan di bawah ini menyatakan bahwa mahasiswa tersebut di atas memenuhi syarat
    akademik untuk mengikuti kegiatan Magang.</p>

    <div class="sig-kaprodi">
        <p>Jakarta, {{ $approvalDate }}</p>
        <p>Ketua Program Studi {{ $student->study_program }}</p>
        @if ($signatureSrc)
            <img class="sig-img" src="{{ $signatureSrc }}" alt="Tanda tangan">
        @else
            <div class="sig-space"></div>
        @endif
        <p class="sig-name">{{ $kaprodiName }}</p>
        <p>NIDN {{ $kaprodiNidn }}</p>
    </div>
</body>
</html>
