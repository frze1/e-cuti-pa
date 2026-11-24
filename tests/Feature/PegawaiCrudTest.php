<?php

use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Admin user
    $this->adminUser = User::factory()->create([
        'name' => 'Admin Kepegawaian',
        'email' => 'admin.kepegawaian@pa-sumber.go.id',
        'role' => 'admin',
    ]);
    $this->adminPegawai = Pegawai::create([
        'user_id' => $this->adminUser->id,
        'nip' => '198001012005011001',
        'jabatan' => 'Kasubbag Kepegawaian',
        'unit_kerja' => 'Kesekretariatan',
        'sisa_cuti_n' => 12,
    ]);

    // Regular employee user
    $this->regularUser = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->regularPegawai = Pegawai::create([
        'user_id' => $this->regularUser->id,
        'nip' => '199002022015021002',
        'jabatan' => 'Panitera Pengganti',
        'unit_kerja' => 'Kepaniteraan',
        'sisa_cuti_n' => 12,
    ]);
});

test('non-admin user cannot access pegawai index', function () {
    $response = $this->actingAs($this->regularUser)->get(route('pegawai.index'));
    $response->assertForbidden();
});

test('non-admin user cannot store new pegawai', function () {
    $response = $this->actingAs($this->regularUser)->post(route('pegawai.store'), [
        'name' => 'Test Baru',
        'email' => 'baru@pa-sumber.go.id',
        'role' => 'pegawai',
        'jabatan' => 'Staf',
        'sisa_cuti_n' => 12,
    ]);
    $response->assertForbidden();
});

test('admin can access pegawai index and see list', function () {
    $response = $this->actingAs($this->adminUser)->get(route('pegawai.index'));
    $response->assertOk();
    $response->assertSee('Admin Kepegawaian');
    $response->assertSee('Budi Santoso');
    $response->assertSee('Kasubbag Kepegawaian');
    $response->assertSee('Panitera Pengganti');
});

test('admin can view create pegawai form with tanggal masuk and no password field', function () {
    $response = $this->actingAs($this->adminUser)->get(route('pegawai.create'));
    $response->assertOk();
    $response->assertSee('Tambah Pegawai & Akun Baru');
    $response->assertSee('Tanggal Masuk / TMT');
    $response->assertSee('Kata sandi awal akun ini akan otomatis diatur menjadi <i>password</i>.', false);
    $response->assertDontSee('name="password"', false);
});

test('admin can create a new pegawai with default password and tanggal masuk', function () {
    $data = [
        'name' => 'Siti Aminah, S.H.I.',
        'email' => 'siti.aminah@pa-sumber.go.id',
        'role' => 'pegawai',
        'nip' => '199505052020122003',
        'jabatan' => 'Jurusita',
        'unit_kerja' => 'Kepaniteraan',
        'tanggal_masuk' => '2020-12-01',
        'atasan_langsung_id' => $this->regularPegawai->id,
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 6,
        'sisa_cuti_n_2' => 0,
    ];

    $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $data);

    $response->assertRedirect(route('pegawai.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Siti Aminah, S.H.I.',
        'email' => 'siti.aminah@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);

    $this->assertDatabaseHas('pegawais', [
        'nip' => '199505052020122003',
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 6,
    ]);

    $this->assertDatabaseHas('jabatans', [
        'nama_jabatan' => 'Jurusita',
    ]);

    $createdPegawai = Pegawai::where('nip', '199505052020122003')->first();
    expect($createdPegawai->tanggal_masuk->format('Y-m-d'))->toBe('2020-12-01');

    $createdUser = User::where('email', 'siti.aminah@pa-sumber.go.id')->first();
    // Default password should be 'password'
    expect(Hash::check('password', $createdUser->password))->toBeTrue();
    // Masa kerja should be calculated dynamically
    expect($createdUser->pegawai->masa_kerja)->toContain('Tahun');
});

test('admin can create pegawai with string null as atasan_langsung_id for pimpinan utama', function () {
    $data = [
        'name' => 'Drs. H. Pimpinan Utama, M.H.',
        'email' => 'pimpinan@pa-sumber.go.id',
        'role' => 'pegawai',
        'nip' => '196501011990031001',
        'jabatan' => 'Ketua Pengadilan Agama',
        'unit_kerja' => 'Pimpinan',
        'tanggal_masuk' => '1990-03-01',
        'sisa_cuti_n' => 12,
        'sisa_cuti_n_1' => 0,
        'sisa_cuti_n_2' => 0,
    ];

    $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $data);
    $response->assertRedirect(route('pegawai.index'));

    $pegawai = Pegawai::where('nip', '196501011990031001')->first();
    expect($pegawai)->not->toBeNull();
    expect($pegawai->atasan)->toBeNull();
});

test('admin can view edit pegawai form', function () {
    $response = $this->actingAs($this->adminUser)->get(route('pegawai.edit', $this->regularPegawai->id));
    $response->assertOk();
    $response->assertSee('Perbarui Data Pegawai & Akun');
    $response->assertSee('Budi Santoso');
    $response->assertSee('budi.santoso@pa-sumber.go.id');
    $response->assertSee('Tanggal Masuk / TMT');
});

test('admin can update pegawai with tanggal_masuk without changing password', function () {
    $oldPasswordHash = $this->regularUser->password;

    $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), [
        'name' => 'Budi Santoso, S.H.',
        'email' => 'budi.santoso.updated@pa-sumber.go.id',
        'password' => '', // optional, empty means unchanged
        'role' => 'pegawai',
        'nip' => '199002022015021002',
        'jabatan' => 'Panitera Muda Gugatan',
        'unit_kerja' => 'Kepaniteraan',
        'tanggal_masuk' => '2015-02-01',
        'atasan_langsung_id' => $this->adminPegawai->id,
        'sisa_cuti_n' => 10,
        'sisa_cuti_n_1' => 4,
        'sisa_cuti_n_2' => 0,
    ]);

    $response->assertRedirect(route('pegawai.index'));
    $response->assertSessionHas('success');

    $this->regularUser->refresh();
    $this->regularPegawai->refresh();

    expect($this->regularUser->name)->toBe('Budi Santoso, S.H.');
    expect($this->regularUser->email)->toBe('budi.santoso.updated@pa-sumber.go.id');
    expect($this->regularUser->password)->toBe($oldPasswordHash); // unchanged
    expect($this->regularPegawai->jabatan->nama_jabatan)->toBe('Panitera Muda Gugatan');
    expect($this->regularPegawai->tanggal_masuk->format('Y-m-d'))->toBe('2015-02-01');
    expect($this->regularPegawai->sisa_cuti_n)->toBe(10);
    expect($this->regularPegawai->masa_kerja)->toContain('Tahun');
});

test('admin can update pegawai without atasan fields', function () {
    $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), [
        'name' => 'Budi Santoso Diperbarui',
        'email' => 'budi.santoso@pa-sumber.go.id',
        'role' => 'pegawai',
        'jabatan' => 'Panitera Pengganti',
        'sisa_cuti_n' => 12,
    ]);

    $response->assertRedirect(route('pegawai.index'));
    expect($this->regularPegawai->fresh()->user->name)->toBe('Budi Santoso Diperbarui');
});

test('admin cannot delete their own account', function () {
    $response = $this->actingAs($this->adminUser)->delete(route('pegawai.destroy', $this->adminPegawai->id));

    $response->assertRedirect(route('pegawai.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    $this->assertDatabaseHas('pegawais', ['id' => $this->adminPegawai->id]);
});

test('admin can delete other pegawai and cascaded user', function () {
    $response = $this->actingAs($this->adminUser)->delete(route('pegawai.destroy', $this->regularPegawai->id));

    $response->assertRedirect(route('pegawai.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $this->regularUser->id]);
    $this->assertDatabaseMissing('pegawais', ['id' => $this->regularPegawai->id]);
});

test('masa kerja calculation is active when tanggal_keluar is null and freezes when tanggal_keluar is set', function () {
    // 1. Pegawai Aktif: Masuk 2020-01-01, tanggal_keluar null -> masa kerja dinamis s.d. now
    $pegawaiAktif = Pegawai::create([
        'user_id' => $this->regularUser->id,
        'nip' => '199001012020011001',
        'jabatan' => 'Analis Perkara',
        'tanggal_masuk' => '2020-01-01',
        'status_pegawai' => 'Aktif',
        'tanggal_keluar' => null,
    ]);

    expect($pegawaiAktif->masa_kerja)->toContain('Tahun');

    // 2. Pegawai Keluar/Pensiun: Masuk 2015-01-01, Keluar 2020-01-01 (Tepat 5 Tahun)
    $userPensiun = User::factory()->create(['name' => 'Pensiunan A']);
    $pegawaiPensiun = Pegawai::create([
        'user_id' => $userPensiun->id,
        'nip' => '196001011985011001',
        'jabatan' => 'Panitera',
        'tanggal_masuk' => '2015-01-01',
        'status_pegawai' => 'Pensiun',
        'tanggal_keluar' => '2020-01-01',
    ]);

    // Selisih antara 2015-01-01 dan 2020-01-01 tepat 5 Tahun 0 Bulan
    expect($pegawaiPensiun->masa_kerja)->toBe('5 Tahun');
});

test('pegawai index defaults to status_pegawai Aktif only and supports status filtering', function () {
    // Buat pegawai pensiun dan pegawai mutasi
    $userPensiun = User::factory()->create(['name' => 'Bapak Pensiun']);
    $pegawaiPensiun = Pegawai::create([
        'user_id' => $userPensiun->id,
        'nip' => '196201011985011002',
        'jabatan' => 'Panitera Pensiun',
        'status_pegawai' => 'Pensiun',
        'tanggal_keluar' => '2024-05-01',
    ]);

    $userMutasi = User::factory()->create(['name' => 'Ibu Mutasi']);
    $pegawaiMutasi = Pegawai::create([
        'user_id' => $userMutasi->id,
        'nip' => '198501012010012005',
        'jabatan' => 'Kasubbag Mutasi',
        'status_pegawai' => 'Mutasi',
        'tanggal_keluar' => '2025-01-01',
    ]);

    // 1. Akses default tanpa query param -> Hanya menampilkan pegawai Aktif
    $responseDefault = $this->actingAs($this->adminUser)->get(route('pegawai.index'));
    $responseDefault->assertOk();
    $responseDefault->assertSee('Admin Kepegawaian');
    $responseDefault->assertSee('Budi Santoso');
    $responseDefault->assertDontSee('Bapak Pensiun');
    $responseDefault->assertDontSee('Ibu Mutasi');

    // 2. Filter status=Pensiun -> Hanya menampilkan pegawai Pensiun
    $responsePensiun = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['status' => 'Pensiun']));
    $responsePensiun->assertOk();
    $responsePensiun->assertSee('Bapak Pensiun');
    $responsePensiun->assertDontSee('Budi Santoso');
    $responsePensiun->assertDontSee('Ibu Mutasi');

    // 3. Filter status=semua -> Menampilkan semua pegawai
    $responseSemua = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['status' => 'semua']));
    $responseSemua->assertOk();
    $responseSemua->assertSee('Admin Kepegawaian');
    $responseSemua->assertSee('Budi Santoso');
    $responseSemua->assertSee('Bapak Pensiun');
    $responseSemua->assertSee('Ibu Mutasi');
});

test('admin can update pegawai status to Pensiun with tanggal_keluar', function () {
    $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $this->regularPegawai->id), [
        'name' => 'Budi Santoso Pensiun',
        'email' => 'budi.santoso@pa-sumber.go.id',
        'role' => 'pegawai',
        'nip' => $this->regularPegawai->nip,
        'jabatan' => $this->regularPegawai->jabatan,
        'tanggal_masuk' => '2010-01-01',
        'status_pegawai' => 'Pensiun',
        'tanggal_keluar' => '2025-01-01',
        'sisa_cuti_n' => 0,
    ]);

    $response->assertRedirect(route('pegawai.index'));
    $this->regularPegawai->refresh();

    expect($this->regularPegawai->status_pegawai)->toBe('Pensiun');
    expect($this->regularPegawai->tanggal_keluar->format('Y-m-d'))->toBe('2025-01-01');
    // Masa kerja terkunci antara 2010-01-01 dan 2025-01-01 (15 Tahun)
    expect($this->regularPegawai->masa_kerja)->toBe('15 Tahun');
});

test('admin can create and update pegawai with select options', function () {
    // 1. Create
    $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), [
        'name' => 'Dr. H. Calon Atasan, S.H.',
        'email' => 'calon.atasan@pa-sumber.go.id',
        'role' => 'pegawai',
        'nip' => '197501012000031005',
        'jabatan' => 'Panitera Muda',
        'unit_kerja' => 'Kepaniteraan',
        'sisa_cuti_n' => 12,
    ]);

    $response->assertRedirect(route('pegawai.index'));
    $atasanPegawai = Pegawai::where('nip', '197501012000031005')->first();
    expect($atasanPegawai->jabatan->nama_jabatan)->toBe('Panitera Muda');

    // 2. Form create must list select options
    $respCreate = $this->actingAs($this->adminUser)->get(route('pegawai.create'));
    $respCreate->assertOk();
    $respCreate->assertSee('Panitera Muda');

    // 3. Update
    $respUpdate = $this->actingAs($this->adminUser)->put(route('pegawai.update', $atasanPegawai->id), [
        'name' => 'Dr. H. Calon Atasan, S.H.',
        'email' => 'calon.atasan@pa-sumber.go.id',
        'role' => 'pegawai',
        'nip' => '197501012000031005',
        'jabatan' => 'Hakim',
        'sisa_cuti_n' => 12,
    ]);

    $respUpdate->assertRedirect(route('pegawai.index'));
    $atasanPegawai->refresh();
    expect($atasanPegawai->jabatan->nama_jabatan)->toBe('Hakim');
});

test('admin can view pegawai detail show page', function () {
    $response = $this->actingAs($this->adminUser)->get(route('pegawai.show', $this->regularPegawai->id));
    $response->assertOk();
    $response->assertSee('Detail Data Pegawai & Akun');
    $response->assertSee('Budi Santoso');
});

test('admin updating pegawai on page 2 preserves page and filter parameters on redirect and cancel', function () {
    // 1. Cek halaman edit dengan parameter page=2
    $respEdit = $this->actingAs($this->adminUser)->get(route('pegawai.edit', ['pegawai' => $this->regularPegawai->id, 'page' => 2, 'status' => 'Aktif']));
    $respEdit->assertOk();
    // Tombol Batal & Kembali harus mengarah ke page=2
    $respEdit->assertSee(route('pegawai.index', ['page' => 2, 'status' => 'Aktif']));

    // 2. Submit update dengan parameter page=2
    $respUpdate = $this->actingAs($this->adminUser)->put(route('pegawai.update', ['pegawai' => $this->regularPegawai->id, 'page' => 2, 'status' => 'Aktif']), [
        'name' => 'Budi Santoso Halaman 2',
        'email' => 'budi.halaman2@pa-sumber.go.id',
        'role' => 'pegawai',
        'jabatan' => 'Panitera Pengganti',
        'sisa_cuti_n' => 12,
        'page' => 2,
        'status' => 'Aktif',
    ]);

    // Redirect harus kembali ke page 2
    $respUpdate->assertRedirect(route('pegawai.index', ['page' => 2, 'status' => 'Aktif']));
    $respUpdate->assertSessionHas('success');
});

test('admin can sort pegawai by various criteria', function () {
    $u1 = User::factory()->create(['name' => 'AAA Andi']);
    $p1 = Pegawai::create([
        'user_id' => $u1->id,
        'nip' => '11111111',
        'jabatan' => 'Staff Umum',
        'sisa_cuti_n' => 5,
        'status_pegawai' => 'Aktif',
    ]);

    $u2 = User::factory()->create(['name' => 'ZZZ Zainal']);
    $p2 = Pegawai::create([
        'user_id' => $u2->id,
        'nip' => '99999999',
        'jabatan' => 'Staff IT',
        'sisa_cuti_n' => 15,
        'status_pegawai' => 'Aktif',
    ]);

    // Sort by name ASC
    $respNameAsc = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['sort' => 'nama_asc']));
    $respNameAsc->assertOk();
    $pegawais = $respNameAsc->viewData('pegawais');
    expect($pegawais->first()->user->name)->toBe('AAA Andi');

    // Sort by cuti DESC
    $respCutiDesc = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['sort' => 'cuti_desc']));
    $respCutiDesc->assertOk();
    $pegawaisCuti = $respCutiDesc->viewData('pegawais');
    expect($pegawaisCuti->first()->sisa_cuti_n)->toBe(15);
});

test('admin can filter pegawai by role, gender, and struktural', function () {
    $uAdmin = User::factory()->create(['name' => 'Pak Admin', 'role' => 'admin']);
    $pAdmin = Pegawai::create([
        'user_id' => $uAdmin->id,
        'nip' => '198501012010011001', // Laki-laki (digit 15 = 1)
        'jabatan' => 'Sekretaris',
        'status_pegawai' => 'Aktif',
    ]);
    // Ensure is_struktural true for Sekretaris
    $pAdmin->jabatan()->update(['is_struktural' => true]);

    $uWanita = User::factory()->create(['name' => 'Bu Fungsional', 'role' => 'pegawai']);
    $pWanita = Pegawai::create([
        'user_id' => $uWanita->id,
        'nip' => '199202022018022002', // Perempuan (digit 15 = 2)
        'jabatan' => 'Pranata Komputer',
        'status_pegawai' => 'Aktif',
    ]);
    $pWanita->jabatan()->update(['is_struktural' => false]);

    // 1. Filter Role: admin
    $respRole = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['role' => 'admin']));
    $respRole->assertOk();
    $respRole->assertSee('Pak Admin');
    $respRole->assertDontSee('Bu Fungsional');

    // 2. Filter Gender: P (Perempuan)
    $respGender = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['gender' => 'P']));
    $respGender->assertOk();
    $respGender->assertSee('Bu Fungsional');
    $respGender->assertDontSee('Pak Admin');

    // 3. Filter Struktural: struktural
    $respStruktural = $this->actingAs($this->adminUser)->get(route('pegawai.index', ['struktural' => 'struktural']));
    $respStruktural->assertOk();
    $respStruktural->assertSee('Pak Admin');
    $respStruktural->assertDontSee('Bu Fungsional');

    // 4. View renders unified search bar with popover and mobile cards
    $respIndex = $this->actingAs($this->adminUser)->get(route('pegawai.index'));
    $respIndex->assertOk();
    $respIndex->assertSee('openFilter: false', false);
    $respIndex->assertSee('hidden md:block', false);
    $respIndex->assertSee('block md:hidden', false);
    $respIndex->assertSee('NIP (Terkecil-Terbesar)');
    $respIndex->assertSee('Sisa Saldo Cuti (Terdikit)');
    $respIndex->assertSee('Saldo Cuti');
});

