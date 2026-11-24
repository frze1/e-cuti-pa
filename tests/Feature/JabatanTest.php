<?php

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'name' => 'Admin Kepegawaian',
        'email' => 'admin@pa-sumber.go.id',
        'role' => 'admin',
    ]);
    $this->adminJabatan = Jabatan::create([
        'nama_jabatan' => 'Administrator Sistem',
        'is_struktural' => false,
    ]);
    $this->adminPegawai = Pegawai::create([
        'user_id' => $this->adminUser->id,
        'nip' => '198001012005011001',
        'jabatan_id' => $this->adminJabatan->id,
        'unit_kerja' => 'Kesekretariatan',
        'sisa_cuti_n' => 12,
    ]);

    $this->regularUser = User::factory()->create([
        'name' => 'Pegawai Biasa',
        'email' => 'pegawai@pa-sumber.go.id',
        'role' => 'pegawai',
    ]);
    $this->regularJabatan = Jabatan::create([
        'nama_jabatan' => 'Staf Pelaksana',
        'is_struktural' => false,
    ]);
    $this->regularPegawai = Pegawai::create([
        'user_id' => $this->regularUser->id,
        'nip' => '199502022020021002',
        'jabatan_id' => $this->regularJabatan->id,
        'unit_kerja' => 'Kepaniteraan',
        'sisa_cuti_n' => 12,
    ]);
});

test('non-admin user cannot access jabatan management', function () {
    $this->actingAs($this->regularUser)->get(route('jabatan.index'))->assertForbidden();
    $this->actingAs($this->regularUser)->get(route('jabatan.create'))->assertForbidden();
    $this->actingAs($this->regularUser)->post(route('jabatan.store'), ['nama_jabatan' => 'Jabatan Test'])->assertForbidden();
    $this->actingAs($this->regularUser)->get(route('jabatan.edit', $this->regularJabatan->id))->assertForbidden();
    $this->actingAs($this->regularUser)->put(route('jabatan.update', $this->regularJabatan->id), ['nama_jabatan' => 'Update Test'])->assertForbidden();
    $this->actingAs($this->regularUser)->delete(route('jabatan.destroy', $this->regularJabatan->id))->assertForbidden();
});

test('admin can view jabatan index and see list with statistics', function () {
    $response = $this->actingAs($this->adminUser)->get(route('jabatan.index'));
    $response->assertOk();
    $response->assertSee('Manajemen Jabatan & Struktur Organisasi');
    $response->assertSee('Total Master Jabatan');
    $response->assertSee('Administrator Sistem');
    $response->assertSee('Staf Pelaksana');
});

test('admin can create a new jabatan with parent and is_struktural flag', function () {
    // 1. Buat jabatan induk (Ketua)
    $ketua = Jabatan::create([
        'nama_jabatan' => 'Ketua Pengadilan',
        'is_struktural' => true,
    ]);

    // 2. Buat jabatan bawahan via form POST
    $response = $this->actingAs($this->adminUser)->post(route('jabatan.store'), [
        'nama_jabatan' => 'Panitera Baru',
        'parent_id' => $ketua->id,
        'is_struktural' => '1',
    ]);

    $response->assertRedirect(route('jabatan.index'));
    $response->assertSessionHas('success');

    $panitera = Jabatan::where('nama_jabatan', 'Panitera Baru')->first();
    expect($panitera)->not->toBeNull();
    expect($panitera->parent_id)->toBe($ketua->id);
    expect($panitera->is_struktural)->toBeTrue();
    expect($panitera->parent->nama_jabatan)->toBe('Ketua Pengadilan');
    expect($ketua->children()->pluck('id')->all())->toContain($panitera->id);
});

test('admin can update a jabatan and change its parent', function () {
    $parent1 = Jabatan::create(['nama_jabatan' => 'Induk 1', 'is_struktural' => true]);
    $parent2 = Jabatan::create(['nama_jabatan' => 'Induk 2', 'is_struktural' => true]);
    $anak = Jabatan::create(['nama_jabatan' => 'Anak Asuh', 'parent_id' => $parent1->id, 'is_struktural' => false]);

    $response = $this->actingAs($this->adminUser)->put(route('jabatan.update', $anak->id), [
        'nama_jabatan' => 'Anak Asuh Terpilih',
        'parent_id' => $parent2->id,
        'is_struktural' => '1',
    ]);

    $response->assertRedirect(route('jabatan.index'));
    $anak->refresh();
    expect($anak->nama_jabatan)->toBe('Anak Asuh Terpilih');
    expect($anak->parent_id)->toBe($parent2->id);
    expect($anak->is_struktural)->toBeTrue();
});

test('validation prevents jabatan from being assigned as its own parent or circular hierarchy', function () {
    $jabatanA = Jabatan::create(['nama_jabatan' => 'Jabatan A', 'is_struktural' => true]);
    $jabatanB = Jabatan::create(['nama_jabatan' => 'Jabatan B', 'parent_id' => $jabatanA->id, 'is_struktural' => true]);

    // 1. Tidak boleh atasan dari diri sendiri
    $respSelf = $this->actingAs($this->adminUser)->put(route('jabatan.update', $jabatanA->id), [
        'nama_jabatan' => 'Jabatan A',
        'parent_id' => $jabatanA->id,
    ]);
    $respSelf->assertSessionHasErrors('parent_id');

    // 2. Circular reference: Jabatan A tidak boleh memilih Jabatan B (karena B adalah bawahan A)
    $respCirc = $this->actingAs($this->adminUser)->put(route('jabatan.update', $jabatanA->id), [
        'nama_jabatan' => 'Jabatan A',
        'parent_id' => $jabatanB->id,
    ]);
    $respCirc->assertSessionHasErrors('parent_id');
});

test('admin cannot delete jabatan that still has employees or child positions', function () {
    $parent = Jabatan::create(['nama_jabatan' => 'Pimpinan Cabang', 'is_struktural' => true]);
    $child = Jabatan::create(['nama_jabatan' => 'Sub Pimpinan', 'parent_id' => $parent->id]);

    // Hapus parent yang masih punya child -> gagal
    $resp1 = $this->actingAs($this->adminUser)->delete(route('jabatan.destroy', $parent->id));
    $resp1->assertSessionHas('error');
    expect(Jabatan::find($parent->id))->not->toBeNull();

    // Hapus jabatan yang masih diduduki pegawai -> gagal
    $resp2 = $this->actingAs($this->adminUser)->delete(route('jabatan.destroy', $this->regularJabatan->id));
    $resp2->assertSessionHas('error');
    expect(Jabatan::find($this->regularJabatan->id))->not->toBeNull();

    // Hapus child yang kosong -> berhasil
    $resp3 = $this->actingAs($this->adminUser)->delete(route('jabatan.destroy', $child->id));
    $resp3->assertSessionHas('success');
    expect(Jabatan::find($child->id))->toBeNull();
});

test('subordinates automatically inherit new superior when employee in superior position changes', function () {
    // 1. Buat hierarki jabatan: Panitera -> Panitera Pengganti
    $jabatanPanitera = Jabatan::create([
        'nama_jabatan' => 'Panitera Pengadilan',
        'is_struktural' => true,
    ]);
    $jabatanPP = Jabatan::create([
        'nama_jabatan' => 'Panitera Pengganti Tingkat I',
        'parent_id' => $jabatanPanitera->id,
        'is_struktural' => false,
    ]);

    // 2. Buat Pegawai Pejabat Panitera Lama (Bpk. Fulan)
    $userFulan = User::factory()->create(['name' => 'Bpk. Fulan (Panitera Lama)']);
    $pegawaiFulan = Pegawai::create([
        'user_id' => $userFulan->id,
        'nip' => '197001011995011001',
        'jabatan_id' => $jabatanPanitera->id,
        'status_pegawai' => 'Aktif',
    ]);

    // 3. Buat Pegawai Bawahan (Bpk. Bawahan)
    $userBawahan = User::factory()->create(['name' => 'Bpk. Bawahan']);
    $pegawaiBawahan = Pegawai::create([
        'user_id' => $userBawahan->id,
        'nip' => '199001012015011002',
        'jabatan_id' => $jabatanPP->id,
        'status_pegawai' => 'Aktif',
    ]);

    // Verifikasi: Atasan langsung awal adalah Bpk. Fulan
    expect($pegawaiBawahan->getAtasanPegawai()->id)->toBe($pegawaiFulan->id);
    expect($pegawaiBawahan->atasan->user->name)->toBe('Bpk. Fulan (Panitera Lama)');

    // 4. Bpk. Fulan pensiun (status = Pensiun) dan digantikan oleh Ibu Siti (Panitera Baru)
    $pegawaiFulan->update(['status_pegawai' => 'Pensiun']);

    $userSiti = User::factory()->create(['name' => 'Ibu Siti (Panitera Baru)']);
    $pegawaiSiti = Pegawai::create([
        'user_id' => $userSiti->id,
        'nip' => '197801012000012001',
        'jabatan_id' => $jabatanPanitera->id,
        'status_pegawai' => 'Aktif',
    ]);

    // TANPA MENGUBAH DATA PEGAWAI BAWAHAN SAMA SEKALI,
    // Atasan langsung Bpk. Bawahan secara otomatis langsung beralih ke Ibu Siti!
    $pegawaiBawahan->refresh();
    expect($pegawaiBawahan->getAtasanPegawai()->id)->toBe($pegawaiSiti->id);
    expect($pegawaiBawahan->atasan->user->name)->toBe('Ibu Siti (Panitera Baru)');
});

test('parent_id dropdown on create and edit only lists structural positions', function () {
    $struktural = Jabatan::create([
        'nama_jabatan' => 'Sekretaris Pengadilan',
        'is_struktural' => true,
    ]);
    $nonStruktural = Jabatan::create([
        'nama_jabatan' => 'Pengemudi / Driver',
        'is_struktural' => false,
    ]);

    // Pada form create
    $respCreate = $this->actingAs($this->adminUser)->get(route('jabatan.create'));
    $respCreate->assertOk();
    $respCreate->assertSee('Sekretaris Pengadilan');
    $respCreate->assertDontSee('Pengemudi / Driver');

    // Pada form edit
    $targetEdit = Jabatan::create([
        'nama_jabatan' => 'Staf Arsip',
        'is_struktural' => false,
    ]);
    $respEdit = $this->actingAs($this->adminUser)->get(route('jabatan.edit', $targetEdit->id));
    $respEdit->assertOk();
    $respEdit->assertSee('Sekretaris Pengadilan');
    $respEdit->assertDontSee('Pengemudi / Driver');
});

test('validation blocks assigning a non-structural position as parent_id', function () {
    $nonStruktural = Jabatan::create([
        'nama_jabatan' => 'Tenaga Honorer',
        'is_struktural' => false,
    ]);

    // Store
    $respStore = $this->actingAs($this->adminUser)->post(route('jabatan.store'), [
        'nama_jabatan' => 'Bawahan Baru',
        'parent_id' => $nonStruktural->id,
    ]);
    $respStore->assertSessionHasErrors('parent_id');

    // Update
    $targetJabatan = Jabatan::create([
        'nama_jabatan' => 'Jabatan Percobaan',
        'is_struktural' => false,
    ]);
    $respUpdate = $this->actingAs($this->adminUser)->put(route('jabatan.update', $targetJabatan->id), [
        'nama_jabatan' => 'Jabatan Percobaan',
        'parent_id' => $nonStruktural->id,
    ]);
    $respUpdate->assertSessionHasErrors('parent_id');
});

test('admin updating jabatan on page 2 preserves page parameter on redirect and cancel', function () {
    $targetJabatan = Jabatan::create([
        'nama_jabatan' => 'Jabatan Halaman 2',
        'is_struktural' => false,
    ]);

    // 1. Cek form edit membawa link Kembali & Batal ke page 2
    $respEdit = $this->actingAs($this->adminUser)->get(route('jabatan.edit', ['jabatan' => $targetJabatan->id, 'page' => 2]));
    $respEdit->assertOk();
    $respEdit->assertSee(route('jabatan.index', ['page' => 2]));

    // 2. Submit update dengan parameter page 2
    $respUpdate = $this->actingAs($this->adminUser)->put(route('jabatan.update', ['jabatan' => $targetJabatan->id, 'page' => 2]), [
        'nama_jabatan' => 'Jabatan Halaman 2 Diperbarui',
        'page' => 2,
    ]);

    // Redirect harus kembali ke page 2
    $respUpdate->assertRedirect(route('jabatan.index', ['page' => 2]));
    $respUpdate->assertSessionHas('success');
});

test('admin can sort jabatan by name ascending and descending', function () {
    Jabatan::create(['nama_jabatan' => 'AAA Jabatan Awal', 'is_struktural' => false]);
    Jabatan::create(['nama_jabatan' => 'ZZZ Jabatan Akhir', 'is_struktural' => false]);

    $respAsc = $this->actingAs($this->adminUser)->get(route('jabatan.index', ['sort' => 'nama_asc']));
    $respAsc->assertOk();
    $jabatansAsc = $respAsc->viewData('jabatans');
    expect($jabatansAsc->first()->nama_jabatan)->toBe('AAA Jabatan Awal');

    $respDesc = $this->actingAs($this->adminUser)->get(route('jabatan.index', ['sort' => 'nama_desc']));
    $respDesc->assertOk();
    $jabatansDesc = $respDesc->viewData('jabatans');
    expect($jabatansDesc->first()->nama_jabatan)->toBe('ZZZ Jabatan Akhir');
});

test('admin can sort jabatan by bawahan_desc and view unified search bar and mobile cards', function () {
    $parentBanyak = Jabatan::create(['nama_jabatan' => 'Induk Banyak Bawahan', 'is_struktural' => true]);
    Jabatan::create(['nama_jabatan' => 'Bawahan 1', 'parent_id' => $parentBanyak->id, 'is_struktural' => false]);
    Jabatan::create(['nama_jabatan' => 'Bawahan 2', 'parent_id' => $parentBanyak->id, 'is_struktural' => false]);

    $parentSedikit = Jabatan::create(['nama_jabatan' => 'Induk Sedikit Bawahan', 'is_struktural' => true]);
    Jabatan::create(['nama_jabatan' => 'Bawahan Tunggal', 'parent_id' => $parentSedikit->id, 'is_struktural' => false]);

    // 1. Sort by bawahan_desc
    $respBawahan = $this->actingAs($this->adminUser)->get(route('jabatan.index', ['sort' => 'bawahan_desc']));
    $respBawahan->assertOk();
    $jabatansBawahan = $respBawahan->viewData('jabatans');
    expect($jabatansBawahan->first()->nama_jabatan)->toBe('Induk Banyak Bawahan');

    // 2. Filter by struktural
    $respStruktural = $this->actingAs($this->adminUser)->get(route('jabatan.index', ['struktural' => '1']));
    $respStruktural->assertOk();
    $respStruktural->assertSee('Induk Banyak Bawahan');
    expect($respStruktural->viewData('jabatans')->pluck('nama_jabatan'))->not->toContain('Bawahan 1');

    // 3. View renders unified search with Alpine popover and mobile cards
    $respIndex = $this->actingAs($this->adminUser)->get(route('jabatan.index'));
    $respIndex->assertOk();
    $respIndex->assertSee('openFilter: false', false);
    $respIndex->assertSee('hidden md:block', false);
    $respIndex->assertSee('block md:hidden', false);
    $respIndex->assertSee('Jumlah Bawahan (Terbanyak - Terdikit)');
    $respIndex->assertSee('Alfabetis Nama Jabatan (A-Z)');
    $respIndex->assertSee('Kewenangan Approval');
});

test('admin sees interactive bawahan modal trigger button when children exist and muted badge when 0', function () {
    $parent = Jabatan::create(['nama_jabatan' => 'Panitera Utama', 'is_struktural' => true]);
    $child1 = Jabatan::create(['nama_jabatan' => 'Panitera Muda Permohonan', 'parent_id' => $parent->id, 'is_struktural' => true]);
    $child2 = Jabatan::create(['nama_jabatan' => 'Panitera Pengganti Senior', 'parent_id' => $parent->id, 'is_struktural' => false]);

    // Pegawai aktif for child1
    $childUser = User::factory()->create(['name' => 'Bawahan Satu']);
    Pegawai::create([
        'user_id' => $childUser->id,
        'nip' => '199001012015011001',
        'jabatan_id' => $child1->id,
        'status_pegawai' => 'Aktif',
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('jabatan.index'));
    $response->assertOk();

    // Verify modal state container & functions exist
    $response->assertSee('modalBawahanOpen: false', false);
    $response->assertSee('bukaModalBawahan', false);

    // Verify modal structure & dynamic content
    $response->assertSee('Panitera Muda Permohonan');
    $response->assertSee('Bawahan Satu');
    $response->assertSee('Daftar Bawahan');
    $response->assertSee('cursor-not-allowed', false);
    $response->assertSee('cursor-pointer hover:bg-dinas-green', false);
});

