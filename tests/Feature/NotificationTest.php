<?php

use App\Models\User;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Notifications\CutiWebPushNotification;

beforeEach(function () {
    $this->userAtasan = User::factory()->create([
        'name' => 'Bapak Atasan',
        'email' => 'atasan_notif_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
        'notif_enabled' => true,
    ]);
    $this->pegawaiAtasan = Pegawai::create([
        'user_id' => $this->userAtasan->id,
        'nip' => '19800101' . rand(100000000, 999999999),
        'jabatan' => 'Panitera Muda Gugatan',
        'unit_kerja' => 'Kepaniteraan',
        'tanggal_masuk' => '2015-01-01',
        'atasan_langsung_id' => null,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 6,
        'sisa_cuti_n_2' => 0,
    ]);
    $jabatanKetua = \App\Models\Jabatan::firstOrCreate(['nama_jabatan' => 'Ketua PA'], ['parent_id' => null, 'is_struktural' => true]);
    $this->pegawaiAtasan->jabatan->update(['parent_id' => $jabatanKetua->id]);

    $this->userPegawai = User::factory()->create([
        'name' => 'Ahmad Pegawai',
        'email' => 'pegawai_notif_' . uniqid() . '@pa-sumber.go.id',
        'role' => 'pegawai',
        'notif_enabled' => true,
    ]);
    $this->pegawaiBawahan = Pegawai::create([
        'user_id' => $this->userPegawai->id,
        'nip' => '19950505' . rand(100000000, 999999999),
        'jabatan' => 'Staf Pelaksana',
        'unit_kerja' => 'Kepaniteraan',
        'tanggal_masuk' => '2020-01-01',
        'atasan_langsung_id' => $this->pegawaiAtasan->id,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ]);
});

test('user dapat mengakses halaman riwayat notifikasi dan melihat daftar notifikasi', function () {
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Status Permohonan Disetujui',
        body: 'Cuti tahunan Anda telah disetujui atasan langsung.',
        actionUrl: '/cuti/riwayat',
        type: 'success'
    ));

    $response = $this->actingAs($this->userPegawai)->get(route('notifications.index'));
    $response->assertStatus(200);
    $response->assertSee('Riwayat Notifikasi');
    $response->assertSee('Status Permohonan Disetujui');
    $response->assertSee('Cuti tahunan Anda telah disetujui atasan langsung.');
});

test('endpoint JSON notifikasi mengembalikan unread count dan notif_enabled dengan benar', function () {
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Notifikasi Uji Coba',
        body: 'Isi pesan uji coba',
        actionUrl: '/cuti/riwayat',
        type: 'info'
    ));

    $response = $this->actingAs($this->userPegawai)->getJson(route('notifications.index'));
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'unread_count' => 1,
        'notif_enabled' => true,
    ]);
    $response->assertJsonFragment([
        'title' => 'Notifikasi Uji Coba',
        'body' => 'Isi pesan uji coba',
    ]);
});

test('user dapat menandai notifikasi sebagai dibaca dan menandai semua dibaca', function () {
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Pesan 1',
        body: 'Body 1',
    ));
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Pesan 2',
        body: 'Body 2',
    ));

    $this->assertEquals(2, $this->userPegawai->unreadNotifications()->count());

    $notif1 = $this->userPegawai->unreadNotifications()->first();
    $response = $this->actingAs($this->userPegawai)->postJson(route('notifications.read', $notif1->id));
    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'unread_count' => 1]);

    // Tandai semua dibaca
    $responseAll = $this->actingAs($this->userPegawai)->postJson(route('notifications.readAll'));
    $responseAll->assertStatus(200);
    $responseAll->assertJson(['success' => true, 'unread_count' => 0]);
    $this->assertEquals(0, $this->userPegawai->unreadNotifications()->count());
});

test('user dapat mengubah setting notifikasi ON dan OFF', function () {
    $this->assertTrue((bool)$this->userPegawai->notif_enabled);

    // Toggle ke OFF
    $responseOff = $this->actingAs($this->userPegawai)->postJson(route('notifications.toggle'), [
        'enabled' => false,
    ]);
    $responseOff->assertStatus(200);
    $responseOff->assertJson(['success' => true, 'notif_enabled' => false]);
    $this->assertFalse((bool)$this->userPegawai->fresh()->notif_enabled);

    // Saat notif_enabled bernilai false, pengiriman notifikasi baru ditiadakan (muted)
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Pesan Muted',
        body: 'Pesan ini tidak boleh masuk karena setting OFF',
    ));
    $this->assertEquals(0, $this->userPegawai->fresh()->notifications()->count());

    // Toggle kembali ke ON
    $responseOn = $this->actingAs($this->userPegawai)->postJson(route('notifications.toggle'), [
        'enabled' => true,
    ]);
    $responseOn->assertStatus(200);
    $responseOn->assertJson(['success' => true, 'notif_enabled' => true]);
    $this->assertTrue((bool)$this->userPegawai->fresh()->notif_enabled);

    // Sekarang pengiriman notifikasi kembali masuk
    $this->userPegawai->notify(new CutiWebPushNotification(
        title: 'Pesan Aktif',
        body: 'Pesan ini berhasil masuk',
    ));
    $this->assertEquals(1, $this->userPegawai->fresh()->notifications()->count());
});

test('user dapat menghapus satu notifikasi dan membersihkan seluruh riwayat', function () {
    $this->userPegawai->notify(new CutiWebPushNotification(title: 'Notif A', body: 'Body A'));
    $this->userPegawai->notify(new CutiWebPushNotification(title: 'Notif B', body: 'Body B'));

    $this->assertEquals(2, $this->userPegawai->notifications()->count());

    $first = $this->userPegawai->notifications()->first();
    $this->actingAs($this->userPegawai)->deleteJson(route('notifications.destroy', $first->id))
        ->assertStatus(200);
    $this->assertEquals(1, $this->userPegawai->notifications()->count());

    $this->actingAs($this->userPegawai)->postJson(route('notifications.clear'))
        ->assertStatus(200);
    $this->assertEquals(0, $this->userPegawai->notifications()->count());
});

test('pengajuan cuti baru otomatis mencatat riwayat notifikasi di database untuk atasan langsung', function () {
    $this->actingAs($this->userPegawai)->post(route('cuti.store'), [
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Ada acara pernikahan keluarga',
        'tgl_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tgl_selesai' => now()->addDays(4)->format('Y-m-d'),
        'durasi_hari' => 3,
        'alamat_selama_cuti' => 'Jl. Cirebon No. 10',
        'no_telp' => '081234567890',
    ])->assertRedirect(route('cuti.riwayat'));

    // Cek apakah atasan langsung menerima notifikasi di database
    $atasanNotifs = $this->userAtasan->notifications()->get();
    $this->assertCount(1, $atasanNotifs);
    $this->assertStringContainsString('Permohonan Cuti Baru Masuk', $atasanNotifs->first()->data['title']);
    $this->assertStringContainsString($this->userPegawai->name, $atasanNotifs->first()->data['body']);
    $this->assertEquals('/cuti/persetujuan', $atasanNotifs->first()->data['action_url']);
});

test('halaman profile dan notifikasi merender validasi perizinan browser dan banner mismatch', function () {
    // 1. Profile edit view renders notificationPreferenceManager and mismatch warning texts
    $responseProfile = $this->actingAs($this->userPegawai)->get(route('profile.edit'));
    $responseProfile->assertOk();
    $responseProfile->assertSee('notificationPreferenceManager');
    $responseProfile->assertSee('Pemberitahuan Aktif di sistem, namun diblokir oleh browser');
    $responseProfile->assertSee('STATUS: DIBLOKIR BROWSER');
    $responseProfile->assertSee('Gagal mengaktifkan. Anda harus memberikan izin notifikasi di popup browser terlebih dahulu');

    // 2. Notifications index view renders notificationPreferenceManager and mismatch warning texts
    $responseNotif = $this->actingAs($this->userPegawai)->get(route('notifications.index'));
    $responseNotif->assertOk();
    $responseNotif->assertSee('notificationPreferenceManager');
    $responseNotif->assertSee('Pemberitahuan Aktif di sistem, namun diblokir oleh browser');
    $responseNotif->assertSee('STATUS: DIBLOKIR BROWSER');

    // 3. Dashboard renders mismatch warning for users with notif_enabled = true
    $responseDash = $this->actingAs($this->userPegawai)->get(route('dashboard'));
    $responseDash->assertOk();
    $responseDash->assertSee('Pemberitahuan Aktif di sistem, namun diblokir oleh browser');
});
