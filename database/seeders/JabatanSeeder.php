<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds with accurate Court Organization Hierarchy (Pohon Jabatan)
     */
    public function run(): void
    {
        // 1. Data master jabatan dengan status struktural dan nama jabatan atasan
        $strukturJabatan = [
            // Pimpinan Utama
            ['nama' => 'Ketua', 'parent' => null, 'is_struktural' => true],
            ['nama' => 'Wakil Ketua', 'parent' => 'Ketua', 'is_struktural' => true],
            ['nama' => 'Hakim', 'parent' => 'Ketua', 'is_struktural' => false],

            // Pejabat Eselon / Pimpinan Bidang
            ['nama' => 'Panitera', 'parent' => 'Ketua', 'is_struktural' => true],
            ['nama' => 'Sekretaris', 'parent' => 'Ketua', 'is_struktural' => true],

            // Kepaniteraan (Panitera Muda)
            ['nama' => 'Panitera Muda Hukum', 'parent' => 'Panitera', 'is_struktural' => true],
            ['nama' => 'Panitera Muda Gugatan', 'parent' => 'Panitera', 'is_struktural' => true],
            ['nama' => 'Panitera Muda Permohonan', 'parent' => 'Panitera', 'is_struktural' => true],

            // Kesekretariatan (Kepala Sub Bagian)
            ['nama' => 'Kepala Sub Bagian Umum dan Keuangan', 'parent' => 'Sekretaris', 'is_struktural' => true],
            ['nama' => 'Kepala Sub Bagian Kepegawaian, Organisasi, dan Tata Laksana', 'parent' => 'Sekretaris', 'is_struktural' => true],
            ['nama' => 'Kepala Sub Bagian Perencanaan, Teknologi Informasi, dan Pelaporan', 'parent' => 'Sekretaris', 'is_struktural' => true],

            // Fungsional Kepaniteraan
            ['nama' => 'Panitera Pengganti', 'parent' => 'Panitera', 'is_struktural' => false],
            ['nama' => 'Juru Sita', 'parent' => 'Panitera', 'is_struktural' => false],
            ['nama' => 'Juru Sita Pengganti', 'parent' => 'Panitera', 'is_struktural' => false],

            // Fungsional Keahlian & Pelaksana
            ['nama' => 'Pranata Komputer Pertama', 'parent' => 'Kepala Sub Bagian Perencanaan, Teknologi Informasi, dan Pelaporan', 'is_struktural' => false],
            ['nama' => 'Analis Pengelolaan Keuangan APBN Ahli Muda', 'parent' => 'Kepala Sub Bagian Umum dan Keuangan', 'is_struktural' => false],
            ['nama' => 'Analis Sumber Daya Manusia Aparatur', 'parent' => 'Kepala Sub Bagian Kepegawaian, Organisasi, dan Tata Laksana', 'is_struktural' => false],
            ['nama' => 'Pengelola Kepegawaian', 'parent' => 'Kepala Sub Bagian Kepegawaian, Organisasi, dan Tata Laksana', 'is_struktural' => false],
            ['nama' => 'Pengelola Penanganan Perkara', 'parent' => 'Panitera Muda Permohonan', 'is_struktural' => false],
            ['nama' => 'Pengolah Data dan Informasi', 'parent' => 'Kepala Sub Bagian Perencanaan, Teknologi Informasi, dan Pelaporan', 'is_struktural' => false],
            ['nama' => 'Analis Perkara Peradilan', 'parent' => 'Panitera Muda Hukum', 'is_struktural' => false],
            ['nama' => 'Operator Layanan Operasional', 'parent' => 'Kepala Sub Bagian Umum dan Keuangan', 'is_struktural' => false],
            ['nama' => 'Penata Layanan Operasional', 'parent' => 'Kepala Sub Bagian Umum dan Keuangan', 'is_struktural' => false],
        ];

        // Langkah 1: Pastikan semua record jabatan ada terlebih dahulu
        foreach ($strukturJabatan as $item) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $item['nama']],
                ['is_struktural' => $item['is_struktural']]
            );
        }

        // Langkah 2: Hubungkan parent_id (Atasan) dan perbarui flag is_struktural
        foreach ($strukturJabatan as $item) {
            $parentId = null;
            if (!empty($item['parent'])) {
                $parentRecord = Jabatan::where('nama_jabatan', $item['parent'])->first();
                $parentId = $parentRecord?->id;
            }

            Jabatan::where('nama_jabatan', $item['nama'])->update([
                'parent_id' => $parentId,
                'is_struktural' => $item['is_struktural'],
            ]);
        }
    }
}
