<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->foreignId('disetujui_oleh_atasan_id')
                ->nullable()
                ->after('catatan_revisi')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('disahkan_oleh_ketua_id')
                ->nullable()
                ->after('disetujui_oleh_atasan_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh_atasan_id']);
            $table->dropForeign(['disahkan_oleh_ketua_id']);
            $table->dropColumn(['disetujui_oleh_atasan_id', 'disahkan_oleh_ketua_id']);
        });
    }
};
