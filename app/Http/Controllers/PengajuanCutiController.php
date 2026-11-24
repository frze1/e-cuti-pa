<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use App\Models\Pegawai;
use App\Models\User;
use App\Notifications\CutiWebPushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajuanCutiController extends Controller
{
    // Menampilkan form HTML
    public function create()
    {
        $user = Auth::user();
        return view('cuti.create', compact('user'));
    }

    // Memproses data yang dikirim dari form
    public function store(Request $request)
    {
        // 1. Validasi data yang dikirim dari form
        // Fallback default satuan_cuti jika tidak disediakan di payload
        $request->merge([
            'satuan_cuti' => $request->input('satuan_cuti', 'hari'),
        ]);

        $request->validate([
            'jenis_cuti'         => 'required|string',
            'alasan'             => 'required|string',
            'tgl_mulai'          => 'required|date',
            'tgl_selesai'        => 'required|date|after_or_equal:tgl_mulai',
            'durasi_hari'        => 'required|integer|min:1',
            'satuan_cuti'        => 'required|in:hari,bulan,tahun',
            'alamat_selama_cuti' => 'required|string',
            'no_telp'            => 'required|string',
        ]);

        // Validasi: Durasi hari kerja tidak boleh melebihi rentang hari kalender pengajuan (jika satuan hari)
        if ($request->satuan_cuti === 'hari') {
            $rentangHari = Carbon::parse($request->tgl_mulai)->diffInDays(Carbon::parse($request->tgl_selesai)) + 1;
            if ($request->durasi_hari > $rentangHari) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi hari kerja (' . $request->durasi_hari . ' hari) tidak boleh melebihi rentang tanggal pengajuan (' . $rentangHari . ' hari).',
                ]);
            }
        }

        // Validasi Kuota: Khusus Cuti Tahunan dan satuan hari, durasi tidak boleh melebihi sisa kuota kumulatif (N + N-1 + N-2)
        $user = Auth::user();

        if ($request->jenis_cuti === 'Cuti Tahunan' && $request->satuan_cuti === 'hari') {
            $pegawai = $user->pegawai;
            $totalKuota = $pegawai ? ($pegawai->sisa_cuti_n + $pegawai->sisa_cuti_n_1 + $pegawai->sisa_cuti_n_2) : 0;
            if ($request->durasi_hari > $totalKuota) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi cuti tahunan (' . $request->durasi_hari . ' hari) melebihi total sisa cuti tahunan yang Anda miliki (' . $totalKuota . ' hari).',
                ]);
            }
        }

        // Cek hierarki kepegawaian: apakah pemohon merupakan bawahan langsung Ketua PA (atau tanpa atasan perantara)
        $atasanPegawai = $user->pegawai?->getAtasanPegawai();
        $isDirectUnderKetua = (!$atasanPegawai || $atasanPegawai->is_pimpinan_final);
        $initialStatus = $isDirectUnderKetua ? 'MENUNGGU_KETUA' : 'MENUNGGU_ATASAN';

        // 2. Simpan data ke tabel pengajuan_cutis
        $cutiBaru = \App\Models\PengajuanCuti::create([
            'user_id'            => Auth::id(), // Mengikat pengajuan ke pegawai yang login
            'jenis_cuti'         => $request->jenis_cuti,
            'alasan'             => $request->alasan,
            'tgl_mulai'          => $request->tgl_mulai,
            'tgl_selesai'        => $request->tgl_selesai,
            'durasi_hari'        => $request->durasi_hari,
            'satuan_cuti'        => $request->satuan_cuti,
            'alamat_selama_cuti' => $request->alamat_selama_cuti,
            'no_telp'            => $request->no_telp,
            'status'             => $initialStatus, 
        ]);

        // Kirim WebPush notification sesuai alur (langsung ke Ketua jika bawahan langsung, atau ke Atasan Langsung)
        try {
            if ($isDirectUnderKetua) {
                $ketuaUsers = User::all()->filter(function ($u) {
                    return $u->is_pimpinan_final || in_array($u->jabatan, ['Ketua', 'Ketua PA', 'Ketua Pengadilan Agama']);
                });
                foreach ($ketuaUsers as $ketua) {
                    $ketua->notify(new CutiWebPushNotification(
                        title: 'Permohonan Cuti Masuk (Pimpinan)',
                        body: "Aparatur {$user->name} ({$user->jabatan}) mengajukan {$request->jenis_cuti} ({$request->durasi_hari} hari). Menunggu pengesahan Ketua PA.",
                        actionUrl: '/cuti/keputusan-ketua',
                        tag: 'cuti-ketua-' . $cutiBaru->id
                    ));
                }
            } else {
                $atasanUser = $atasanPegawai?->user;
                if ($atasanUser) {
                    $atasanUser->notify(new CutiWebPushNotification(
                        title: 'Permohonan Cuti Baru Masuk',
                        body: "Aparatur {$user->name} mengajukan {$request->jenis_cuti} ({$request->durasi_hari} hari). Menunggu pertimbangan Anda.",
                        actionUrl: '/cuti/persetujuan',
                        tag: 'cuti-masuk-' . $cutiBaru->id
                    ));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification to atasan failed: ' . $e->getMessage());
        }

        // 3. Kembalikan ke halaman riwayat pengajuan dengan pesan sukses
        $pesanSukses = $isDirectUnderKetua 
            ? 'Pengajuan cuti berhasil dikirim! Menunggu keputusan dan pengesahan Ketua Pengadilan Agama.'
            : 'Pengajuan cuti berhasil dikirim! Menunggu pertimbangan atasan langsung.';

        return redirect()->route('cuti.riwayat')->with('success', $pesanSukses);
    }

    public function riwayat()
    {
        $riwayatCuti = \App\Models\PengajuanCuti::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cuti.riwayat', compact('riwayatCuti'));
    }

    /**
     * Tampilkan formulir perbaikan pengajuan cuti (Edit).
     * Hanya pemilik permohonan dengan status 'Revisi Atasan' yang berhak mengakses.
     */
    public function edit($id)
    {
        $cuti = $id instanceof PengajuanCuti ? $id : PengajuanCuti::findOrFail($id);

        // Pengecekan otorisasi: hanya user_id pemilik cuti yang bisa mengakses
        if ($cuti->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses permohonan cuti ini.');
        }

        // Pastikan status saat ini adalah 'Revisi Atasan'
        if (!in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN'])) {
            return redirect()->route('cuti.riwayat')->with('error', 'Hanya permohonan dengan status Revisi Atasan yang dapat diperbaiki.');
        }

        $user = Auth::user();
        return view('cuti.edit', compact('cuti', 'user'));
    }

    /**
     * Memproses pembaruan data pengajuan cuti yang telah diperbaiki.
     * Mengembalikan status ke 'Menunggu Atasan' dan mereset catatan atasan.
     */
    public function update(Request $request, $id)
    {
        $cuti = $id instanceof PengajuanCuti ? $id : PengajuanCuti::findOrFail($id);

        // Pengecekan otorisasi: hanya user_id pemilik cuti yang bisa mengakses
        if ($cuti->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak memperbarui permohonan cuti ini.');
        }

        // Pastikan status saat ini adalah 'Revisi Atasan'
        if (!in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN'])) {
            return redirect()->route('cuti.riwayat')->with('error', 'Permohonan ini tidak sedang dalam status Revisi Atasan.');
        }

        // Validasi input form (tanggal, alasan, dll)
        // Fallback default satuan_cuti jika tidak disediakan di payload
        $request->merge([
            'satuan_cuti' => $request->input('satuan_cuti', 'hari'),
        ]);

        $request->validate([
            'jenis_cuti'         => 'required|string',
            'alasan'             => 'required|string',
            'tgl_mulai'          => 'required|date',
            'tgl_selesai'        => 'required|date|after_or_equal:tgl_mulai',
            'durasi_hari'        => 'required|integer|min:1',
            'satuan_cuti'        => 'required|in:hari,bulan,tahun',
            'alamat_selama_cuti' => 'required|string',
            'no_telp'            => 'required|string',
        ]);

        // Validasi: Durasi hari kerja tidak boleh melebihi rentang hari kalender pengajuan (jika satuan hari)
        if ($request->satuan_cuti === 'hari') {
            $rentangHari = Carbon::parse($request->tgl_mulai)->diffInDays(Carbon::parse($request->tgl_selesai)) + 1;
            if ($request->durasi_hari > $rentangHari) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi hari kerja (' . $request->durasi_hari . ' hari) tidak boleh melebihi rentang tanggal pengajuan (' . $rentangHari . ' hari).',
                ]);
            }
        }

        // Validasi Kuota: Khusus Cuti Tahunan dan satuan hari, durasi tidak boleh melebihi sisa kuota kumulatif
        $user = Auth::user();

        if ($request->jenis_cuti === 'Cuti Tahunan' && $request->satuan_cuti === 'hari') {
            $pegawai = $user->pegawai;
            $totalKuota = $pegawai ? ($pegawai->sisa_cuti_n + $pegawai->sisa_cuti_n_1 + $pegawai->sisa_cuti_n_2) : 0;
            if ($request->durasi_hari > $totalKuota) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi cuti tahunan (' . $request->durasi_hari . ' hari) melebihi total sisa cuti tahunan yang Anda miliki (' . $totalKuota . ' hari).',
                ]);
            }
        }

        // Gunakan DB Transaction untuk keamanan
        DB::transaction(function () use ($request, $cuti) {
            $cuti->update([
                'jenis_cuti'         => $request->jenis_cuti,
                'alasan'             => $request->alasan,
                'tgl_mulai'          => $request->tgl_mulai,
                'tgl_selesai'        => $request->tgl_selesai,
                'durasi_hari'        => $request->durasi_hari,
                'satuan_cuti'        => $request->satuan_cuti,
                'alamat_selama_cuti' => $request->alamat_selama_cuti,
                'no_telp'            => $request->no_telp,
                'status'             => 'MENUNGGU_ATASAN', // Ubah status kembali menjadi 'Menunggu Atasan' (mereset siklus)
                'catatan_atasan'     => null, // Kosongkan kolom catatan_atasan (set null)
                'catatan_revisi'     => null,
            ]);
        });

        // Kirim WebPush notification ke Atasan Langsung bahwa berkas telah diperbaiki
        try {
            $atasanUser = $user->pegawai?->atasan?->user;
            if ($atasanUser) {
                $atasanUser->notify(new CutiWebPushNotification(
                    title: 'Perbaikan Pengajuan Cuti Masuk',
                    body: "Aparatur {$user->name} telah memperbaiki permohonan {$request->jenis_cuti} ({$request->durasi_hari} hari). Menunggu pertimbangan ulang Anda.",
                    actionUrl: '/cuti/persetujuan',
                    tag: 'cuti-revisi-' . $cuti->id
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification to atasan after revision failed: ' . $e->getMessage());
        }

        return redirect()->route('cuti.riwayat')->with('success', 'Permohonan cuti berhasil diperbaiki dan diajukan ulang ke Atasan Langsung.');
    }

    /**
     * Memproses penarikan/pembatalan permohonan cuti oleh pegawai.
     * Hanya mengubah status pengajuan menjadi 'Dibatalkan Pegawai' tanpa mengubah kuota,
     * karena kuota cuti belum pernah dipotong pada tahap awal (hanya dipotong saat disetujui Ketua PA).
     */
    public function tarik($id)
    {
        $cuti = $id instanceof PengajuanCuti ? $id : PengajuanCuti::findOrFail($id);

        // Pengecekan otorisasi: hanya user_id pemilik cuti yang bisa menarik
        if ($cuti->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak menarik permohonan cuti ini.');
        }

        // Pastikan status sedang 'Menunggu Atasan' atau 'Revisi Atasan'
        if (!in_array($cuti->status, ['Menunggu Atasan', 'MENUNGGU_ATASAN', 'Revisi Atasan', 'REVISI_ATASAN'])) {
            return redirect()->route('cuti.riwayat')->with('error', 'Permohonan cuti tidak dapat ditarik pada tahap status saat ini.');
        }

        // Ubah status menjadi 'Dibatalkan Pegawai'
        $cuti->status = 'Dibatalkan Pegawai';
        $cuti->save();

        // Kirim notifikasi ke Atasan Langsung bahwa permohonan telah ditarik
        try {
            $atasanUser = $cuti->user->pegawai?->atasan?->user;
            if ($atasanUser) {
                $atasanUser->notify(new CutiWebPushNotification(
                    title: 'Permohonan Cuti Ditarik',
                    body: "Aparatur {$cuti->user->name} telah menarik/membatalkan permohonan {$cuti->jenis_cuti}.",
                    actionUrl: '/cuti/persetujuan',
                    tag: 'cuti-batal-' . $cuti->id,
                    type: 'danger'
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification to atasan after cancellation failed: ' . $e->getMessage());
        }

        return redirect()->route('cuti.riwayat')->with('success', 'Permohonan cuti berhasil ditarik/dibatalkan.');
    }

    public function persetujuanAtasan()
    {
        if (!Auth::user()->is_atasan_langsung) {
            abort(403, 'Akses terbatas untuk Atasan Langsung.');
        }

        $myJabatanId = Auth::user()->pegawai?->jabatan_id;
        
        // Ambil data cuti dari bawahan yang masih menunggu persetujuan berdasarkan pohon hierarki jabatan
        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan'])
            ->whereHas('user.pegawai.jabatan', function($query) use ($myJabatanId) {
                $query->where('parent_id', $myJabatanId);
            })
            ->where('status', 'MENUNGGU_ATASAN')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cuti.persetujuan', compact('daftarPengajuan'));
    }

    public function tinjau($id)
    {
        if (!Auth::user()->is_atasan_langsung) {
            abort(403, 'Akses terbatas untuk Atasan Langsung.');
        }

        // Ambil data cuti beserta relasi usernya
        $cuti = \App\Models\PengajuanCuti::with('user.pegawai')->findOrFail($id);
        
        // Proteksi 1: Pastikan status berkas masih MENUNGGU_ATASAN
        if ($cuti->status !== 'MENUNGGU_ATASAN') {
            return redirect()->route('cuti.persetujuan')->with('info', 'Berkas cuti ini sudah tidak lagi dalam status menunggu pertimbangan Anda.');
        }

        // Proteksi 2: Pastikan atasan yang mengakses benar-benar atasan dari pegawai ini
        $atasanPegawai = $cuti->user->pegawai?->getAtasanPegawai();
        if (!$atasanPegawai || $atasanPegawai->id !== Auth::user()->pegawai?->id) {
            abort(403, 'Anda tidak memiliki akses ke berkas ini.');
        }

        return view('cuti.tinjau', compact('cuti'));
    }

    public function prosesPersetujuan(Request $request, $id)
    {
        if (!Auth::user()->is_atasan_langsung) {
            abort(403, 'Akses terbatas untuk Atasan Langsung.');
        }

        $request->validate([
            'keputusan' => 'required|in:DISETUJUI,PERUBAHAN,DITANGGUHKAN,DITOLAK',
            'catatan'   => 'nullable|string',
        ]);

        $cuti = \App\Models\PengajuanCuti::with('user.pegawai')->findOrFail($id);

        // Proteksi Hak Akses: Pastikan atasan yang mengakses benar-benar atasan dari pegawai ini
        $atasanPegawai = $cuti->user->pegawai?->getAtasanPegawai();
        if (!$atasanPegawai || $atasanPegawai->id !== Auth::user()->pegawai?->id) {
            abort(403, 'Anda tidak memiliki hak memberikan pertimbangan pada berkas ini.');
        }

        // Proteksi Status: Harus berstatus MENUNGGU_ATASAN
        if ($cuti->status !== 'MENUNGGU_ATASAN') {
            abort(400, 'Berkas cuti ini sudah tidak dalam status menunggu pertimbangan atasan.');
        }

        // Ubah status berdasarkan keputusan atasan (sesuai standar formulir BKN)
        switch ($request->keputusan) {
            case 'DISETUJUI':
                $cuti->status = 'MENUNGGU_KETUA';
                $cuti->disetujui_oleh_atasan_id = Auth::id();
                break;
            case 'PERUBAHAN':
                $cuti->status = 'REVISI_ATASAN';
                break;
            case 'DITANGGUHKAN':
                $cuti->status = 'DITANGGUHKAN_ATASAN';
                break;
            case 'DITOLAK':
                $cuti->status = 'DITOLAK';
                break;
        }

        $cuti->catatan_revisi = $request->catatan;
        $cuti->save();

        // Kirim WebPush notification ke pemohon dan Ketua PA
        try {
            $pemohon = $cuti->user;
            if ($pemohon) {
                $pesanPemohon = match ($request->keputusan) {
                    'DISETUJUI' => "Permohonan cuti Anda telah disetujui Atasan Langsung dan diteruskan ke Ketua PA untuk pengesahan final.",
                    'PERUBAHAN' => "Atasan Langsung meminta perubahan pada berkas cuti Anda: " . ($request->catatan ?: '-'),
                    'DITANGGUHKAN' => "Permohonan cuti Anda ditangguhkan oleh Atasan Langsung. Catatan: " . ($request->catatan ?: '-'),
                    'DITOLAK' => "Permohonan cuti Anda tidak disetujui oleh Atasan Langsung. Catatan: " . ($request->catatan ?: '-'),
                    default => "Status cuti Anda: {$request->keputusan}",
                };

                $pemohon->notify(new CutiWebPushNotification(
                    title: 'Pertimbangan Cuti: ' . $request->keputusan,
                    body: $pesanPemohon,
                    actionUrl: '/cuti/riwayat',
                    tag: 'cuti-status-' . $cuti->id
                ));
            }

            if ($request->keputusan === 'DISETUJUI') {
                $ketuaUsers = User::all()->filter(function ($u) {
                    return $u->is_pimpinan_final || in_array($u->jabatan, ['Ketua PA', 'Ketua Pengadilan Agama']);
                });
                foreach ($ketuaUsers as $ketua) {
                    $ketua->notify(new CutiWebPushNotification(
                        title: 'Antrean Pengesahan Cuti',
                        body: "Berkas cuti a.n. {$cuti->user->name} telah disetujui Atasan Langsung dan menunggu keputusan Ketua PA.",
                        actionUrl: '/cuti/keputusan-ketua',
                        tag: 'cuti-ketua-' . $cuti->id
                    ));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification after atasan review failed: ' . $e->getMessage());
        }

        return redirect()->route('cuti.persetujuan')->with('success', 'Keputusan pertimbangan cuti berhasil disimpan dan diteruskan ke Ketua PA.');
    }

    /**
     * Menampilkan riwayat persetujuan cuti bawahan yang pernah diproses oleh Atasan Langsung
     */
    public function riwayatAtasan()
    {
        if (!Auth::user()->is_atasan_langsung) {
            abort(403, 'Akses terbatas untuk Atasan Langsung.');
        }

        $jabatanId = Auth::user()->pegawai?->jabatan_id;

        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan', 'ketua_pengesah.pegawai'])
            ->where(function ($q) use ($jabatanId) {
                if ($jabatanId) {
                    $q->whereHas('user.pegawai.jabatan', function ($sq) use ($jabatanId) {
                        $sq->where('parent_id', $jabatanId);
                    });
                }
                $q->orWhere('disetujui_oleh_atasan_id', Auth::id());
            })
            ->where('status', '!=', 'MENUNGGU_ATASAN')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cuti.riwayat-atasan', compact('daftarPengajuan'));
    }

    public function daftarPenomoran()
    {
        // Proteksi: Hanya Admin yang bisa akses
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin Kepegawaian yang berhak mengakses halaman ini.');
        }

        // Ambil data cuti yang statusnya menunggu nomor surat (setelah di-ACC Ketua)
        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan.parent', 'atasan_penyetuju.pegawai', 'ketua_pengesah.pegawai'])
            ->where('status', 'MENUNGGU_PENOMORAN')
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('cuti.penomoran', compact('daftarPengajuan'));
    }

    /**
     * Menampilkan riwayat surat cuti yang telah dinomori dan selesai (Arsip Admin)
     */
    public function riwayatPenomoran()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin Kepegawaian yang berhak mengakses halaman ini.');
        }

        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan', 'atasan_penyetuju.pegawai', 'ketua_pengesah.pegawai'])
            ->where('status', 'SELESAI_DISETUJUI')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cuti.riwayat-penomoran', compact('daftarPengajuan'));
    }

    public function inputNomor($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        
        $cuti = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan.parent', 'atasan_penyetuju.pegawai', 'ketua_pengesah.pegawai'])->findOrFail($id);

        if ($cuti->status !== 'MENUNGGU_PENOMORAN') {
            return redirect()->route('cuti.penomoran')->with('info', 'Berkas ini tidak sedang dalam status menunggu penomoran surat.');
        }
        
        // Buat rekomendasi format nomor dinas resmi dengan angka Romawi pada bulan
        $romawiMap = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $bulanAngka = (int) \Carbon\Carbon::now()->format('n');
        $bulanRomawi = $romawiMap[$bulanAngka] ?? 'I';
        $tahun = \Carbon\Carbon::now()->format('Y');
        $rekomendasiNomor = ".../KPA.W10-A4/KP5.3/$bulanRomawi/$tahun";

        return view('cuti.input-nomor', compact('cuti', 'rekomendasiNomor'));
    }

    public function simpanNomor(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'nomor_surat' => 'required|string|max:255|unique:pengajuan_cutis,nomor_surat,' . $id,
        ], [
            'nomor_surat.unique' => 'Nomor surat dinas tersebut sudah pernah digunakan pada berkas lain. Mohon periksa buku register surat.',
        ]);

        $cuti = \App\Models\PengajuanCuti::with('user')->findOrFail($id);

        if ($cuti->status !== 'MENUNGGU_PENOMORAN') {
            abort(400, 'Berkas cuti ini tidak sedang dalam status menunggu penomoran surat.');
        }
        
        // Simpan nomor dan ubah status menjadi SELESAI (Final)
        $cuti->nomor_surat = $request->nomor_surat;
        $cuti->status = 'SELESAI_DISETUJUI';
        $cuti->save();

        // Kirim WebPush notification ke Pemohon
        try {
            if ($cuti->user) {
                $cuti->user->notify(new CutiWebPushNotification(
                    title: 'Surat Cuti Resmi Diterbitkan',
                    body: "Surat cuti Anda nomor {$cuti->nomor_surat} telah resmi diterbitkan. Dokumen formulir cuti dapat diunduh sekarang.",
                    actionUrl: '/cuti/riwayat',
                    tag: 'cuti-final-' . $cuti->id
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification after penomoran failed: ' . $e->getMessage());
        }

        return redirect()->route('cuti.penomoran')->with('success', 'Nomor surat dinas berhasil disimpan. Proses pengajuan cuti telah SELESAI.');
    }

    public function daftarKetua()
    {
        // Proteksi: Hanya Ketua / Pimpinan Final (is_pimpinan_final & is_atasan) yang berhak mengakses
        if (!Auth::user()->is_atasan || !Auth::user()->is_pimpinan_final) {
            abort(403, 'Hanya Ketua Pengadilan Agama yang berhak mengakses halaman ini.');
        }

        // Ambil cuti yang menunggu keputusan Ketua, termasuk pengajuan bawahan langsung Ketua
        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan', 'atasan_penyetuju.pegawai'])
            ->where(function ($query) {
                $query->where('status', 'MENUNGGU_KETUA')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'MENUNGGU_ATASAN')
                            ->whereHas('user.pegawai.jabatan', function ($jq) {
                                $jq->whereHas('parent', function ($pq) {
                                    $pq->whereIn('nama_jabatan', ['Ketua', 'Ketua PA', 'Ketua Pengadilan Agama']);
                                });
                            });
                    });
            })
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('cuti.daftar-ketua', compact('daftarPengajuan'));
    }

    /**
     * Menampilkan riwayat keputusan dan pengesahan cuti oleh Ketua PA
     */
    public function riwayatKetua()
    {
        if (!Auth::user()->is_atasan || !Auth::user()->is_pimpinan_final) {
            abort(403, 'Hanya Ketua Pengadilan Agama yang berhak mengakses halaman ini.');
        }

        $daftarPengajuan = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan', 'atasan_penyetuju.pegawai'])
            ->where(function ($q) {
                $q->whereIn('status', ['MENUNGGU_PENOMORAN', 'SELESAI_DISETUJUI', 'DITOLAK', 'REVISI_KETUA', 'DITANGGUHKAN_KETUA'])
                  ->orWhere('disahkan_oleh_ketua_id', Auth::id());
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cuti.riwayat-ketua', compact('daftarPengajuan'));
    }

    public function tinjauKetua($id)
    {
        if (!Auth::user()->is_atasan || !Auth::user()->is_pimpinan_final) abort(403);

        $cuti = \App\Models\PengajuanCuti::with(['user.pegawai.jabatan.parent', 'atasan_penyetuju.pegawai'])->findOrFail($id);

        $isEligibleStatus = ($cuti->status === 'MENUNGGU_KETUA')
            || ($cuti->status === 'MENUNGGU_ATASAN' && ($cuti->user->pegawai?->getAtasanPegawai()?->is_pimpinan_final ?? false));

        if (!$isEligibleStatus) {
            return redirect()->route('cuti.daftar_ketua')->with('info', 'Berkas ini tidak sedang dalam status menunggu keputusan Ketua PA.');
        }
        
        return view('cuti.tinjau-ketua', compact('cuti'));
    }

    public function prosesKetua(Request $request, $id)
    {
        if (!Auth::user()->is_atasan || !Auth::user()->is_pimpinan_final) abort(403);

        $request->validate([
            'keputusan' => 'required|in:DISETUJUI,PERUBAHAN,DITANGGUHKAN,DITOLAK',
            'catatan'   => 'nullable|string',
        ]);

        // Jalankan dalam database transaction dengan pessimistic locking (lockForUpdate)
        // untuk menjamin konsistensi ACID, mencegah double-deduction dan race conditions
        DB::transaction(function () use ($request, $id) {
            $cuti = \App\Models\PengajuanCuti::lockForUpdate()->findOrFail($id);

            // Proteksi Status (Replay / Idempotency protection):
            $isEligibleStatus = ($cuti->status === 'MENUNGGU_KETUA')
                || ($cuti->status === 'MENUNGGU_ATASAN' && ($cuti->user->pegawai?->getAtasanPegawai()?->is_pimpinan_final ?? false));

            if (!$isEligibleStatus) {
                abort(400, 'Berkas cuti ini tidak sedang menunggu keputusan Ketua PA.');
            }

            switch ($request->keputusan) {
                case 'DISETUJUI':
                    // Khusus Cuti Tahunan dan satuan hari: Jalankan pemotongan kuota cuti dengan locking
                    if ($cuti->jenis_cuti === 'Cuti Tahunan' && ($cuti->satuan_cuti ?? 'hari') === 'hari') {
                        $pegawai = \App\Models\Pegawai::lockForUpdate()->where('user_id', $cuti->user_id)->first();
                        if (!$pegawai) {
                            abort(404, 'Data kepegawaian pemohon cuti tidak ditemukan.');
                        }

                        $durasi = $cuti->durasi_hari;
                        $totalKuota = $pegawai->sisa_cuti_n + $pegawai->sisa_cuti_n_1 + $pegawai->sisa_cuti_n_2;

                        if ($durasi > $totalKuota) {
                            abort(422, 'Sisa kuota cuti pemohon (' . $totalKuota . ' hari) tidak mencukupi untuk durasi pengajuan (' . $durasi . ' hari). Kuota tidak dapat dipotong.');
                        }

                        // Algoritma Pemotongan Saldo Cuti Cascading N -> N-1 -> N-2 tanpa sisa negatif
                        if ($pegawai->sisa_cuti_n >= $durasi) {
                            $pegawai->sisa_cuti_n -= $durasi;
                        } elseif (($pegawai->sisa_cuti_n + $pegawai->sisa_cuti_n_1) >= $durasi) {
                            $sisa_durasi = $durasi - $pegawai->sisa_cuti_n;
                            $pegawai->sisa_cuti_n = 0;
                            $pegawai->sisa_cuti_n_1 -= $sisa_durasi;
                        } else {
                            $sisa_durasi = $durasi - $pegawai->sisa_cuti_n - $pegawai->sisa_cuti_n_1;
                            $pegawai->sisa_cuti_n = 0;
                            $pegawai->sisa_cuti_n_1 = 0;
                            $pegawai->sisa_cuti_n_2 = max(0, $pegawai->sisa_cuti_n_2 - $sisa_durasi);
                        }
                        $pegawai->save();
                    }

                    // Jika pemohon bawahan langsung (belum ada atasan penyetuju), catat Ketua sebagai atasan langsung sekaligus pengesah
                    if (!$cuti->disetujui_oleh_atasan_id) {
                        $cuti->disetujui_oleh_atasan_id = Auth::id();
                    }

                    // Berkas cuti disahkan Ketua PA dan diteruskan ke Admin untuk penomoran surat
                    $cuti->status = 'MENUNGGU_PENOMORAN';
                    $cuti->disahkan_oleh_ketua_id = Auth::id();
                    break;
                    
                case 'PERUBAHAN':
                    $cuti->status = 'REVISI_KETUA';
                    break;
                case 'DITANGGUHKAN':
                    $cuti->status = 'DITANGGUHKAN_KETUA';
                    break;
                case 'DITOLAK':
                    $cuti->status = 'DITOLAK';
                    break;
            }

            $cuti->catatan_revisi = $request->catatan;
            $cuti->save();
        });

        // Kirim WebPush notification ke pemohon dan admin kepegawaian
        try {
            $cutiFresh = PengajuanCuti::with('user')->find($id);
            if ($cutiFresh && $cutiFresh->user) {
                $pesan = match ($request->keputusan) {
                    'DISETUJUI' => "Permohonan cuti Anda telah disahkan Ketua Pengadilan Agama Sumber dan diteruskan ke Admin untuk penomoran surat dinas.",
                    'PERUBAHAN' => "Ketua PA memberikan catatan revisi pada permohonan cuti Anda: " . ($request->catatan ?: '-'),
                    'DITANGGUHKAN' => "Permohonan cuti Anda ditangguhkan oleh Ketua PA. Catatan: " . ($request->catatan ?: '-'),
                    'DITOLAK' => "Permohonan cuti Anda tidak disetujui oleh Ketua PA. Catatan: " . ($request->catatan ?: '-'),
                    default => "Status akhir cuti: {$request->keputusan}",
                };

                $cutiFresh->user->notify(new CutiWebPushNotification(
                    title: $request->keputusan === 'DISETUJUI' ? 'Cuti Disahkan Ketua PA' : 'Keputusan Ketua PA: ' . $request->keputusan,
                    body: $pesan,
                    actionUrl: '/cuti/riwayat',
                    tag: 'cuti-final-' . $cutiFresh->id
                ));
            }

            if ($request->keputusan === 'DISETUJUI') {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new CutiWebPushNotification(
                        title: 'Antrean Penomoran Surat Cuti',
                        body: "Berkas cuti a.n. {$cutiFresh->user->name} telah disahkan Ketua PA. Silakan input nomor surat dinas.",
                        actionUrl: '/cuti/penomoran',
                        tag: 'cuti-nomor-' . $cutiFresh->id
                    ));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification after ketua decision failed: ' . $e->getMessage());
        }

        return redirect()->route('cuti.daftar_ketua')->with('success', 'Keputusan final berhasil disimpan dan diteruskan ke Admin Kepegawaian untuk penomoran surat.');
    }

    public function cetakPdf($id)
    {
        // Ambil data cuti beserta relasi berantai dan snapshot pejabat penyetuju & pengesah
        $cuti = \App\Models\PengajuanCuti::with([
            'user.pegawai.jabatan.parent',
            'atasan_penyetuju.pegawai',
            'ketua_pengesah.pegawai',
        ])->findOrFail($id);

        // Proteksi 1: Pastikan dokumen sudah selesai ACC final dan bernomor surat
        if (!in_array($cuti->status, ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])) {
            abort(403, 'Dokumen belum selesai diproses atau belum diterbitkan nomor surat resminya.');
        }

        // Proteksi 2: Hanya pemohon asli atau Admin Kepegawaian yang boleh mencetak
        if (Auth::user()->role !== 'admin' && Auth::id() !== $cuti->user_id) {
            abort(403, 'Anda tidak berhak mengakses dokumen ini.');
        }

        $ketua = \App\Models\Pegawai::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'like', '%Ketua%')
              ->where('nama_jabatan', 'not like', '%Wakil%');
        })->with(['user', 'jabatan'])->first();

        // Render tampilan Blade menjadi PDF
        $pdf = Pdf::loadView('cuti.cetak-pdf', compact('cuti', 'ketua'));
        
        // Atur ukuran kertas ke A4 (Portrait)
        $pdf->setPaper('A4', 'portrait');

        // Gunakan stream() untuk preview di browser, atau download() untuk langsung unduh
        return $pdf->stream('Formulir_Cuti_' . str_replace(' ', '_', $cuti->user->name) . '.pdf');
    }

    /**
     * Mengunduh dokumen formulir cuti dalam format Word (.doc) yang editable
     */
    public function downloadWord($id)
    {
        $cuti = \App\Models\PengajuanCuti::with([
            'user.pegawai.jabatan.parent',
            'atasan_penyetuju.pegawai',
            'ketua_pengesah.pegawai',
        ])->findOrFail($id);

        // Proteksi 1: Pastikan dokumen sudah selesai ACC final dan bernomor surat
        if (!in_array($cuti->status, ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])) {
            abort(403, 'Dokumen belum selesai diproses atau belum diterbitkan nomor surat resminya.');
        }

        // Proteksi 2: Hanya pemohon asli atau Admin Kepegawaian yang boleh mengunduh
        if (Auth::user()->role !== 'admin' && Auth::id() !== $cuti->user_id) {
            abort(403, 'Anda tidak berhak mengakses dokumen ini.');
        }

        $ketua = \App\Models\Pegawai::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'like', '%Ketua%')
              ->where('nama_jabatan', 'not like', '%Wakil%');
        })->with(['user', 'jabatan'])->first();

        $htmlContent = view('cuti.cetak-doc', compact('cuti', 'ketua'))->render();
        $filename = 'Formulir_Cuti_' . str_replace(' ', '_', $cuti->user->name) . '.doc';

        return response($htmlContent)
            ->header('Content-Type', 'application/msword; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0, must-revalidate');
    }

    /**
     * Menampilkan halaman Kalender Cuti dan data aparatur yang sedang cuti hari ini
     * (Khusus hanya dapat diakses oleh Administrator Kepegawaian)
     */
    public function kalender()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Fitur Kalender Cuti hanya dapat diakses oleh Administrator Kepegawaian.');
        }

        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // Pegawai yang sedang cuti hari ini (Status sudah disetujui / selesai)
        $pegawaiCutiHariIni = PengajuanCuti::with(['user.pegawai'])
            ->whereIn('status', ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->orderBy('tgl_selesai', 'asc')
            ->get();

        $isLeaderOrAdmin = true;

        return view('cuti.kalender', compact('user', 'pegawaiCutiHariIni', 'isLeaderOrAdmin'));
    }

    /**
     * Endpoint API JSON untuk FullCalendar.js
     * (Khusus hanya dapat diakses oleh Administrator Kepegawaian)
     */
    public function kalenderEvents(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Data kalender cuti hanya dapat diakses oleh Administrator Kepegawaian.');
        }

        $user = Auth::user();

        $query = PengajuanCuti::with(['user.pegawai'])
            ->whereIn('status', ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui']);

        // Jika FullCalendar mengirim filter rentang tanggal (start dan end)
        if ($request->filled('start') && $request->filled('end')) {
            $start = Carbon::parse($request->start)->toDateString();
            $end = Carbon::parse($request->end)->toDateString();
            $query->where('tgl_mulai', '<=', $end)
                  ->where('tgl_selesai', '>=', $start);
        }

        $daftarCuti = $query->get();

        $events = $daftarCuti->map(function ($cuti) {
            // Palette warna berdasarkan jenis cuti
            $colors = match ($cuti->jenis_cuti) {
                'Cuti Tahunan' => ['bg' => '#0A773A', 'border' => '#085C2D'],
                'Cuti Besar' => ['bg' => '#7c3aed', 'border' => '#6d28d9'],
                'Cuti Sakit' => ['bg' => '#ea580c', 'border' => '#c2410c'],
                'Cuti Melahirkan' => ['bg' => '#e11d48', 'border' => '#be123c'],
                'Cuti Karena Alasan Penting' => ['bg' => '#0d9488', 'border' => '#0f766e'],
                'Cuti Bersama' => ['bg' => '#0284c7', 'border' => '#0369a1'],
                default => ['bg' => '#475569', 'border' => '#334155'],
            };

            // FullCalendar allDay end date bersifat exclusive, maka tgl_selesai ditambah 1 hari
            $startDate = Carbon::parse($cuti->tgl_mulai)->format('Y-m-d');
            $endDateExclusive = Carbon::parse($cuti->tgl_selesai ?? $cuti->tgl_mulai)->addDay()->format('Y-m-d');

            return [
                'id' => $cuti->id,
                'title' => ($cuti->user?->name ?? 'Pegawai') . ' - ' . $cuti->jenis_cuti,
                'start' => $startDate,
                'end' => $endDateExclusive,
                'allDay' => true,
                'display' => 'block',
                'backgroundColor' => $colors['bg'],
                'borderColor' => $colors['border'],
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'nama' => $cuti->user?->name ?? 'Pegawai',
                    'nip' => $cuti->user?->nip ?? '-',
                    'jabatan' => $cuti->user?->jabatan ?? '-',
                    'unit_kerja' => $cuti->user?->unit_kerja ?? '-',
                    'jenis_cuti' => $cuti->jenis_cuti,
                    'durasi_hari' => $cuti->durasi_hari,
                    'tgl_mulai_formatted' => Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY'),
                    'tgl_selesai_formatted' => Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY'),
                    'tgl_mulai_raw' => Carbon::parse($cuti->tgl_mulai)->format('Y-m-d'),
                    'tgl_selesai_raw' => Carbon::parse($cuti->tgl_selesai)->format('Y-m-d'),
                    'nomor_surat' => $cuti->nomor_surat ?? '-',
                    'can_view_alasan' => true,
                    'alasan' => $cuti->alasan,
                ],
            ];
        });

        return response()->json($events);
    }
}
