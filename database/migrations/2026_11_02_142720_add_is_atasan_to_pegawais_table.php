<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->boolean('is_atasan')->default(false)->after('jabatan');
        });

        // Tandai otomatis pegawai yang saat ini sudah menjadi atasan langsung atau berpangkat pimpinan/struktural
        try {
            DB::table('pegawais')
                ->whereIn('id', function ($query) {
                    $query->select('atasan_langsung_id')
                        ->from('pegawais')
                        ->whereNotNull('atasan_langsung_id');
                })
                ->orWhere('jabatan', 'like', '%Ketua%')
                ->orWhere('jabatan', 'like', '%Panitera%')
                ->orWhere('jabatan', 'like', '%Sekretaris%')
                ->orWhere('jabatan', 'like', '%Kasubbag%')
                ->update(['is_atasan' => true]);
        } catch (\Throwable $e) {
            // Ignore seeder sync error on fresh setup
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn('is_atasan');
        });
    }
};
