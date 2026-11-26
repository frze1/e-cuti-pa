<?php

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\User;

test('export word generates docx with all variables populated from template', function () {
    $ketuaJabatan = Jabatan::create([
        'nama_jabatan' => 'Ketua Pengadilan Agama',
        'is_struktural' => true,
    ]);

    $atasanJabatan = Jabatan::create([
        'nama_jabatan' => 'Panitera',
        'is_struktural' => true,
        'parent_id' => $ketuaJabatan->id,
    ]);

    $pegawaiJabatan = Jabatan::create([
        'nama_jabatan' => 'Panitera Pengganti',
        'is_struktural' => false,
        'parent_id' => $atasanJabatan->id,
    ]);

    $ketuaUser = User::factory()->create(['name' => 'Dr. H. Ishak Lubis, S.Ag.', 'role' => 'pegawai']);
    Pegawai::create([
        'user_id' => $ketuaUser->id,
        'nip' => '196906221997031001',
        'jabatan_id' => $ketuaJabatan->id,
        'status_pegawai' => 'Aktif',
    ]);

    $atasanUser = User::factory()->create(['name' => 'Misbah, S.Ag.', 'role' => 'pegawai']);
    Pegawai::create([
        'user_id' => $atasanUser->id,
        'nip' => '197708082007041001',
        'jabatan_id' => $atasanJabatan->id,
        'status_pegawai' => 'Aktif',
    ]);

    $pemohonUser = User::factory()->create(['name' => 'Agus Nasrullah, S.H.', 'role' => 'pegawai']);
    $pemohonPegawai = Pegawai::create([
        'user_id' => $pemohonUser->id,
        'nip' => '198501012010011002',
        'jabatan_id' => $pegawaiJabatan->id,
        'status_pegawai' => 'Aktif',
        'tanggal_masuk' => '2015-01-01',
        'sisa_cuti_n' => 10,
        'sisa_cuti_n_1' => 4,
        'sisa_cuti_n_2' => 2,
    ]);

    $cuti = PengajuanCuti::create([
        'user_id' => $pemohonUser->id,
        'jenis_cuti' => 'Cuti Tahunan',
        'alasan' => 'Urusan keluarga mendesak',
        'tgl_mulai' => '2026-12-01',
        'tgl_selesai' => '2026-12-03',
        'durasi_hari' => 3,
        'satuan_cuti' => 'hari',
        'alamat_selama_cuti' => 'Jl. Cirebon No. 10',
        'no_telp' => '08123456789',
        'status' => 'SELESAI_DISETUJUI',
        'nomor_surat' => 'W10-A1/123/KP.05.2/XII/2026',
        'disetujui_oleh_atasan_id' => $atasanUser->id,
        'disahkan_oleh_ketua_id' => $ketuaUser->id,
    ]);

    // Uji akses unduh Word oleh pemohon
    $response = $this->actingAs($pemohonUser)->get(route('cuti.export-word', $cuti->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    // Ambil isi dokumen dan verifikasi tidak ada placeholder ${...} yang tertinggal
    $content = $response->streamedContent();
    $tempFile = tempnam(sys_get_temp_dir(), 'test_docx_');
    file_put_contents($tempFile, $content);

    $zip = new ZipArchive();
    $opened = $zip->open($tempFile);
    expect($opened)->toBeTrue();

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($tempFile);

    // Verifikasi variabel terpetakan dengan benar
    expect($xml)->toContain('Agus Nasrullah, S.H.');
    expect($xml)->toContain('W10-A1/123/KP.05.2/XII/2026');
    expect($xml)->toContain('Misbah, S.Ag.');
    expect($xml)->toContain('Dr. H. Ishak Lubis, S.Ag.');
    expect($xml)->toContain('3');
    expect($xml)->toContain('hari');
    expect($xml)->toContain('✓');
    expect($xml)->toContain('2024'); // tahun_n2
    expect($xml)->toContain('2025'); // tahun_n1
    expect($xml)->toContain('2026'); // tahun_n

    // Pastikan tidak ada ${...} yang tertinggal
    preg_match_all('/\$\{[^}]+\}/', $xml, $matches);
    expect($matches[0])->toBeEmpty();
});
