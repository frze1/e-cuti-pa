<?php

use App\Models\User;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use Carbon\Carbon;

beforeEach(function () {
    // 1. Atasan Panitera Muda (Atasan Langsung River)
    $this->userAtasan = User::factory()->create([
        'name' => 'Bapak Atasan',
        'email' => 'atasan_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiAtasan = Pegawai::create([
        'user_id' => $this->userAtasan->id,
        'nip' => '19800101' . rand(100000000, 999999999),
        'jabatan' => 'Panitera Muda',
        'unit_kerja' => 'Kepaniteraan',
        'tanggal_masuk' => '2015-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 6,
        'sisa_cuti_n_2' => 0,
    ]);

    // 2. Pegawai Biasa (River) - Bawahan dari $this->pegawaiAtasan
    $this->userRiver = User::factory()->create([
        'name' => 'River Al Farizie',
        'email' => 'river_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiRiver = Pegawai::create([
        'user_id' => $this->userRiver->id,
        'nip' => '19980202' . rand(100000000, 999999999),
        'jabatan' => 'Staf IT',
        'unit_kerja' => 'Kesekretariatan',
        'tanggal_masuk' => '2022-01-01',
        'atasan_langsung_id' => $this->pegawaiAtasan->id,
        'sisa_cuti_n' => 10,
        'sisa_cuti_n_1' => 4,
        'sisa_cuti_n_2' => 2,
    ]);

    // 3. Pegawai Biasa Lain (Yogi)
    $this->userYogi = User::factory()->create([
        'name' => 'Yogi Saputra',
        'email' => 'yogi_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiYogi = Pegawai::create([
        'user_id' => $this->userYogi->id,
        'nip' => '19950303' . rand(100000000, 999999999),
        'jabatan' => 'Staff',
        'unit_kerja' => 'Kesekretariatan',
        'tanggal_masuk' => '2020-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    // 4. Admin Kepegawaian
    $this->userAdmin = User::factory()->create([
        'name' => 'Admin Kepegawaian',
        'email' => 'admin_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'admin',
    ]);
    $this->pegawaiAdmin = Pegawai::create([
        'user_id' => $this->userAdmin->id,
        'nip' => '19850505' . rand(100000000, 999999999),
        'jabatan' => 'Kasubbag Kepegawaian',
        'unit_kerja' => 'Kepegawaian',
        'tanggal_masuk' => '2012-09-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    // 5. Ketua PA
    $this->userKetua = User::factory()->create([
        'name' => 'Ketua Pengadilan Agama',
        'email' => 'ketua_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->pegawaiKetua = Pegawai::create([
        'user_id' => $this->userKetua->id,
        'nip' => '19700303' . rand(100000000, 999999999),
        'jabatan' => 'Ketua PA',
        'unit_kerja' => 'Pimpinan',
        'tanggal_masuk' => '2001-03-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);

    $this->pegawaiAtasan->jabatan->update(['parent_id' => $this->pegawaiKetua->jabatan_id]);
});

test('tamu tidak dapat mengakses halaman kalender cuti', function () {
    $response = $this->get('/kalender-cuti');
    $response->assertRedirect('/login');
});

test('pegawai biasa dan atasan langsung tidak dapat mengakses halaman kalender cuti', function () {
    // 1. Pegawai biasa
    $this->actingAs($this->userRiver)->get('/kalender-cuti')->assertStatus(403);
    
    // 2. Atasan langsung
    $this->actingAs($this->userAtasan)->get('/kalender-cuti')->assertStatus(403);
});

test('non-admin tidak dapat mengakses endpoint api json kalender events', function () {
    $this->actingAs($this->userRiver)->getJson('/kalender-cuti/events')->assertStatus(403);
    $this->actingAs($this->userAtasan)->getJson('/kalender-cuti/events')->assertStatus(403);
});

test('ketua atau pimpinan tertinggi dapat mengakses halaman dan api kalender cuti', function () {
    expect($this->userKetua->is_pimpinan_final)->toBeTrue();

    $this->actingAs($this->userKetua)
        ->get('/kalender-cuti')
        ->assertOk()
        ->assertSee('Kalender Cuti Aparatur');

    $this->getJson('/kalender-cuti/events')->assertOk();
});

test('admin kepegawaian dapat mengakses halaman kalender cuti', function () {
    $response = $this->actingAs($this->userAdmin)->get('/kalender-cuti');
    $response->assertStatus(200);
    $response->assertSee('Kalender Cuti Aparatur');
    $response->assertSee('Detail Pengajuan Cuti');
    $response->assertSee('Alamat Selama Menjalankan Cuti');
    $response->assertSee('Nomor Telepon / WhatsApp');
});

test('admin kepegawaian dapat mengakses api kalender events', function () {
    $cutiAcc = PengajuanCuti::create([
        'user_id' => $this->userYogi->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Liburan keluarga ke Dieng',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Dieng',
        'no_telp' => '0811223344',
        'status' => 'SELESAI_DISETUJUI',
    ]);

    $cutiPending = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Sakit',
        'alasan' => 'Rawat inap RS',
        'tgl_mulai' => '2026-11-05',
        'tgl_selesai' => '2026-11-06',
        'durasi_hari' => 2,
        'alamat_selama_cuti' => 'Cirebon',
        'no_telp' => '0812345678',
        'status' => 'MENUNGGU_ATASAN',
    ]);

    $response = $this->actingAs($this->userAdmin)->getJson('/kalender-cuti/events');
    $response->assertStatus(200);

    $data = $response->json();
    $ids = collect($data)->pluck('id')->all();

    expect($ids)->toContain($cutiAcc->id);
    expect($ids)->not->toContain($cutiPending->id);

    $eventYogi = collect($data)->firstWhere('id', $cutiAcc->id);
    expect($eventYogi['extendedProps']['can_view_alasan'])->toBeTrue();
    expect($eventYogi['extendedProps']['alasan'])->toBe('Liburan keluarga ke Dieng');
    expect($eventYogi['extendedProps']['alamat_selama_cuti'])->toBe('Dieng');
    expect($eventYogi['extendedProps']['no_telp'])->toBe('0811223344');
});

test('menu sidebar kalender cuti tampil untuk admin dan ketua saja', function () {
    // Pegawai biasa tidak melihat menu Kalender Cuti di navigasi
    $respRiver = $this->actingAs($this->userRiver)->get('/dashboard');
    $respRiver->assertStatus(200);
    $respRiver->assertDontSee('href="' . route('cuti.kalender') . '"', false);

    // Atasan langsung yang bukan pimpinan tertinggi juga tidak melihat menu
    $respAtasan = $this->actingAs($this->userAtasan)->get('/dashboard');
    $respAtasan->assertStatus(200);
    $respAtasan->assertDontSee('href="' . route('cuti.kalender') . '"', false);

    // Admin melihat menu Kalender Cuti di navigasi
    $respAdmin = $this->actingAs($this->userAdmin)->get('/dashboard');
    $respAdmin->assertStatus(200);
    $respAdmin->assertSee('href="' . route('cuti.kalender') . '"', false);
    $respAdmin->assertSee('Kalender Cuti');

    // Ketua PA juga melihat menu Kalender Cuti
    $respKetua = $this->actingAs($this->userKetua)->get('/dashboard');
    $respKetua->assertStatus(200);
    $respKetua->assertSee('href="' . route('cuti.kalender') . '"', false);
    $respKetua->assertSee('Kalender Cuti');
});

test('dashboard hanya menampilkan widget aparatur sedang cuti hari ini untuk admin', function () {
    $today = Carbon::today()->format('Y-m-d');

    // Buat cuti disetujui yang aktif hari ini untuk Yogi
    PengajuanCuti::create([
        'user_id' => $this->userYogi->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Liburan keluarga ke Yogyakarta',
        'tgl_mulai' => Carbon::today()->subDay()->format('Y-m-d'),
        'tgl_selesai' => Carbon::today()->addDay()->format('Y-m-d'),
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Yogyakarta',
        'no_telp' => '0811223344',
        'status' => 'SELESAI_DISETUJUI',
    ]);

    // Pegawai biasa (River) TIDAK BISA melihat widget cuti aparatur di dashboard
    $respRiver = $this->actingAs($this->userRiver)->get('/dashboard');
    $respRiver->assertStatus(200);
    $respRiver->assertDontSee('Aparatur Sedang Cuti Hari Ini');
    $respRiver->assertDontSee('Kalender Lengkap');
    $respRiver->assertDontSee('Yogi Saputra');

    // Admin BISA melihat informasi siapa yang sedang cuti hari ini di dashboard
    $respAdmin = $this->actingAs($this->userAdmin)->get('/dashboard');
    $respAdmin->assertStatus(200);
    $respAdmin->assertSee('Aparatur Sedang Cuti Hari Ini');
    $respAdmin->assertSee('Kalender Lengkap');
    $respAdmin->assertSee('Yogi Saputra');
});
