<?php

use App\Models\User;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Setup 4 actors according to architecture
    $this->userAtasan = User::factory()->create([
        'name' => 'Bapak Atasan',
        'email' => 'atasan_test@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiAtasan = Pegawai::create([
        'user_id' => $this->userAtasan->id,
        'nip' => '198001012005011001',
        'jabatan' => 'Panitera Muda',
        'unit_kerja' => 'Kepaniteraan',
        'is_atasan' => true,
        'tanggal_masuk' => '2015-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 6,
        'sisa_cuti_n_2' => 0,
    ]);

    $this->userRiver = User::factory()->create([
        'name' => 'River Al Farizie',
        'email' => 'river_test@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiRiver = Pegawai::create([
        'user_id' => $this->userRiver->id,
        'nip' => '199802022022031002',
        'jabatan' => 'Staf IT',
        'unit_kerja' => 'Kesekretariatan',
        'tanggal_masuk' => '2022-01-01',
        'atasan_langsung_id' => $this->pegawaiAtasan->id,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 4,
        'sisa_cuti_n_2' => 2,
    ]);

    $this->userAdmin = User::factory()->create([
        'name' => 'Admin Kepegawaian',
        'email' => 'admin_test@pa-sumber.go.id',
        'role' => 'admin',
    ]);
    $this->pegawaiAdmin = Pegawai::create([
        'user_id' => $this->userAdmin->id,
        'nip' => '198505052010122001',
        'jabatan' => 'Kasubbag Kepegawaian',
        'unit_kerja' => 'Kepegawaian',
        'tanggal_masuk' => '2010-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    $this->userKetua = User::factory()->create([
        'name' => 'Bapak Ketua PA',
        'email' => 'ketua_test@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiKetua = Pegawai::create([
        'user_id' => $this->userKetua->id,
        'nip' => '197003031995031001',
        'jabatan' => 'Ketua PA',
        'unit_kerja' => 'Pimpinan',
        'is_atasan' => true,
        'tanggal_masuk' => '2000-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    $this->userOther = User::factory()->create([
        'name' => 'Pegawai Lain',
        'email' => 'other_test@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiOther = Pegawai::create([
        'user_id' => $this->userOther->id,
        'nip' => '199501012020011003',
        'jabatan' => 'Staf Umum',
        'unit_kerja' => 'Umum',
        'tanggal_masuk' => '2022-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    // Relasikan pohon hierarki: jabatan atasan & admin menginduk ke Ketua PA
    $this->pegawaiAtasan->jabatan->update(['parent_id' => $this->pegawaiKetua->jabatan_id]);
    $this->pegawaiAdmin->jabatan->update(['parent_id' => $this->pegawaiKetua->jabatan_id]);
    $this->pegawaiOther->jabatan->update(['parent_id' => $this->pegawaiAtasan->jabatan_id]);
});

test('unauthenticated users cannot access cuti routes', function () {
    $this->get('/cuti/ajukan')->assertRedirect('/login');
    $this->get('/cuti/riwayat')->assertRedirect('/login');
    $this->get('/cuti/persetujuan')->assertRedirect('/login');
    $this->get('/cuti/penomoran')->assertRedirect('/login');
    $this->get('/cuti/keputusan-ketua')->assertRedirect('/login');
    $this->get('/cuti/cetak/1')->assertRedirect('/login');
});

test('pegawai can submit cuti application with validation', function () {
    // Validasi form kosong
    $this->actingAs($this->userRiver)
        ->post('/cuti/ajukan', [])
        ->assertSessionHasErrors(['jenis_cuti', 'alasan', 'tgl_mulai', 'tgl_selesai', 'durasi_hari', 'alamat_selama_cuti', 'no_telp']);

    // Validasi tanggal selesai sebelum tanggal mulai
    $this->actingAs($this->userRiver)
        ->post('/cuti/ajukan', [
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Liburan',
            'tgl_mulai' => '2026-11-05',
            'tgl_selesai' => '2026-11-01',
            'durasi_hari' => 3,
            'alamat_selama_cuti' => 'Cirebon',
            'no_telp' => '08123456789',
        ])
        ->assertSessionHasErrors(['tgl_selesai']);

    // Submit data valid
    $response = $this->actingAs($this->userRiver)
        ->post('/cuti/ajukan', [
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Urusan Keluarga',
            'tgl_mulai' => '2026-11-01',
            'tgl_selesai' => '2026-11-03',
            'durasi_hari' => 3,
            'alamat_selama_cuti' => 'Cirebon',
            'no_telp' => '08123456789',
        ]);

    $response->assertRedirect(route('cuti.riwayat'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('pengajuan_cutis', [
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'status' => 'MENUNGGU_ATASAN',
        'durasi_hari' => 3,
    ]);
});

test('authorization: only direct atasan can review submission', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'status' => 'MENUNGGU_ATASAN',
    ]);

    // Pegawai lain (bukan atasan) mencoba akses -> 403
    $this->actingAs($this->userOther)
        ->get("/cuti/persetujuan/{$cuti->id}")
        ->assertStatus(403);

    // Atasan langsung dapat mengakses -> 200
    $this->actingAs($this->userAtasan)
        ->get("/cuti/persetujuan/{$cuti->id}")
        ->assertOk()
        ->assertSee('River Al Farizie');
});

test('atasan can approve or reject cuti application', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'status' => 'MENUNGGU_ATASAN',
    ]);

    // Atasan menyetujui
    $response = $this->actingAs($this->userAtasan)
        ->post("/cuti/persetujuan/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Disetujui silakan lanjut',
        ]);

    $response->assertRedirect('/cuti/persetujuan');
    $cuti->refresh();
    expect($cuti->status)->toBe('MENUNGGU_KETUA');
    expect($cuti->catatan_revisi)->toBe('Disetujui silakan lanjut');
    expect($cuti->disetujui_oleh_atasan_id)->toBe($this->userAtasan->id);
    expect($cuti->atasan_penyetuju->id)->toBe($this->userAtasan->id);
});

test('authorization: only admin can access penomoran surat', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'status' => 'MENUNGGU_PENOMORAN',
    ]);

    // Non-admin (misal River) -> 403
    $this->actingAs($this->userRiver)
        ->get('/cuti/penomoran')
        ->assertStatus(403);

    $this->actingAs($this->userRiver)
        ->get("/cuti/penomoran/{$cuti->id}")
        ->assertStatus(403);

    // Admin -> 200
    $this->actingAs($this->userAdmin)
        ->get('/cuti/penomoran')
        ->assertOk()
        ->assertSee('River Al Farizie');

    // Admin input nomor surat
    $response = $this->actingAs($this->userAdmin)
        ->post("/cuti/penomoran/{$cuti->id}", [
            'nomor_surat' => 'W10-A4/055/KP5.3/11/2026',
        ]);

    $response->assertRedirect('/cuti/penomoran');
    $cuti->refresh();
    expect($cuti->status)->toBe('SELESAI_DISETUJUI');
    expect($cuti->nomor_surat)->toBe('W10-A4/055/KP5.3/11/2026');
});

test('authorization: only Ketua PA can access keputusan ketua', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/055/KP5.3/11/2026',
    ]);

    // Non-Ketua -> 403
    $this->actingAs($this->userAdmin)
        ->get('/cuti/keputusan-ketua')
        ->assertStatus(403);

    $this->actingAs($this->userRiver)
        ->get("/cuti/keputusan-ketua/{$cuti->id}")
        ->assertStatus(403);

    // Ketua PA -> 200
    $this->actingAs($this->userKetua)
        ->get('/cuti/keputusan-ketua')
        ->assertOk()
        ->assertSee('River Al Farizie');

    $this->actingAs($this->userKetua)
        ->get("/cuti/keputusan-ketua/{$cuti->id}")
        ->assertOk()
        ->assertSee('Ringkasan Pengajuan');
});

test('ketua approves cuti and automatically cascades leave quota deduction', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-05',
        'durasi_hari' => 14, // Lebih besar dari sisa_cuti_n (12), harus ambil dari N-1 (4)
        'status' => 'MENUNGGU_KETUA',
    ]);

    // Initial state: N=12, N-1=4, N-2=2
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(12);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe(4);

    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Selamat berlibur',
        ])
        ->assertRedirect('/cuti/keputusan-ketua');

    $cuti->refresh();
    expect($cuti->status)->toBe('MENUNGGU_PENOMORAN');
    expect($cuti->disahkan_oleh_ketua_id)->toBe($this->userKetua->id);
    expect($cuti->ketua_pengesah->id)->toBe($this->userKetua->id);

    $this->pegawaiRiver->refresh();
    // 14 hari: N habis (12 -> 0), sisa 2 hari diambil dari N-1 (4 -> 2), N-2 tetap 2
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(0);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe(2);
    expect($this->pegawaiRiver->sisa_cuti_n_2)->toBe(2);
});

test('pdf print security and rendering', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Cirebon',
        'no_telp' => '08123456789',
        'status' => 'MENUNGGU_ATASAN', // Belum selesai
    ]);

    // 1. Belum disetujui -> 403
    $this->actingAs($this->userRiver)
        ->get("/cuti/cetak/{$cuti->id}")
        ->assertStatus(403);

    // Set status SELESAI_DISETUJUI
    $cuti->status = 'SELESAI_DISETUJUI';
    $cuti->nomor_surat = 'W10-A4/055/KP5.3/11/2026';
    $cuti->save();

    // 2. User lain mencoba mencetak dokumen River -> 403
    $this->actingAs($this->userOther)
        ->get("/cuti/cetak/{$cuti->id}")
        ->assertStatus(403);

    // 3. Pemohon asli (River) mencetak -> 200 PDF stream
    $responseRiver = $this->actingAs($this->userRiver)
        ->get("/cuti/cetak/{$cuti->id}");
    $responseRiver->assertOk();
    $responseRiver->assertHeader('Content-Type', 'application/pdf');

    // 4. Admin Kepegawaian mencetak -> 200 PDF stream
    $responseAdmin = $this->actingAs($this->userAdmin)
        ->get("/cuti/cetak/{$cuti->id}");
    $responseAdmin->assertOk();
    $responseAdmin->assertHeader('Content-Type', 'application/pdf');
});

test('durasi_hari cannot exceed calendar day range at submission', function () {
    // 2 hari kalender (01 Nov s/d 02 Nov), tetapi diinput durasi 5 hari -> harus gagal
    $this->actingAs($this->userRiver)
        ->post('/cuti/ajukan', [
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Urusan Keluarga',
            'tgl_mulai' => '2026-11-01',
            'tgl_selesai' => '2026-11-02',
            'durasi_hari' => 5,
            'alamat_selama_cuti' => 'Cirebon',
            'no_telp' => '08123456789',
        ])
        ->assertSessionHasErrors(['durasi_hari']);
});

test('cuti tahunan submission fails if durasi_hari exceeds total available quota', function () {
    // Total kuota River: 12 + 4 + 2 = 18 hari
    // Mencoba mengajukan 19 hari dalam rentang 30 hari -> harus gagal validasi kuota
    $this->actingAs($this->userRiver)
        ->post('/cuti/ajukan', [
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Urusan Keluarga Panjang',
            'tgl_mulai' => '2026-11-01',
            'tgl_selesai' => '2026-11-30',
            'durasi_hari' => 19,
            'alamat_selama_cuti' => 'Cirebon',
            'no_telp' => '08123456789',
        ])
        ->assertSessionHasErrors(['durasi_hari']);
});

test('ketua approves cuti when quota is exactly depleted (0, 0, 0)', function () {
    // Total kuota River: 12 + 4 + 2 = 18 hari. Durasi tepat 18 hari
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Habiskan cuti kumulatif',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-30',
        'durasi_hari' => 18,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/099/KP5.3/11/2026',
    ]);

    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Disetujui',
        ])
        ->assertRedirect(route('cuti.daftar_ketua'));

    $this->pegawaiRiver->refresh();
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(0);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe(0);
    expect($this->pegawaiRiver->sisa_cuti_n_2)->toBe(0);
});

test('ketua approval fails with 422 if quota is insufficient at approval time and balances never become negative', function () {
    // Kurangi kuota River terlebih dahulu menjadi hanya 2 hari tersisa
    $this->pegawaiRiver->update([
        'sisa_cuti_n' => 2,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan mendesak',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-05',
        'durasi_hari' => 5, // Lebih besar dari sisa total (2)
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/100/KP5.3/11/2026',
    ]);

    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Coba setujui',
        ])
        ->assertStatus(422);

    $this->pegawaiRiver->refresh();
    // Kuota tetap 2, tidak berkurang dan TIDAK PERNAH negatif
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(2);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe(0);
    expect($this->pegawaiRiver->sisa_cuti_n_2)->toBe(0);
});

test('rejection (DITOLAK) by Ketua PA does NOT deduct any leave quota', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Pribadi',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-05',
        'durasi_hari' => 4,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/101/KP5.3/11/2026',
    ]);

    $initialN = $this->pegawaiRiver->sisa_cuti_n;
    $initialN1 = $this->pegawaiRiver->sisa_cuti_n_1;
    $initialN2 = $this->pegawaiRiver->sisa_cuti_n_2;

    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DITOLAK',
            'catatan' => 'Beban pekerjaan menumpuk',
        ])
        ->assertRedirect(route('cuti.daftar_ketua'));

    $cuti->refresh();
    expect($cuti->status)->toBe('DITOLAK');

    $this->pegawaiRiver->refresh();
    // Kuota tidak berubah sama sekali
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe($initialN);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe($initialN1);
    expect($this->pegawaiRiver->sisa_cuti_n_2)->toBe($initialN2);
});

test('idempotency: repeated approval call is blocked and does NOT deduct quota twice', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Keluarga',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-05',
        'durasi_hari' => 3,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/102/KP5.3/11/2026',
    ]);

    // Panggilan pertama: Berhasil
    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'OK 1',
        ])
        ->assertRedirect(route('cuti.daftar_ketua'));

    $this->pegawaiRiver->refresh();
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(9); // 12 - 3 = 9

    // Panggilan kedua (replay / double request): Harus diblokir 400
    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'OK 2',
        ])
        ->assertStatus(400);

    $this->pegawaiRiver->refresh();
    // Kuota tetap 9, TIDAK terpotong dua kali!
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe(9);
});

test('admin cannot input duplicate nomor_surat', function () {
    // Cuti pertama sudah pakai nomor W10-A4/999/KP5.3/11/2026
    PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Cuti 1',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 2,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/999/KP5.3/11/2026',
    ]);

    // Cuti kedua mencoba memakai nomor yang sama
    $cuti2 = PengajuanCuti::create([
        'user_id' => $this->userOther->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Cuti 2',
        'tgl_mulai' => '2026-11-10',
        'tgl_selesai' => '2026-11-12',
        'durasi_hari' => 2,
        'status' => 'MENUNGGU_PENOMORAN',
    ]);

    $this->actingAs($this->userAdmin)
        ->post("/cuti/penomoran/{$cuti2->id}", [
            'nomor_surat' => 'W10-A4/999/KP5.3/11/2026', // Duplikat
        ])
        ->assertSessionHasErrors(['nomor_surat']);
});

test('stage transition integrity: steps cannot be skipped', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Cuti Baru',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 2,
        'status' => 'MENUNGGU_ATASAN', // Belum disetujui atasan
    ]);

    // Admin mencoba langsung menomori berkas yang belum disetujui atasan -> 400
    $this->actingAs($this->userAdmin)
        ->post("/cuti/penomoran/{$cuti->id}", [
            'nomor_surat' => 'W10-A4/SKIP/KP5.3/11/2026',
        ])
        ->assertStatus(400);

    // Ketua PA mencoba langsung memutuskan berkas yang belum dinomori -> 400
    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Skip langsung',
        ])
        ->assertStatus(400);
});

test('non-annual leave categories do not deduct annual leave quota', function () {
    // Ajukan Cuti Sakit 5 hari
    $cutiSakit = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Sakit',
        'alasan' => 'Sakit demam berdarah opname',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-07',
        'durasi_hari' => 5,
        'status' => 'MENUNGGU_KETUA',
        'nomor_surat' => 'W10-A4/SAKIT/KP5.3/11/2026',
    ]);

    $initialN = $this->pegawaiRiver->sisa_cuti_n;
    $initialN1 = $this->pegawaiRiver->sisa_cuti_n_1;
    $initialN2 = $this->pegawaiRiver->sisa_cuti_n_2;

    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cutiSakit->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Lekas sembuh',
        ])
        ->assertRedirect(route('cuti.daftar_ketua'));

    $cutiSakit->refresh();
    expect($cutiSakit->status)->toBe('MENUNGGU_PENOMORAN');

    $this->pegawaiRiver->refresh();
    // Kuota cuti tahunan tetap utuh
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe($initialN);
    expect($this->pegawaiRiver->sisa_cuti_n_1)->toBe($initialN1);
    expect($this->pegawaiRiver->sisa_cuti_n_2)->toBe($initialN2);
});

test('historical data integrity: pejabat penyetuju dan pengesah tersimpan immutable saat mutasi pejabat', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Pribadi',
        'tgl_mulai' => '2026-11-10',
        'tgl_selesai' => '2026-11-12',
        'durasi_hari' => 2,
        'status' => 'MENUNGGU_ATASAN',
    ]);

    // 1. Atasan Lama (Bapak Atasan) menyetujui -> MENUNGGU_KETUA
    $this->actingAs($this->userAtasan)
        ->post("/cuti/persetujuan/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'OK',
        ]);
    
    $cuti->refresh();
    expect($cuti->status)->toBe('MENUNGGU_KETUA');
    expect($cuti->disetujui_oleh_atasan_id)->toBe($this->userAtasan->id);
    expect($cuti->atasan_penyetuju->id)->toBe($this->userAtasan->id);
    expect($cuti->atasan_penyetuju->name)->toBe($this->userAtasan->name);

    // 2. Ketua Lama (Bapak Ketua PA) mengesahkan -> MENUNGGU_PENOMORAN
    $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cuti->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'ACC Final',
        ]);

    $cuti->refresh();
    expect($cuti->status)->toBe('MENUNGGU_PENOMORAN');
    expect($cuti->disahkan_oleh_ketua_id)->toBe($this->userKetua->id);
    expect($cuti->ketua_pengesah->id)->toBe($this->userKetua->id);
    expect($cuti->ketua_pengesah->name)->toBe($this->userKetua->name);

    // 3. Admin input nomor surat dinas -> SELESAI_DISETUJUI
    $this->actingAs($this->userAdmin)
        ->post("/cuti/penomoran/{$cuti->id}", [
            'nomor_surat' => 'W10-A4/999/KP5.3/11/2026',
        ]);

    $cuti->refresh();
    expect($cuti->status)->toBe('SELESAI_DISETUJUI');
    expect($cuti->nomor_surat)->toBe('W10-A4/999/KP5.3/11/2026');

    // 4. SIMULASI MUTASI PEJABAT:
    // Buat Atasan Baru dan mutasikan atasan River
    $userAtasanBaru = User::create([
        'name' => 'Atasan Baru Pengganti',
        'email' => 'atasan.baru@pa-sumber.go.id',
        'password' => Hash::make('password'),
        'role' => 'pegawai',
    ]);
    $pegawaiAtasanBaru = Pegawai::create([
        'user_id' => $userAtasanBaru->id,
        'nip' => '199999999999999999',
        'jabatan' => 'Panitera Muda Baru',
        'unit_kerja' => 'Kepaniteraan',
    ]);
    $pegawaiAtasanBaru->jabatan->update(['parent_id' => $this->pegawaiKetua->jabatan_id]);
    $this->pegawaiRiver->update(['atasan_langsung_id' => $pegawaiAtasanBaru->id]);

    // Buat Ketua Baru dan mutasikan jabatan ketua
    $userKetuaBaru = User::create([
        'name' => 'Ketua Baru Pengganti',
        'email' => 'ketua.baru@pa-sumber.go.id',
        'password' => Hash::make('password'),
        'role' => 'pegawai',
    ]);
    $pegawaiKetuaBaru = Pegawai::create([
        'user_id' => $userKetuaBaru->id,
        'nip' => '198888888888888888',
        'jabatan' => 'Ketua PA',
        'unit_kerja' => 'Pimpinan',
    ]);
    // Ubah jabatan ketua lama menjadi Hakim Utama
    $this->pegawaiKetua->update(['jabatan' => 'Hakim Utama']);
    $this->pegawaiKetua->jabatan->update(['parent_id' => $pegawaiKetuaBaru->jabatan_id]);

    // 5. Cetak PDF dokumen cuti lama
    $response = $this->actingAs($this->userRiver)
        ->get("/cuti/cetak/{$cuti->id}");
    $response->assertOk();

    // Verifikasi relasi transaksi snapshot tetap menunjuk ke Pejabat Lama
    $cutiLama = PengajuanCuti::with(['atasan_penyetuju', 'ketua_pengesah'])->find($cuti->id);
    expect($cutiLama->atasan_penyetuju->name)->toBe('Bapak Atasan');
    expect($cutiLama->atasan_penyetuju->name)->not->toBe('Atasan Baru Pengganti');
    expect($cutiLama->ketua_pengesah->name)->toBe('Bapak Ketua PA');
    expect($cutiLama->ketua_pengesah->name)->not->toBe('Ketua Baru Pengganti');
});

test('menu sidebar and approval routes respect is_atasan boolean flag', function () {
    // 1. Pegawai biasa (bukan atasan) -> tidak boleh akses /cuti/persetujuan (403)
    $this->actingAs($this->userRiver)
        ->get('/cuti/persetujuan')
        ->assertStatus(403);

    // Sidebar pegawai biasa tidak menampilkan menu "Persetujuan Cuti"
    $this->actingAs($this->userRiver)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Persetujuan Cuti')
        ->assertDontSee('Keputusan Final');

    // 2. Atasan baru yang belum memiliki bawahan tetapi is_atasan = true
    $userAtasanBaru = User::factory()->create(['role' => 'pegawai']);
    Pegawai::create([
        'user_id' => $userAtasanBaru->id,
        'nip' => '198901012015011009',
        'jabatan' => 'Kasubbag Umum dan Keuangan',
        'unit_kerja' => 'Kesekretariatan',
        'is_atasan' => true,
        'atasan_langsung_id' => $this->pegawaiKetua->id,
    ]);

    // Atasan baru dapat mengakses /cuti/persetujuan (200)
    $this->actingAs($userAtasanBaru)
        ->get('/cuti/persetujuan')
        ->assertOk();

    // Menu sidebar menampilkan sub-menu Atasan tetapi tidak menampilkan sub-menu Ketua
    $this->actingAs($userAtasanBaru)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Antrean Persetujuan')
        ->assertDontSee('Antrean Pengesahan');

    // 3. Ketua PA hanya melihat menu Pengesahan (tidak melihat Persetujuan Cuti tahap 1)
    $this->actingAs($this->userKetua)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Antrean Persetujuan')
        ->assertSee('Antrean Pengesahan');

    // Ketua PA dibatasi dari mengakses route persetujuan atasan langsung (/cuti/persetujuan -> 403)
    $this->actingAs($this->userKetua)
        ->get('/cuti/persetujuan')
        ->assertStatus(403);
});

test('riwayat persetujuan atasan, riwayat ketua, and riwayat penomoran admin routes', function () {
    $cutiSelesai = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Liburan',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 2,
        'status' => 'SELESAI_DISETUJUI',
        'nomor_surat' => 'W10-A4/TEST-RIWAYAT/KP5.3/11/2026',
        'disetujui_oleh_atasan_id' => $this->userAtasan->id,
        'disahkan_oleh_ketua_id' => $this->userKetua->id,
    ]);

    // 1. Atasan dapat mengakses riwayat persetujuan
    $this->actingAs($this->userAtasan)
        ->get('/cuti/persetujuan/riwayat')
        ->assertOk()
        ->assertSee('Riwayat Persetujuan Cuti')
        ->assertSee('River Al Farizie');

    // Non-atasan (River) tidak dapat mengakses riwayat persetujuan atasan -> 403
    $this->actingAs($this->userRiver)
        ->get('/cuti/persetujuan/riwayat')
        ->assertStatus(403);

    // 2. Ketua dapat mengakses riwayat pengesahan
    $this->actingAs($this->userKetua)
        ->get('/cuti/keputusan-ketua/riwayat')
        ->assertOk()
        ->assertSee('Riwayat Keputusan & Pengesahan')
        ->assertSee('River Al Farizie');

    // Non-ketua (Admin) tidak dapat mengakses riwayat ketua -> 403
    $this->actingAs($this->userAdmin)
        ->get('/cuti/keputusan-ketua/riwayat')
        ->assertStatus(403);

    // 3. Admin dapat mengakses riwayat penomoran arsip
    $this->actingAs($this->userAdmin)
        ->get('/cuti/penomoran/riwayat')
        ->assertOk()
        ->assertSee('Riwayat Penomoran & Arsip')
        ->assertSee('W10-A4/TEST-RIWAYAT/KP5.3/11/2026')
        ->assertSee('River Al Farizie');

    // Non-admin (River) tidak dapat mengakses riwayat penomoran -> 403
    $this->actingAs($this->userRiver)
        ->get('/cuti/penomoran/riwayat')
        ->assertStatus(403);
});

test('word and pdf download authorization: admin and applicant can download, others blocked', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan Penting',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 2,
        'status' => 'MENUNGGU_ATASAN', // Belum selesai
    ]);

    // Belum disetujui / belum dinomori -> 403
    $this->actingAs($this->userRiver)
        ->get("/cuti/download-word/{$cuti->id}")
        ->assertStatus(403);

    $cuti->status = 'SELESAI_DISETUJUI';
    $cuti->nomor_surat = 'W10-A4/WORD/KP5.3/11/2026';
    $cuti->save();

    // User lain mencoba download Word milik River -> 403
    $this->actingAs($this->userOther)
        ->get("/cuti/download-word/{$cuti->id}")
        ->assertStatus(403);

    // Pemohon asli (River) mendownload Word -> 200 ms-word
    $respRiver = $this->actingAs($this->userRiver)
        ->get("/cuti/download-word/{$cuti->id}");
    $respRiver->assertOk();
    $respRiver->assertHeader('Content-Type', 'application/msword; charset=utf-8');

    // Admin Kepegawaian mendownload Word -> 200 ms-word
    $respAdmin = $this->actingAs($this->userAdmin)
        ->get("/cuti/download-word/{$cuti->id}");
    $respAdmin->assertOk();
    $respAdmin->assertHeader('Content-Type', 'application/msword; charset=utf-8');
});

test('direct subordinate of Ketua (e.g. Hakim) submits cuti directly to MENUNGGU_KETUA and Ketua can approve', function () {
    // Buat Jabatan Hakim yang berinduk langsung ke Ketua
    $jabatanKetua = \App\Models\Jabatan::where('nama_jabatan', 'Ketua PA')->first();
    $jabatanHakim = \App\Models\Jabatan::create([
        'nama_jabatan' => 'Hakim Test Direct',
        'parent_id' => $jabatanKetua ? $jabatanKetua->id : $this->pegawaiKetua->jabatan_id,
        'is_struktural' => false,
    ]);

    $userHakim = User::factory()->create([
        'name' => 'Bapak Hakim Pengadilan',
        'email' => 'hakim_test_direct@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $pegawaiHakim = Pegawai::create([
        'user_id' => $userHakim->id,
        'nip' => '197505052000031005',
        'jabatan_id' => $jabatanHakim->id,
        'tanggal_masuk' => '2005-01-01',
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    // Hakim submit cuti -> langsung berstatus MENUNGGU_KETUA (tanpa lewat MENUNGGU_ATASAN)
    $response = $this->actingAs($userHakim)
        ->post('/cuti/ajukan', [
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Sidang di luar kota & urusan dinas',
            'tgl_mulai' => '2026-11-10',
            'tgl_selesai' => '2026-11-12',
            'durasi_hari' => 3,
            'alamat_selama_cuti' => 'Bandung',
            'no_telp' => '081234567890',
        ]);

    $response->assertRedirect(route('cuti.riwayat'));
    $response->assertSessionHas('success');

    $cutiHakim = PengajuanCuti::where('user_id', $userHakim->id)->latest()->first();
    expect($cutiHakim)->not->toBeNull();
    expect($cutiHakim->status)->toBe('MENUNGGU_KETUA');

    // Ketua melihat permohonan ini di daftar antrean Ketua
    $this->actingAs($this->userKetua)
        ->get('/cuti/keputusan-ketua')
        ->assertOk()
        ->assertSee('Bapak Hakim Pengadilan')
        ->assertSee('Langsung ke Ketua');

    // Ketua membuka halaman tinjau
    $this->actingAs($this->userKetua)
        ->get("/cuti/keputusan-ketua/{$cutiHakim->id}")
        ->assertOk()
        ->assertSee('Bapak Hakim Pengadilan');

    // Ketua menyetujui (ACC)
    $prosesResp = $this->actingAs($this->userKetua)
        ->post("/cuti/keputusan-ketua/{$cutiHakim->id}", [
            'keputusan' => 'DISETUJUI',
            'catatan' => 'Disetujui untuk dinas dan cuti',
        ]);

    $prosesResp->assertRedirect('/cuti/keputusan-ketua');
    $cutiHakim->refresh();
    expect($cutiHakim->status)->toBe('MENUNGGU_PENOMORAN');
    expect($cutiHakim->disahkan_oleh_ketua_id)->toBe($this->userKetua->id);
    expect($cutiHakim->disetujui_oleh_atasan_id)->toBe($this->userKetua->id);

    // Saldo cuti Hakim terpotong 12 - 3 = 9
    $pegawaiHakim->refresh();
    expect($pegawaiHakim->sisa_cuti_n)->toBe(9);
});


