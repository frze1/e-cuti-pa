<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Permintaan dan Pemberian Cuti - {{ $cuti->user->name }}</title>
    <style>
        @page {
            margin: 0.5cm 1.2cm 0.4cm 1.2cm;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7.2pt;
            line-height: 1.1;
            color: #000;
        }
        .header-lampiran {
            float: right;
            width: 46%;
            text-align: left;
            font-size: 6.2pt;
            line-height: 1.05;
            margin-bottom: 4px;
        }
        .clear {
            clear: both;
        }
        .title-box {
            text-align: center;
            margin-bottom: 4px;
        }
        .title-main {
            font-weight: bold;
            font-size: 8.2pt;
            letter-spacing: 0.4px;
        }
        .title-nomor {
            font-size: 7.5pt;
            margin-top: 1px;
        }
        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        table.form-table, table.form-table th, table.form-table td {
            border: 1px solid #000;
        }
        table.form-table td, table.form-table th {
            padding: 1.5px 3.5px;
            vertical-align: middle;
        }
        .section-head {
            font-weight: bold;
            font-size: 7.4pt;
            background-color: #ffffff;
            text-align: left;
            padding: 2px 3.5px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .check {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
        }
        .ttd-space {
            height: 36px;
        }
        .footnotes {
            font-size: 5.8pt;
            line-height: 1.1;
            margin-top: 2px;
        }
        .footnotes table {
            border-collapse: collapse;
            font-size: 5.8pt;
        }
        .footnotes td {
            padding: 0.2px 1.5px;
            vertical-align: top;
            border: none;
        }
    </style>
</head>
<body>

    <!-- KOP ATAS KANAN SESUAI PERATURAN BKN NO 24 TAHUN 2017 -->
    <div class="header-lampiran">
        ANAK LAMPIRAN 1.b<br>
        PERATURAN BADAN KEPEGAWAIAN NEGARA<br>
        REPUBLIK INDONESIA<br>
        NOMOR 24 TAHUN 2017<br>
        TENTANG<br>
        TATA CARA PEMBERIAN CUTI PEGAWAI NEGERI SIPIL<br><br>
        Sumber, {{ \Carbon\Carbon::parse($cuti->created_at)->isoFormat('D MMMM YYYY') }}<br><br>
        Kepada<br>
        Yth. Ketua Pengadilan Agama Sumber<br>
        di<br>
        &nbsp;&nbsp;&nbsp;&nbsp;Sumber
    </div>
    <div class="clear"></div>

    <!-- JUDUL FORMULIR & NOMOR SURAT -->
    <div class="title-box">
        <div class="title-main">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
        <div class="title-nomor">NOMOR : {{ $cuti->nomor_surat ?? '........................................................' }}</div>
    </div>

    <!-- I. DATA PEGAWAI -->
    <table class="form-table">
        <tr>
            <td colspan="4" class="section-head">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td width="14%">Nama</td>
            <td width="36%">{{ $cuti->user->name }}</td>
            <td width="15%">NIP</td>
            <td width="35%">{{ $cuti->user->pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $cuti->user->pegawai->jabatan ?? '-' }}</td>
            <td>Masa Kerja</td>
            <td>{{ $cuti->user->pegawai->masa_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3">Pengadilan Agama Sumber</td>
        </tr>
    </table>

    <!-- II. JENIS CUTI YANG DIAMBIL -->
    <table class="form-table">
        <tr>
            <td colspan="4" class="section-head">II. JENIS CUTI YANG DIAMBIL**</td>
        </tr>
        <tr>
            <td width="42%">1. Cuti Tahunan</td>
            <td width="8%" class="check">{{ $cuti->jenis_cuti == 'Cuti Tahunan' ? '&#8730;' : '' }}</td>
            <td width="42%">2. Cuti Besar</td>
            <td width="8%" class="check">{{ $cuti->jenis_cuti == 'Cuti Besar' ? '&#8730;' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Sakit' ? '&#8730;' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Melahirkan' ? '&#8730;' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Karena Alasan Penting' ? '&#8730;' : '' }}</td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti di Luar Tanggungan Negara' ? '&#8730;' : '' }}</td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <table class="form-table">
        <tr>
            <td class="section-head">III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td style="padding: 2.5px 3.5px;">{{ $cuti->alasan }}</td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <table class="form-table">
        <tr>
            <td colspan="7" class="section-head">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td width="9%">Selama</td>
            <td width="7%" class="text-center bold">{{ $cuti->durasi_hari }}</td>
            <td width="22%">
                @php $satuan = strtolower($cuti->satuan_cuti ?? 'hari'); @endphp
                ({!! $satuan === 'hari' ? 'hari' : '<del>hari</del>' !!}/{!! $satuan === 'bulan' ? 'bulan' : '<del>bulan</del>' !!}/{!! $satuan === 'tahun' ? 'tahun' : '<del>tahun</del>' !!})*
            </td>
            <td width="16%" class="text-center">mulai tanggal</td>
            <td width="20%" class="text-center">{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }}</td>
            <td width="6%" class="text-center">s/d</td>
            <td width="20%" class="text-center">{{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY') }}</td>
        </tr>
    </table>

    <!-- V. CATATAN CUTI -->
    @php
        $tahunSekarang = \Carbon\Carbon::parse($cuti->tgl_mulai)->year;
        $tahunN = $tahunSekarang;
        $tahunN1 = $tahunSekarang - 1;
        $tahunN2 = $tahunSekarang - 2;

        $sisaN2 = $cuti->user->pegawai->sisa_cuti_n_2 ?? 0;
        $sisaN1 = $cuti->user->pegawai->sisa_cuti_n_1 ?? 0;
        $sisaN  = $cuti->user->pegawai->sisa_cuti_n ?? 0;
    @endphp
    <table class="form-table">
        <tr>
            <td colspan="6" class="section-head">V. CATATAN CUTI***</td>
        </tr>
        <tr>
            <td colspan="3" width="37%" class="bold">1. Cuti Tahunan</td>
            <td rowspan="5" width="13%" class="text-center bold" style="vertical-align: middle;">
                Paraf<br>Petugas Cuti
            </td>
            <td width="42%">2. Cuti Besar</td>
            <td width="8%" class="text-center"></td>
        </tr>
        <tr>
            <td width="11%" class="text-center bold">Tahun</td>
            <td width="11%" class="text-center bold">Sisa</td>
            <td width="15%" class="text-center bold">Keterangan</td>
            <td>3. Cuti Sakit</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td class="text-center">{{ $tahunN2 }}</td>
            <td class="text-center">{{ $sisaN2 > 0 ? $sisaN2 . ' HARI' : '- HARI' }}</td>
            <td></td>
            <td>4. Cuti Melahirkan</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td class="text-center">{{ $tahunN1 }}</td>
            <td class="text-center">{{ $sisaN1 > 0 ? $sisaN1 . ' HARI' : '- HARI' }}</td>
            <td></td>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="text-center"></td>
        </tr>
        <tr>
            <td class="text-center">{{ $tahunN }}</td>
            <td class="text-center">{{ $sisaN }} HARI</td>
            <td class="text-center" style="font-size: 6.8pt;">
                @if($cuti->jenis_cuti == 'Cuti Tahunan')
                    - {{ $cuti->durasi_hari }} = {{ max(0, $sisaN - $cuti->durasi_hari) }} HARI
                @endif
            </td>
            <td>6. Cuti Di Luar Tanggungan Negara</td>
            <td class="text-center"></td>
        </tr>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
    <table class="form-table">
        <tr>
            <td colspan="3" class="section-head">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
        </tr>
        <tr>
            <td rowspan="2" width="55%" style="vertical-align: top; padding: 3px 4px;">
                {{ $cuti->alamat_selama_cuti }}
            </td>
            <td width="10%" class="bold text-center">TELP.</td>
            <td width="35%">{{ $cuti->no_telp }}</td>
        </tr>
        <tr>
            <td colspan="2" class="text-center" style="vertical-align: top; padding: 3px 5px;">
                Hormat Saya,
                <div class="ttd-space"></div>
                <span class="bold">{{ $cuti->user->name }}</span>
            </td>
        </tr>
    </table>

    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
    <table class="form-table">
        <tr>
            <td colspan="5" class="section-head">VII. PERTIMBANGAN ATASAN LANGSUNG**</td>
        </tr>
        <tr class="text-center bold" style="font-size: 7pt;">
            <td width="16%">DISETUJUI</td>
            <td width="16%">PERUBAHAN****</td>
            <td width="18%">DITANGGUHKAN****</td>
            <td width="18%">TIDAK DISETUJUI****</td>
            <td rowspan="2" width="32%" style="vertical-align: top; padding: 3px 5px; font-weight: normal;">
                {{ $cuti->atasan_penyetuju?->jabatan ?? $cuti->user->pegawai->atasan->jabatan ?? 'Panitera Muda' }}
                <div class="ttd-space"></div>
                <span class="bold">{{ $cuti->atasan_penyetuju?->name ?? $cuti->user->pegawai->atasan->user->name ?? '.......................................' }}</span>
                @php
                    $nipAtasan = $cuti->atasan_penyetuju?->nip ?? $cuti->user->pegawai?->atasan?->nip;
                @endphp
                @if($nipAtasan)
                    <br><span style="font-size: 6.5pt;">NIP. {{ $nipAtasan }}</span>
                @endif
            </td>
        </tr>
        <tr class="check">
            <td height="18">&#8730;</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI -->
    <table class="form-table">
        <tr>
            <td colspan="5" class="section-head">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td>
        </tr>
        <tr class="text-center bold" style="font-size: 7pt;">
            <td width="16%">DISETUJUI</td>
            <td width="16%">PERUBAHAN****</td>
            <td width="18%">DITANGGUHKAN****</td>
            <td width="18%">TIDAK DISETUJUI****</td>
            <td rowspan="2" width="32%" style="vertical-align: top; padding: 3px 5px; font-weight: normal;">
                {{ $cuti->ketua_pengesah?->jabatan ?? 'Ketua' }}
                <div class="ttd-space"></div>
                <span class="bold">{{ $cuti->ketua_pengesah?->name ?? $ketua->user->name ?? 'Ishak Lubis' }}</span>
                @php
                    $nipKetua = $cuti->ketua_pengesah?->nip ?? $ketua?->nip;
                @endphp
                @if($nipKetua)
                    <br><span style="font-size: 6.5pt;">NIP. {{ $nipKetua }}</span>
                @endif
            </td>
        </tr>
        <tr class="check">
            <td height="18">&#8730;</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- CATATAN KAKI -->
    <div class="footnotes">
        <table>
            <tr><td colspan="2" class="bold">Catatan :</td></tr>
            <tr>
                <td width="25">*</td>
                <td>Coret yang tidak perlu</td>
            </tr>
            <tr>
                <td>**</td>
                <td>Pilih salah satu dengan memberi tanda centang (&#8730;)</td>
            </tr>
            <tr>
                <td>***</td>
                <td>diisi oleh Pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti</td>
            </tr>
            <tr>
                <td>****</td>
                <td>diberi tanda centang (&#8730;) dan alasannya</td>
            </tr>
        </table>
    </div>

</body>
</html>
