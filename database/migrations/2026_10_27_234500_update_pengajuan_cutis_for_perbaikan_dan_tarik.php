<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            // Ubah tipe status menjadi string agar mendukung status Dibatalkan Pegawai / DIBATALKAN_PEGAWAI dll
            $table->string('status', 50)->default('MENUNGGU_ATASAN')->change();
            
            // Tambahkan catatan_atasan jika belum ada
            if (!Schema::hasColumn('pengajuan_cutis', 'catatan_atasan')) {
                $table->text('catatan_atasan')->nullable()->after('catatan_revisi');
            }
        });

        // Sinkronkan catatan_revisi ke catatan_atasan
        DB::statement('UPDATE pengajuan_cutis SET catatan_atasan = catatan_revisi WHERE catatan_atasan IS NULL AND catatan_revisi IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_cutis', 'catatan_atasan')) {
                $table->dropColumn('catatan_atasan');
            }
        });
    }
};
