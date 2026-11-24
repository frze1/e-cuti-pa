<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Seed 20+ realistic Court Staff (Aparatur Pengadilan Agama Sumber)
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        $pegawaiList = [
            // PIMPINAN
            [
                'name' => 'Drs. H. Ahmad Fulan, M.H.',
                'email' => 'ketua@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197003031995031001',
                'jabatan' => 'Ketua',
                'tanggal_masuk' => '2001-03-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Dra. Hj. Siti Aminah, M.H.',
                'email' => 'wakilketua@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197305121998032001',
                'jabatan' => 'Wakil Ketua',
                'tanggal_masuk' => '2003-04-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 2,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Drs. H. Bambang Sutrisno, S.H., M.H.',
                'email' => 'hakim.bambang@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197508152000031002',
                'jabatan' => 'Hakim',
                'tanggal_masuk' => '2005-01-01',
                'sisa_cuti_n' => 10,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Dra. Hj. Nurul Hidayah, M.H.',
                'email' => 'hakim.nurul@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197811202003122001',
                'jabatan' => 'Hakim',
                'tanggal_masuk' => '2007-06-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 4,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Muhammad Ridwan, S.H.I., M.S.I.',
                'email' => 'hakim.ridwan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198204102008011003',
                'jabatan' => 'Hakim',
                'tanggal_masuk' => '2010-09-01',
                'sisa_cuti_n' => 11,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],

            // KEPANITERAAN - STRUKTURAL
            [
                'name' => 'H. Mochamad Dedy, S.H., M.H.',
                'email' => 'panitera@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197206141997031001',
                'jabatan' => 'Panitera',
                'tanggal_masuk' => '2002-05-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Hendra Gunawan, S.H., M.H.',
                'email' => 'panmud.hukum@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198107192006041002',
                'jabatan' => 'Panitera Muda Hukum',
                'tanggal_masuk' => '2008-03-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Hj. Maryati, S.Ag.',
                'email' => 'panmud.gugatan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197903212005012003',
                'jabatan' => 'Panitera Muda Gugatan',
                'tanggal_masuk' => '2007-08-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 1,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Abdul Halim, S.H.',
                'email' => 'panmud.permohonan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198310052009121001',
                'jabatan' => 'Panitera Muda Permohonan',
                'tanggal_masuk' => '2011-02-01',
                'sisa_cuti_n' => 8,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],

            // KEPANITERAAN - FUNGSIONAL
            [
                'name' => 'Dwi Lestari, S.H.',
                'email' => 'pp.dwilestari@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198708222011012002',
                'jabatan' => 'Panitera Pengganti',
                'tanggal_masuk' => '2013-05-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Agus Setiawan, S.H.I.',
                'email' => 'pp.agus@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198901142014021001',
                'jabatan' => 'Panitera Pengganti',
                'tanggal_masuk' => '2015-08-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 2,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'M. Syarifudin',
                'email' => 'jurusita1@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198606182009031002',
                'jabatan' => 'Juru Sita',
                'tanggal_masuk' => '2010-04-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Ratna Sari, A.Md.',
                'email' => 'jsp.ratna@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199209102015032001',
                'jabatan' => 'Juru Sita Pengganti',
                'tanggal_masuk' => '2016-09-01',
                'sisa_cuti_n' => 9,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Dian Permata, S.H.',
                'email' => 'analis.perkara@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199705302022032003',
                'jabatan' => 'Analis Perkara Peradilan',
                'tanggal_masuk' => '2022-03-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Eko Wahyudi, A.Md.',
                'email' => 'pengelola.perkara@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199407222019031002',
                'jabatan' => 'Pengelola Penanganan Perkara',
                'tanggal_masuk' => '2019-03-01',
                'sisa_cuti_n' => 10,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],

            // KESEKRETARIATAN - STRUKTURAL
            [
                'name' => 'Ahmad Zaini, S.Ag., M.H.',
                'email' => 'sekretaris@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '197609252002121002',
                'jabatan' => 'Sekretaris',
                'tanggal_masuk' => '2004-07-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Rahmat Hidayat, S.E.',
                'email' => 'kasubbag.umum@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198412152008011004',
                'jabatan' => 'Kepala Sub Bagian Umum dan Keuangan',
                'tanggal_masuk' => '2010-02-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Admin Kepegawaian',
                'email' => 'admin@pa-sumber.go.id',
                'role' => 'admin',
                'nip' => '198505052010122001',
                'jabatan' => 'Kepala Sub Bagian Kepegawaian, Organisasi, dan Tata Laksana',
                'tanggal_masuk' => '2012-09-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Bapak Atasan Langsung',
                'email' => 'atasan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '198001012005011001',
                'jabatan' => 'Kepala Sub Bagian Perencanaan, Teknologi Informasi, dan Pelaporan',
                'tanggal_masuk' => '2015-05-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],

            // KESEKRETARIATAN - FUNGSIONAL & PELAKSANA
            [
                'name' => 'River Al Farizie',
                'email' => 'river@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199802022022031002',
                'jabatan' => 'Pranata Komputer Pertama',
                'tanggal_masuk' => '2022-03-01',
                'sisa_cuti_n' => 9,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Budi Prasetyo, S.E.',
                'email' => 'analis.keuangan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199003152014031003',
                'jabatan' => 'Analis Pengelolaan Keuangan APBN Ahli Muda',
                'tanggal_masuk' => '2016-04-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 3,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Fitri Handayani, S.Sos.',
                'email' => 'analis.sdm@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199311282018012001',
                'jabatan' => 'Analis Sumber Daya Manusia Aparatur',
                'tanggal_masuk' => '2019-01-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Tri Wahyuni, A.Md.',
                'email' => 'pengelola.kepeg@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199504172020122004',
                'jabatan' => 'Pengelola Kepegawaian',
                'tanggal_masuk' => '2021-01-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Reza Fahlevi, A.Md.Kom.',
                'email' => 'pengolah.data@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199612052022031001',
                'jabatan' => 'Pengolah Data dan Informasi',
                'tanggal_masuk' => '2022-03-01',
                'sisa_cuti_n' => 11,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Anisa Rahmawati',
                'email' => 'operator.layanan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199908122023012002',
                'jabatan' => 'Operator Layanan Operasional',
                'tanggal_masuk' => '2023-01-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
            [
                'name' => 'Ilham Pratama',
                'email' => 'penata.layanan@pa-sumber.go.id',
                'role' => 'pegawai',
                'nip' => '199810102023011001',
                'jabatan' => 'Penata Layanan Operasional',
                'tanggal_masuk' => '2023-01-01',
                'sisa_cuti_n' => 12,
                'sisa_cuti_n_1' => 0,
                'sisa_cuti_n_2' => 0,
            ],
        ];

        foreach ($pegawaiList as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => $defaultPassword,
                    'role' => $item['role'],
                ]
            );

            // Update user name/role jika sudah ada
            $user->update([
                'name' => $item['name'],
                'role' => $item['role'],
            ]);

            $jabatanRecord = Jabatan::where('nama_jabatan', $item['jabatan'])->first();

            Pegawai::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $item['nip'],
                    'jabatan_id' => $jabatanRecord?->id,
                    'tanggal_masuk' => $item['tanggal_masuk'],
                    'status_pegawai' => 'Aktif',
                    'sisa_cuti_n' => $item['sisa_cuti_n'],
                    'sisa_cuti_n_1' => $item['sisa_cuti_n_1'],
                    'sisa_cuti_n_2' => $item['sisa_cuti_n_2'],
                ]
            );
        }
    }
}
