<?php

use App\Http\Controllers\JabatanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Illuminate\Support\Facades\Auth::user();
    $today = \Carbon\Carbon::today()->format('Y-m-d');
    
    // Hitung antrean berdasarkan jabatan/role yang sedang login
    $jabatanId = $user->pegawai?->jabatan_id;
    $countAtasan = ($user->is_atasan_langsung && $jabatanId) ? \App\Models\PengajuanCuti::whereHas('user.pegawai.jabatan', function($q) use ($jabatanId) {
        $q->where('parent_id', $jabatanId);
    })->where('status', 'MENUNGGU_ATASAN')->count() : 0;

    $countAdmin = $user->role === 'admin' 
        ? \App\Models\PengajuanCuti::where('status', 'MENUNGGU_PENOMORAN')->count() 
        : 0;

    $countKetua = $user->is_pimpinan_final 
        ? \App\Models\PengajuanCuti::where(function ($query) {
            $query->where('status', 'MENUNGGU_KETUA')
                ->orWhere(function ($sub) {
                    $sub->where('status', 'MENUNGGU_ATASAN')
                        ->whereHas('user.pegawai.jabatan', function ($jq) {
                            $jq->whereHas('parent', function ($pq) {
                                $pq->whereIn('nama_jabatan', ['Ketua', 'Ketua PA', 'Ketua Pengadilan Agama']);
                            });
                        });
                });
        })->count() 
        : 0;

    $riwayatTerbaru = \App\Models\PengajuanCuti::where('user_id', $user->id)
        ->latest()
        ->take(5)
        ->get();

    // Pegawai yang sedang cuti hari ini (Khusus hanya dapat diakses & dilihat oleh Admin)
    $pegawaiCutiHariIni = $user->role === 'admin' 
        ? \App\Models\PengajuanCuti::with(['user.pegawai'])
            ->whereIn('status', ['SELESAI_DISETUJUI', 'DISETUJUI', 'Disetujui'])
            ->whereDate('tgl_mulai', '<=', $today)
            ->whereDate('tgl_selesai', '>=', $today)
            ->orderBy('tgl_selesai', 'asc')
            ->get()
        : collect();

    return view('dashboard', compact('user', 'countAtasan', 'countAdmin', 'countKetua', 'riwayatTerbaru', 'pegawaiCutiHariIni'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Kalender Cuti
    Route::get('/kalender-cuti', [PengajuanCutiController::class, 'kalender'])->name('cuti.kalender');
    Route::get('/kalender-cuti/events', [PengajuanCutiController::class, 'kalenderEvents'])->name('cuti.kalender.events');

    // Route khusus Pengajuan Cuti
    Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'create'])->name('cuti.create');
    Route::post('/cuti/ajukan', [PengajuanCutiController::class, 'store'])->name('cuti.store');
    Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayat'])->name('cuti.riwayat');
    Route::get('/cuti', [PengajuanCutiController::class, 'riwayat'])->name('cuti.index');
    Route::get('/cuti/{cuti}/edit', [PengajuanCutiController::class, 'edit'])->name('cuti.edit');
    Route::match(['put', 'patch'], '/cuti/{cuti}', [PengajuanCutiController::class, 'update'])->name('cuti.update');
    Route::patch('/cuti/{cuti}/tarik', [PengajuanCutiController::class, 'tarik'])->name('cuti.tarik');
    // Route untuk Ekspor/Cetak PDF & Word
    Route::get('/cuti/cetak/{id}', [PengajuanCutiController::class, 'cetakPdf'])->name('cuti.cetak');
    Route::get('/cuti/download-pdf/{id}', [PengajuanCutiController::class, 'cetakPdf'])->name('cuti.download_pdf');
    Route::get('/cuti/download-word/{id}', [PengajuanCutiController::class, 'downloadWord'])->name('cuti.download_word');

    // Route untuk Dasbor Persetujuan & Riwayat Atasan Langsung
    Route::get('/cuti/persetujuan', [PengajuanCutiController::class, 'persetujuanAtasan'])->name('cuti.persetujuan');
    Route::get('/cuti/persetujuan/riwayat', [PengajuanCutiController::class, 'riwayatAtasan'])->name('cuti.persetujuan.riwayat');
    Route::get('/cuti/persetujuan/{id}', [PengajuanCutiController::class, 'tinjau'])->name('cuti.tinjau');
    Route::post('/cuti/persetujuan/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('cuti.proses');

    // Route untuk Antrean & Riwayat Keputusan Final Ketua PA
    Route::get('/cuti/keputusan-ketua', [PengajuanCutiController::class, 'daftarKetua'])->name('cuti.daftar_ketua');
    Route::get('/cuti/keputusan-ketua/riwayat', [PengajuanCutiController::class, 'riwayatKetua'])->name('cuti.ketua.riwayat');
    Route::get('/cuti/keputusan-ketua/{id}', [PengajuanCutiController::class, 'tinjauKetua'])->name('cuti.tinjau_ketua');
    Route::post('/cuti/keputusan-ketua/{id}', [PengajuanCutiController::class, 'prosesKetua'])->name('cuti.proses_ketua');

    // Route untuk Antrean & Riwayat Penomoran Surat oleh Admin Kepegawaian
    Route::get('/cuti/penomoran', [PengajuanCutiController::class, 'daftarPenomoran'])->name('cuti.penomoran');
    Route::get('/cuti/penomoran/riwayat', [PengajuanCutiController::class, 'riwayatPenomoran'])->name('cuti.penomoran.riwayat');
    Route::get('/cuti/penomoran/{id}', [PengajuanCutiController::class, 'inputNomor'])->name('cuti.input_nomor');
    Route::post('/cuti/penomoran/{id}', [PengajuanCutiController::class, 'simpanNomor'])->name('cuti.simpan_nomor');

    // Route CRUD Data Pegawai & Akun (Khusus Admin)
    Route::resource('pegawai', PegawaiController::class);

    // Route CRUD Data Jabatan & Struktur Organisasi (Khusus Admin)
    Route::resource('jabatan', JabatanController::class);

    // Route Web Push Notifications
    Route::get('/push/public-key', [PushSubscriptionController::class, 'getPublicKey'])->name('push.public-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::post('/push/test', [PushSubscriptionController::class, 'test'])->name('push.test');

    // Route Riwayat & Pengaturan Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{id}/baca', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifikasi/toggle-setting', [NotificationController::class, 'toggleSetting'])->name('notifications.toggle');
    Route::delete('/notifikasi/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifikasi/hapus-semua', [NotificationController::class, 'clearAll'])->name('notifications.clear');
});

require __DIR__.'/auth.php';
