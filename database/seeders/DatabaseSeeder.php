<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan master struktur jabatan pengadilan terlebih dahulu
        $this->call(JabatanSeeder::class);

        // 2. Jalankan master pegawai & akun pengguna (26 aparatur lengkap PA Sumber)
        $this->call(PegawaiSeeder::class);
    }
}
