<?php

use App\Models\User;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;

beforeEach(function () {
    $this->userAtasan = User::factory()->create([
        'name' => 'Bapak Atasan',
        'email' => 'atasan_test_' . uniqid() . '@pa-sumber.go.id',
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

    $this->userRiver = User::factory()->create([
        'name' => 'River Al Farizie',
        'email' => 'river_test_' . uniqid() . '@pa-sumber.go.id',
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

    $this->userLain = User::factory()->create([
        'name' => 'Pegawai Lain',
        'email' => 'lain_test_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
});

test('pegawai dapat melihat tombol Perbaiki Pengajuan saat status Revisi Atasan', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Ada keperluan keluarga',
        'tgl_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tgl_selesai' => now()->addDays(4)->format('Y-m-d'),
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Cirebon',
        'no_telp' => '08123456789',
        'status' => 'REVISI_ATASAN',
        'catatan_atasan' => 'Tolong sesuaikan kembali tanggal selesai.',
    ]);

    $response = $this->actingAs($this->userRiver)->get(route('cuti.riwayat'));
    $response->assertStatus(200);
    $response->assertSee('Perbaiki Pengajuan');
    $response->assertSee(route('cuti.edit', $cuti->id));
    $response->assertSee('Tarik Permohonan');
    $response->assertSee('Tolong sesuaikan kembali tanggal selesai.');
});

test('hanya pemilik pengajuan yang dapat mengakses halaman edit cuti dengan status revisi', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Ada acara keluarga',
        'tgl_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tgl_selesai' => now()->addDays(4)->format('Y-m-d'),
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Sumber, Cirebon',
        'no_telp' => '08123456789',
        'status' => 'REVISI_ATASAN',
        'catatan_atasan' => 'Perbaiki alasan cuti lebih spesifik.',
    ]);

    // Akses oleh user lain ditolak (403)
    $this->actingAs($this->userLain)->get(route('cuti.edit', $cuti->id))->assertStatus(403);

    // Akses oleh pemilik berhasil dan memunculkan Alert Box catatan revisi
    $response = $this->actingAs($this->userRiver)->get(route('cuti.edit', $cuti->id));
    $response->assertStatus(200);
    $response->assertSee('Perbaiki Pengajuan Cuti');
    $response->assertSee('bg-amber-100 border-l-4 border-amber-500 text-amber-700', false);
    $response->assertSee('Perbaiki alasan cuti lebih spesifik.');
    $response->assertSee('Ada acara keluarga');
    $response->assertSee('Sumber, Cirebon');
});

test('proses update pengajuan mereset status menjadi MENUNGGU_ATASAN dan mengosongkan catatan atasan', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Alasan lama yang kurang jelas',
        'tgl_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tgl_selesai' => now()->addDays(3)->format('Y-m-d'),
        'durasi_hari' => 2,
        'alamat_selama_cuti' => 'Alamat lama',
        'no_telp' => '08123456789',
        'status' => 'REVISI_ATASAN',
        'catatan_atasan' => 'Perjelas alasan!',
        'catatan_revisi' => 'Perjelas alasan!',
    ]);

    $tglMulaiBaru = now()->addDays(3)->format('Y-m-d');
    $tglSelesaiBaru = now()->addDays(4)->format('Y-m-d');

    $response = $this->actingAs($this->userRiver)->put(route('cuti.update', $cuti->id), [
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Menghadiri wisuda adik kandung di Bandung',
        'tgl_mulai' => $tglMulaiBaru,
        'tgl_selesai' => $tglSelesaiBaru,
        'durasi_hari' => 2,
        'alamat_selama_cuti' => 'Jl. Dipati Ukur No. 10 Bandung',
        'no_telp' => '081299998888',
    ]);

    $response->assertRedirect(route('cuti.riwayat'));
    $response->assertSessionHas('success');

    $cutiFresh = $cuti->fresh();
    expect($cutiFresh->alasan)->toBe('Menghadiri wisuda adik kandung di Bandung');
    expect($cutiFresh->status)->toBe('MENUNGGU_ATASAN');
    expect($cutiFresh->catatan_atasan)->toBeNull();
    expect($cutiFresh->catatan_revisi)->toBeNull();
});

test('fitur tarik permohonan berhasil membatalkan cuti tanpa mengubah saldo sisa_cuti_n pegawai', function () {
    $cuti = PengajuanCuti::create([
        'user_id' => $this->userRiver->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Keperluan mendesak',
        'tgl_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tgl_selesai' => now()->addDays(4)->format('Y-m-d'),
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Cirebon',
        'no_telp' => '08123456789',
        'status' => 'MENUNGGU_ATASAN',
    ]);

    $sisaAwal = $this->pegawaiRiver->sisa_cuti_n; // 10

    // User lain tidak bisa tarik (403)
    $this->actingAs($this->userLain)->patch(route('cuti.tarik', $cuti->id))->assertStatus(403);

    // Pemilik cuti menarik permohonan
    $response = $this->actingAs($this->userRiver)->patch(route('cuti.tarik', $cuti->id));
    $response->assertRedirect(route('cuti.riwayat'));
    $response->assertSessionHas('success');

    $cutiFresh = $cuti->fresh();
    expect($cutiFresh->status)->toBe('Dibatalkan Pegawai');

    // Saldo cuti tidak berubah karena belum pernah dipotong pada tahap Menunggu Atasan
    $this->pegawaiRiver->refresh();
    expect($this->pegawaiRiver->sisa_cuti_n)->toBe($sisaAwal);
});
