<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreignId('jabatan_id')->nullable()->after('nip')->constrained('jabatans')->nullOnDelete();
            $table->string('pangkat_golongan')->nullable()->after('jabatan_id');
        });

        // Migrasikan data string jabatan yang ada ke tabel jabatans agar data tidak hilang
        if (Schema::hasColumn('pegawais', 'jabatan')) {
            $existingPegawais = DB::table('pegawais')->whereNotNull('jabatan')->get(['id', 'jabatan']);
            foreach ($existingPegawais as $p) {
                if (!empty($p->jabatan)) {
                    $jabatanId = DB::table('jabatans')->where('nama_jabatan', $p->jabatan)->value('id');
                    if (!$jabatanId) {
                        $jabatanId = DB::table('jabatans')->insertGetId([
                            'nama_jabatan' => $p->jabatan,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('pegawais')->where('id', $p->id)->update(['jabatan_id' => $jabatanId]);
                }
            }

            // Hapus kolom jabatan string lama sesuai instruksi
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('jabatan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('jabatan')->nullable()->after('nip');
        });

        // Salin kembali nama_jabatan jika rollback
        $pegawais = DB::table('pegawais')
            ->join('jabatans', 'pegawais.jabatan_id', '=', 'jabatans.id')
            ->select('pegawais.id', 'jabatans.nama_jabatan')
            ->get();

        foreach ($pegawais as $p) {
            DB::table('pegawais')->where('id', $p->id)->update(['jabatan' => $p->nama_jabatan]);
        }

        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn(['jabatan_id', 'pangkat_golongan']);
        });
    }
};

