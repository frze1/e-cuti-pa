<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomConfirmAndToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_confirm_modal_and_toast_components_render_in_app_layout(): void
    {
        $user = User::factory()->create();
        Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012020011001',
            'jabatan' => 'Staff Kepegawaian',
            'tanggal_masuk' => '2020-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('confirmModalComponent()', false);
        $response->assertSee('toastNotificationComponent()', false);
        $response->assertSee('data-confirm', false);
    }

    public function test_tarik_cuti_form_has_custom_confirm_attributes(): void
    {
        $user = User::factory()->create();
        Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012020011002',
            'jabatan' => 'Analis',
            'tanggal_masuk' => '2020-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Liburan keluarga',
            'tgl_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tgl_selesai' => now()->addDays(7)->format('Y-m-d'),
            'durasi_hari' => 3,
            'alamat_cuti' => 'Jl. Merdeka',
            'no_telepon' => '08123456789',
            'status' => 'Menunggu Atasan',
        ]);

        $response = $this->actingAs($user)->get(route('cuti.riwayat'));

        $response->assertStatus(200);
        $response->assertSee('data-confirm="true"', false);
        $response->assertSee('data-confirm-title="Tarik Permohonan Cuti?"', false);
        $response->assertDontSee("onsubmit=\"return confirm('Yakin ingin menarik permohonan?')\"", false);
    }

    public function test_toast_notification_captures_flash_success(): void
    {
        $user = User::factory()->create();
        Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012020011003',
            'jabatan' => 'Pranata Komputer',
            'tanggal_masuk' => '2020-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Aksi pengujian berhasil!'])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Aksi pengujian berhasil!', false);
    }

    public function test_global_error_toast_renders_when_validation_errors_present(): void
    {
        $user = User::factory()->create();
        Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012020011004',
            'jabatan' => 'Pranata Komputer',
            'tanggal_masuk' => '2020-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'errors' => session('errors', new \Illuminate\Support\ViewErrorBag)->put(
                    'default',
                    new \Illuminate\Support\MessageBag(['nama' => ['Kolom nama wajib diisi.']])
                )
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Gagal Menyimpan!', false);
        $response->assertSee('Silakan periksa kembali isian form yang ditandai merah.', false);
    }

    public function test_cuti_create_layout_renders_form_and_quota_card(): void
    {
        $user = User::factory()->create();
        Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012020011005',
            'jabatan' => 'Pranata Komputer',
            'tanggal_masuk' => '2020-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Urusan keluarga',
            'tgl_mulai' => '2026-12-01',
            'tgl_selesai' => '2026-12-04',
            'durasi_hari' => 4,
            'status' => 'MENUNGGU_ATASAN',
        ]);

        $response = $this->actingAs($user)->get(route('cuti.create'));

        $response->assertStatus(200);
        $response->assertSee('Informasi Kuota Cuti Tahunan:');
        $response->assertSee('Kuota Pending:');
        $response->assertSee('4 hari');
        $response->assertSee('Sisa Kuota Efektif:');
        $response->assertSee('8 hari');
        $response->assertSee('x-model.number="durasiCuti"', false);
        $response->assertSee('x-show="melebihiKuota"', false);
        $response->assertSee('Lama cuti melebihi sisa kuota efektif');
        $response->assertSee('Simpan & Ajukan Cuti');
        
        $content = $response->getContent();
        // Ensure no duplicated no_telp input field
        $this->assertEquals(1, substr_count($content, 'id="no_telp"'));
    }

    public function test_tinjau_ketua_ui_enhancements(): void
    {
        $ketuaUser = User::factory()->create([
            'role' => 'pegawai',
        ]);
        Pegawai::create([
            'user_id' => $ketuaUser->id,
            'nip' => '198001012005011001',
            'jabatan' => 'Ketua',
            'is_struktural' => true,
            'is_pimpinan_final' => true,
            'tanggal_masuk' => '2005-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        $pemohon = User::factory()->create(['name' => 'Misbah, S.Ag.', 'role' => 'pegawai']);
        Pegawai::create([
            'user_id' => $pemohon->id,
            'nip' => '198501012010011002',
            'jabatan' => 'Panitera',
            'tanggal_masuk' => '2010-01-01',
            'sisa_cuti_n' => 12,
            'sisa_cuti_n1' => 0,
            'sisa_cuti_n2' => 0,
        ]);

        $cuti = PengajuanCuti::create([
            'user_id' => $pemohon->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'alasan' => 'Urusan keluarga',
            'tgl_mulai' => '2026-11-20',
            'tgl_selesai' => '2026-11-20',
            'durasi_hari' => 1,
            'alamat_selama_cuti' => 'Jl. Pengadilan No. 1',
            'no_telp' => '081234567890',
            'status' => 'MENUNGGU_KETUA',
        ]);

        $response = $this->actingAs($ketuaUser)->get(route('cuti.tinjau_ketua', $cuti->id));

        $response->assertStatus(200);
        // 1. Teks pertimbangan atasan langsung tanpa perantara
        $response->assertSee('Langsung di bawah Ketua PA (Tanpa Atasan Perantara)');
        $response->assertDontSee('Langsung ke Ketua PA (Tanpa Perantara)');

        // 2. Format tanggal 1 hari disederhanakan
        $response->assertSee('20 November 2026 (1 Hari)');
        $response->assertDontSee('20 November 2026 s/d 20 November 2026');

        // 3. Tombol eksekusi hijau khas dinas dan tidak ada warna ungu
        $response->assertSee('bg-dinas-green', false);
        $response->assertDontSee('bg-purple-600', false);

        // 4. Konsistensi layout keputusan dengan tinjau atasan langsung
        $response->assertSee('Keputusan Pertimbangan');
        $response->assertSee('Pilih Keputusan');
        $response->assertSee('Disetujui');
        $response->assertSee('Perubahan (Revisi)');
        $response->assertSee('Ditangguhkan');
        $response->assertSee('Tidak Disetujui');
        $response->assertSee('Catatan (Wajib diisi jika Perubahan / Ditangguhkan)');

        // 5. Label tahap pengesahan pasca ACC dihilangkan
        $response->assertDontSee('Tahap Pengesahan (Penomoran Diterbitkan Pasca ACC)');
    }
}
