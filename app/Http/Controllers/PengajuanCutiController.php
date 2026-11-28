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

class PengajuanCutiController extends Controller
{
    /**
     * @return array{totalKuota: int, kuotaPending: int, sisaKuotaEfektif: int}
     */
    private function hitungKuotaCutiTahunan(?Pegawai $pegawai, ?int $excludePengajuanId = null): array
    {
        if (!$pegawai) {
            return [
                'totalKuota' => 0,
                'kuotaPending' => 0,
                'sisaKuotaEfektif' => 0,
            ];
        }

        $totalKuota = max(0, (int) $pegawai->sisa_cuti_n)
            + max(0, (int) $pegawai->sisa_cuti_n_1)
            + max(0, (int) $pegawai->sisa_cuti_n_2);

        $kuotaPending = (int) PengajuanCuti::query()
            ->where('user_id', $pegawai->user_id)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->where('satuan_cuti', 'hari')
            ->whereIn('status', [
                'MENUNGGU_ATASAN',
                'MENUNGGU_KETUA',
                'REVISI_ATASAN',
                'REVISI_KETUA',
                'DITANGGUHKAN_ATASAN',
                'DITANGGUHKAN_KETUA',
            ])
            ->when($excludePengajuanId, fn ($query) => $query->whereKeyNot($excludePengajuanId))
            ->sum('durasi_hari');

        return [
            'totalKuota' => $totalKuota,
            'kuotaPending' => $kuotaPending,
            'sisaKuotaEfektif' => max(0, $totalKuota - $kuotaPending),
        ];
    }

    // Menampilkan form HTML
    public function create()
    {
        $user = Auth::user();
        $kuotaTahunan = $this->hitungKuotaCutiTahunan($user->pegawai);
        $kuotaPending = $kuotaTahunan['kuotaPending'];
        $sisaKuotaEfektif = $kuotaTahunan['sisaKuotaEfektif'];

        return view('cuti.create', compact('user', 'kuotaPending', 'sisaKuotaEfektif'));
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

        // Validasi Cuti Melahirkan: Tidak dapat diajukan oleh pegawai berjenis kelamin Laki-laki
        $user = Auth::user();
        if ($request->jenis_cuti === 'Cuti Melahirkan' && ($user->jenis_kelamin === 'Laki-laki' || $user->pegawai?->jenis_kelamin === 'Laki-laki')) {
            throw ValidationException::withMessages([
                'jenis_cuti' => 'Jenis cuti melahirkan hanya diperuntukkan bagi pegawai perempuan.',
            ]);
        }

        // Validasi: Durasi hari kerja tidak boleh melebihi rentang hari kalender pengajuan (jika satuan hari)
        if ($request->satuan_cuti === 'hari') {
            $rentangHari = Carbon::parse($request->tgl_mulai)->diffInDays(Carbon::parse($request->tgl_selesai)) + 1;
            if ($request->durasi_hari > $rentangHari) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi hari kerja (' . $request->durasi_hari . ' hari) tidak boleh melebihi rentang tanggal pengajuan (' . $rentangHari . ' hari).',
                ]);
            }
        }

        [$cutiBaru, $atasanPegawai, $isDirectUnderKetua, $isDuplicate] = DB::transaction(function () use ($request, $user) {
            $pegawai = Pegawai::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $isDuplicate = PengajuanCuti::query()
                ->where('user_id', $user->id)
                ->where('jenis_cuti', $request->jenis_cuti)
                ->where('tgl_mulai', $request->tgl_mulai)
                ->where('tgl_selesai', $request->tgl_selesai)
                ->where('durasi_hari', $request->durasi_hari)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->exists();

            if ($isDuplicate) {
                return [null, null, false, true];
            }

            // Validasi kuota dilakukan setelah kunci pegawai diperoleh agar pengajuan paralel tidak memakai saldo yang sama.
            if ($request->jenis_cuti === 'Cuti Tahunan' && $request->satuan_cuti === 'hari') {
                $sisaEfektif = $this->hitungKuotaCutiTahunan($pegawai)['sisaKuotaEfektif'];
                if ($request->durasi_hari > $sisaEfektif) {
                    throw ValidationException::withMessages([
                        'durasi_hari' => 'Durasi cuti tahunan (' . $request->durasi_hari . ' hari) melebihi sisa kuota efektif (' . $sisaEfektif . ' hari) setelah memperhitungkan pengajuan yang masih menunggu persetujuan.',
                    ]);
                }
            }

            // Cek hierarki kepegawaian: apakah pemohon merupakan bawahan langsung Ketua PA (atau tanpa atasan perantara)
            $atasanPegawai = $pegawai?->getAtasanPegawai();
            $isDirectUnderKetua = (!$atasanPegawai || $atasanPegawai->is_pimpinan_final);
            $initialStatus = $isDirectUnderKetua ? 'MENUNGGU_KETUA' : 'MENUNGGU_ATASAN';

            $cutiBaru = PengajuanCuti::create([
                'user_id'            => $user->id,
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

            return [$cutiBaru, $atasanPegawai, $isDirectUnderKetua, false];
        });

        if ($isDuplicate) {
            return redirect()->route('cuti.riwayat')
                ->with('success', 'Pengajuan cuti identik sudah tercatat. Tidak ada pengajuan duplikat yang dibuat.');
        }

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

        // Validasi Cuti Melahirkan: Tidak dapat diajukan oleh pegawai berjenis kelamin Laki-laki
        $user = Auth::user();
        if ($request->jenis_cuti === 'Cuti Melahirkan' && ($user->jenis_kelamin === 'Laki-laki' || $user->pegawai?->jenis_kelamin === 'Laki-laki')) {
            throw ValidationException::withMessages([
                'jenis_cuti' => 'Jenis cuti melahirkan hanya diperuntukkan bagi pegawai perempuan.',
            ]);
        }

        // Validasi: Durasi hari kerja tidak boleh melebihi rentang hari kalender pengajuan (jika satuan hari)
        if ($request->satuan_cuti === 'hari') {
            $rentangHari = Carbon::parse($request->tgl_mulai)->diffInDays(Carbon::parse($request->tgl_selesai)) + 1;
            if ($request->durasi_hari > $rentangHari) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi hari kerja (' . $request->durasi_hari . ' hari) tidak boleh melebihi rentang tanggal pengajuan (' . $rentangHari . ' hari).',
                ]);
            }
        }

        // Validasi Kuota: kecualikan pengajuan revisi ini dari saldo yang masih pending.
        if ($request->jenis_cuti === 'Cuti Tahunan' && $request->satuan_cuti === 'hari') {
            $sisaEfektif = $this->hitungKuotaCutiTahunan($user->pegawai, $cuti->id)['sisaKuotaEfektif'];
            if ($request->durasi_hari > $sisaEfektif) {
                throw ValidationException::withMessages([
                    'durasi_hari' => 'Durasi cuti tahunan (' . $request->durasi_hari . ' hari) melebihi sisa kuota efektif (' . $sisaEfektif . ' hari) setelah memperhitungkan pengajuan lain yang masih menunggu persetujuan.',
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
                'status'             => 'MENUNGGU_ATASAN', 
                'catatan_atasan'     => null,
                'catatan_ketua'      => null,
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

        $cuti->catatan_atasan = $request->catatan;
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

                        $durasi = (int) $cuti->durasi_hari;
                        $saldoFields = ['sisa_cuti_n_2', 'sisa_cuti_n_1', 'sisa_cuti_n'];
                        $totalKuota = array_sum(array_map(
                            fn ($field) => max(0, (int) $pegawai->{$field}),
                            $saldoFields
                        ));

                        if ($durasi > $totalKuota) {
                            abort(422, 'Sisa kuota cuti pemohon (' . $totalKuota . ' hari) tidak mencukupi untuk durasi pengajuan (' . $durasi . ' hari). Kuota tidak dapat dipotong.');
                        }

                        // Gunakan saldo terlama lebih dulu: N-2, kemudian N-1, lalu N.
                        $sisaDurasi = $durasi;
                        foreach ($saldoFields as $field) {
                            $saldo = max(0, (int) $pegawai->{$field});
                            $potongan = min($saldo, $sisaDurasi);
                            $pegawai->{$field} = $saldo - $potongan;
                            $sisaDurasi -= $potongan;
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

            $cuti->catatan_ketua = $request->catatan;
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

    /**
     * Helper untuk membuat TemplateProcessor dengan variabel yang telah terisi
     */
    protected function generateFilledTemplateProcessor(\App\Models\PengajuanCuti $cuti): \PhpOffice\PhpWord\TemplateProcessor
    {
        $templatePath = storage_path('app/templates/format_surat_izin_cuti.docx');
        if (!file_exists($templatePath)) {
            $altTemplatePath = storage_path('app/templates/format surat izin cuti.docx');
            if (file_exists($altTemplatePath)) {
                $templatePath = $altTemplatePath;
            } else {
                abort(404, 'File template formulir cuti tidak ditemukan di storage/app/templates.');
            }
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Data Pemohon
        $pegawai = $cuti->user?->pegawai?->fresh();
        $namaPegawai = $cuti->user?->name ?? '-';
        $nip = $pegawai?->nip ?? ($cuti->user?->nip ?? '-');
        $jabatan = $pegawai?->jabatan?->nama_jabatan ?? ($cuti->user?->jabatan ?? '-');
        $masaKerja = $pegawai?->masa_kerja ?? '-';
        $alamatCuti = $cuti->alamat_selama_cuti ?? '-';
        $noTelepon = $cuti->no_telp ?? '-';

        // Tanggal & Nomor Surat
        $tanggalSurat = Carbon::parse($cuti->created_at)->isoFormat('D MMMM YYYY');
        $nomorSurat = $cuti->nomor_surat ?: '..........................................';

        // Durasi & Periode
        $lamaCuti = $cuti->durasi_hari;
        $satuanCuti = ucfirst($cuti->satuan_cuti ?? 'hari');
        $alasanCuti = $cuti->alasan ?? '-';
        $tanggalMulai = Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY');
        $tanggalSelesai = Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY');

        // Kuota Sisa Cuti
        $sisaCutiN = $pegawai?->sisa_cuti_n ?? 0;
        $sisaCutiN1 = $pegawai?->sisa_cuti_n_1 ?? 0;
        $sisaCutiN2 = $pegawai?->sisa_cuti_n_2 ?? 0;

        // Ceklis Jenis Cuti (✓ jika cocok, '' jika tidak)
        $jenis = trim($cuti->jenis_cuti);
        $cTahunan = ($jenis === 'Cuti Tahunan') ? '✓' : '';
        $cBesar = ($jenis === 'Cuti Besar') ? '✓' : '';
        $cSakit = ($jenis === 'Cuti Sakit') ? '✓' : '';
        $cMelahirkan = ($jenis === 'Cuti Melahirkan') ? '✓' : '';
        $cAlasanPenting = ($jenis === 'Cuti Karena Alasan Penting') ? '✓' : '';
        $cDiluarTanggungan = ($jenis === 'Cuti di Luar Tanggungan Negara') ? '✓' : '';

        // Data Atasan Langsung
        $atasanPegawai = $cuti->atasan_penyetuju?->pegawai ?? $pegawai?->getAtasanPegawai();
        $namaAtasan = $atasanPegawai?->nama_lengkap ?? ($cuti->atasan_penyetuju?->name ?? '-');
        $jabatanAtasan = $atasanPegawai?->jabatan?->nama_jabatan ?? ($cuti->atasan_penyetuju?->jabatan ?? 'Atasan Langsung');

        // Status Pertimbangan Atasan (jika disetujui atasan)
        $atasanSetuju = '✓';
        $atasanUbah = '';
        $atasanTangguh = '';
        $atasanTolak = '';

        // Data Ketua PA (Pejabat Berwenang)
        $ketua = \App\Models\Pegawai::whereHas('jabatan', function ($q) {
            $q->where('nama_jabatan', 'like', '%Ketua%')
              ->where('nama_jabatan', 'not like', '%Wakil%');
        })->with(['user', 'jabatan'])->first();

        $namaKetua = $cuti->ketua_pengesah?->name ?? ($ketua?->nama_lengkap ?? ($ketua?->user?->name ?? 'Ketua Pengadilan Agama'));

        $ketuaSetuju = '✓';
        $ketuaUbah = '';
        $ketuaTangguh = '';
        $ketuaTolak = '';

        // Pemetaan Variabel ke Template Word
        $templateProcessor->setValue('tanggal_surat', $tanggalSurat);
        $templateProcessor->setValue('nomor_surat', $nomorSurat);
        $templateProcessor->setValue('nama_pegawai', $namaPegawai);
        $templateProcessor->setValue('nip', $nip);
        $templateProcessor->setValue('jabatan', $jabatan);
        $templateProcessor->setValue('masa_kerja', $masaKerja);
        $templateProcessor->setValue('alamat_cuti', $alamatCuti);
        $templateProcessor->setValue('no_telepon', $noTelepon);

        $templateProcessor->setValue('c_tahunan', $cTahunan);
        $templateProcessor->setValue('c_besar', $cBesar);
        $templateProcessor->setValue('c_sakit', $cSakit);
        $templateProcessor->setValue('c_melahirkan', $cMelahirkan);
        $templateProcessor->setValue('c_alasan_penting', $cAlasanPenting);
        $templateProcessor->setValue('c_diluar_tanggungan', $cDiluarTanggungan);

        $templateProcessor->setValue('alasan_cuti', $alasanCuti);
        $templateProcessor->setValue('lama_cuti', $lamaCuti);

        // Render satuan_cuti dengan TextRun + strikethrough (hari/bulan/tahun)
        $satuan = strtolower($cuti->satuan_cuti ?? 'hari');
        $textRunSatuan = new \PhpOffice\PhpWord\Element\TextRun();
        $normalStyle = ['name' => 'Calibri', 'size' => 10];
        $strikeStyle = ['name' => 'Calibri', 'size' => 10, 'strikethrough' => true];

        if ($satuan === 'hari') {
            $textRunSatuan->addText('hari', $normalStyle);
            $textRunSatuan->addText('/bulan/tahun', $strikeStyle);
        } elseif ($satuan === 'bulan') {
            $textRunSatuan->addText('hari/', $strikeStyle);
            $textRunSatuan->addText('bulan', $normalStyle);
            $textRunSatuan->addText('/tahun', $strikeStyle);
        } else { // tahun
            $textRunSatuan->addText('hari/bulan/', $strikeStyle);
            $textRunSatuan->addText('tahun', $normalStyle);
        }

        $templateProcessor->setComplexValue('satuan_cuti', $textRunSatuan);
        $templateProcessor->setValue('tanggal_mulai', $tanggalMulai);
        $templateProcessor->setValue('tanggal_selesai', $tanggalSelesai);

        // Tahun dinamis (N, N-1, N-2) berdasarkan tanggal mulai cuti
        $tahunBerjalan = !empty($cuti->tgl_mulai)
            ? \Carbon\Carbon::parse($cuti->tgl_mulai)->year
            : \Carbon\Carbon::now()->year;

        $templateProcessor->setValue('tahun_n2', $tahunBerjalan - 2);
        $templateProcessor->setValue('tahun_n1', $tahunBerjalan - 1);
        $templateProcessor->setValue('tahun_n',  $tahunBerjalan);

        $templateProcessor->setValue('sisa_cuti_n', $sisaCutiN);
        $templateProcessor->setValue('sisa_cuti_n1', $sisaCutiN1);
        $templateProcessor->setValue('sisa_cuti_n2', $sisaCutiN2);

        $templateProcessor->setValue('atasan_setuju', $atasanSetuju);
        $templateProcessor->setValue('atasan_ubah', $atasanUbah);
        $templateProcessor->setValue('atasan_tangguh', $atasanTangguh);
        $templateProcessor->setValue('atasan_tolak', $atasanTolak);
        $templateProcessor->setValue('jabatan_atasan', $jabatanAtasan);
        $templateProcessor->setValue('nama_atasan', $namaAtasan);

        $templateProcessor->setValue('ketua_setuju', $ketuaSetuju);
        $templateProcessor->setValue('ketua_ubah', $ketuaUbah);
        $templateProcessor->setValue('ketua_tangguh', $ketuaTangguh);
        $templateProcessor->setValue('ketua_tolak', $ketuaTolak);
        $templateProcessor->setValue('nama_ketua', $namaKetua);

        $templateProcessor->setValue('catatan_atasan', $cuti->catatan_atasan ?? '');
        $templateProcessor->setValue('catatan_ketua', $cuti->catatan_ketua ?? '');

        return $templateProcessor;
    }

    /**
     * Mengunduh dokumen formulir cuti dalam format Word (.doc) yang editable
     */
    public function downloadWord($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin Kepegawaian yang berhak mengunduh dokumen formulir cuti.');
        }

        $cuti = \App\Models\PengajuanCuti::with([
            'user.pegawai.jabatan.parent',
            'atasan_penyetuju.pegawai',
            'ketua_pengesah.pegawai',
        ])->findOrFail($id);

        // Proteksi 1: Pastikan dokumen sudah selesai ACC final dan bernomor surat
        if (!in_array($cuti->status, ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])) {
            abort(403, 'Dokumen belum selesai diproses atau belum diterbitkan nomor surat resminya.');
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
     * Mengunduh dokumen formulir cuti dalam format Microsoft Word (.docx) berbasis template resmi
     */
    public function exportWord($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin Kepegawaian yang berhak mengunduh dokumen formulir cuti.');
        }

        $cuti = \App\Models\PengajuanCuti::with([
            'user.pegawai.jabatan.parent',
            'atasan_penyetuju.pegawai.jabatan',
            'ketua_pengesah.pegawai.jabatan',
        ])->findOrFail($id);

        // Proteksi 1: Pastikan dokumen sudah selesai ACC final dan bernomor surat
        if (!in_array($cuti->status, ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])) {
            abort(403, 'Dokumen belum selesai diproses atau belum diterbitkan nomor surat resminya.');
        }

        $templateProcessor = $this->generateFilledTemplateProcessor($cuti);

        // Simpan file sementara di storage/app/public/
        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $cuti->user?->name ?? 'Pegawai');
        $filename = 'Surat_Cuti_' . $safeName . '_' . $cuti->id . '.docx';
        $tempPath = storage_path('app/public/' . $filename);

        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function authorizeCalendarAccess(): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && !$user->is_pimpinan_final) {
            abort(403, 'Akses kalender cuti hanya tersedia untuk Admin Kepegawaian dan Ketua/Pimpinan Tertinggi.');
        }
    }

    /**
     * Menampilkan halaman Kalender Cuti dan data aparatur yang sedang cuti hari ini
     * (Dapat diakses oleh Administrator Kepegawaian dan Ketua/Pimpinan Tertinggi)
     */
    public function kalender()
    {
        $this->authorizeCalendarAccess();

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
     * (Dapat diakses oleh Administrator Kepegawaian dan Ketua/Pimpinan Tertinggi)
     */
    public function kalenderEvents(Request $request)
    {
        $this->authorizeCalendarAccess();

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
                    'alamat_selama_cuti' => $cuti->alamat_selama_cuti,
                    'no_telp' => $cuti->no_telp,
                ],
            ];
        });

        return response()->json($events);
    }
}
