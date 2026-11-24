<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:w='urn:schemas-microsoft-com:office:word'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset="utf-8">
    <title>Formulir Cuti - {{ $cuti->user->name }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 595.3pt 841.9pt; /* A4 */
            margin: 28.35pt 42.5pt 28.35pt 42.5pt;
            mso-header-margin: 18pt;
            mso-footer-margin: 18pt;
            mso-paper-source: 0;
        }
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: Arial, 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            line-height: 1.15;
            color: #000000;
        }
        .header-lampiran {
            margin-left: 50%;
            font-size: 7pt;
            line-height: 1.1;
            margin-bottom: 6px;
        }
        .title-box {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .title-main {
            font-weight: bold;
            font-size: 9.5pt;
        }
        .title-nomor {
            font-size: 8.5pt;
            margin-top: 2px;
        }
        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border: 1pt solid #000000;
        }
        table.form-table td, table.form-table th {
            border: 1pt solid #000000;
            padding: 3pt 4pt;
            font-size: 8pt;
            vertical-align: middle;
        }
        .section-head {
            font-weight: bold;
            font-size: 8.5pt;
            background-color: #f2f2f2;
            text-align: left;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .check {
            font-weight: bold;
            font-size: 10pt;
            text-align: center;
        }
        .ttd-space {
            height: 45pt;
        }
        .footnotes {
            font-size: 6.5pt;
            line-height: 1.1;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="Section1">

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
            <td width="15%">Nama</td>
            <td width="35%">{{ $cuti->user->name }}</td>
            <td width="15%">NIP</td>
            <td width="35%">{{ $cuti->user->pegawai->nip ?? ($cuti->user->nip ?: '-') }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}</td>
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
            <td width="8%" class="check">{{ $cuti->jenis_cuti == 'Cuti Tahunan' ? 'V' : '' }}</td>
            <td width="42%">2. Cuti Besar</td>
            <td width="8%" class="check">{{ $cuti->jenis_cuti == 'Cuti Besar' ? 'V' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Sakit' ? 'V' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Melahirkan' ? 'V' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti Karena Alasan Penting' ? 'V' : '' }}</td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="check">{{ $cuti->jenis_cuti == 'Cuti di Luar Tanggungan Negara' ? 'V' : '' }}</td>
        </tr>
    </table>

    <!-- III. ALASAN CUTI -->
    <table class="form-table">
        <tr>
            <td class="section-head">III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td>{{ $cuti->alasan }}</td>
        </tr>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <table class="form-table">
        <tr>
            <td colspan="7" class="section-head">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td width="10%">Selama</td>
            <td width="8%" class="text-center bold">{{ $cuti->durasi_hari }}</td>
            <td width="22%">
                @php $satuan = strtolower($cuti->satuan_cuti ?? 'hari'); @endphp
                ({!! $satuan === 'hari' ? 'hari' : '<del>hari</del>' !!}/{!! $satuan === 'bulan' ? 'bulan' : '<del>bulan</del>' !!}/{!! $satuan === 'tahun' ? 'tahun' : '<del>tahun</del>' !!})*
            </td>
            <td width="15%" class="text-center">mulai tanggal</td>
            <td width="20%" class="text-center">{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }}</td>
            <td width="5%" class="text-center">s/d</td>
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
            <td class="text-center">
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
            <td rowspan="2" width="55%" style="vertical-align: top;">
                {{ $cuti->alamat_selama_cuti }}
            </td>
            <td width="10%" class="bold text-center">TELP.</td>
            <td width="35%">{{ $cuti->no_telp }}</td>
        </tr>
        <tr>
            <td colspan="2" class="text-center" style="vertical-align: top;">
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
        <tr class="text-center bold">
            <td width="16%">DISETUJUI</td>
            <td width="16%">PERUBAHAN****</td>
            <td width="18%">DITANGGUHKAN****</td>
            <td width="18%">TIDAK DISETUJUI****</td>
            <td rowspan="2" width="32%" style="vertical-align: top; font-weight: normal;">
                {{ $cuti->atasan_penyetuju?->jabatan ?? ($cuti->user->pegawai?->atasan?->jabatan ?? 'Atasan Langsung') }}
                <div class="ttd-space"></div>
                <span class="bold">{{ $cuti->atasan_penyetuju?->name ?? ($cuti->user->pegawai?->atasan?->user?->name ?? '.......................................') }}</span>
                @php
                    $nipAtasan = $cuti->atasan_penyetuju?->nip ?? $cuti->user->pegawai?->atasan?->nip;
                @endphp
                @if($nipAtasan)
                    <br><span>NIP. {{ $nipAtasan }}</span>
                @endif
            </td>
        </tr>
        <tr class="check">
            <td height="20">V</td>
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
        <tr class="text-center bold">
            <td width="16%">DISETUJUI</td>
            <td width="16%">PERUBAHAN****</td>
            <td width="18%">DITANGGUHKAN****</td>
            <td width="18%">TIDAK DISETUJUI****</td>
            <td rowspan="2" width="32%" style="vertical-align: top; font-weight: normal;">
                {{ $cuti->ketua_pengesah?->jabatan ?? 'Ketua Pengadilan Agama Sumber' }}
                <div class="ttd-space"></div>
                <span class="bold">{{ $cuti->ketua_pengesah?->name ?? ($ketua->user->name ?? 'Ketua PA') }}</span>
                @php
                    $nipKetua = $cuti->ketua_pengesah?->nip ?? $ketua?->nip;
                @endphp
                @if($nipKetua)
                    <br><span>NIP. {{ $nipKetua }}</span>
                @endif
            </td>
        </tr>
        <tr class="check">
            <td height="20">V</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- CATATAN KAKI -->
    <div class="footnotes">
        <p><b>Catatan :</b></p>
        <p>* Coret yang tidak perlu</p>
        <p>** Pilih salah satu dengan memberi tanda centang (V)</p>
        <p>*** Diisi oleh Pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti</p>
        <p>**** Diberi tanda centang (V) dan alasannya</p>
    </div>

</div>
</body>
</html>

