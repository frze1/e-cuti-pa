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
            $table->dropForeign(['atasan_langsung_id']);
            $table->dropColumn(['pangkat_golongan', 'is_atasan', 'atasan_langsung_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('pangkat_golongan')->nullable()->after('jabatan_id');
            $table->boolean('is_atasan')->default(false)->after('pangkat_golongan');
            $table->unsignedBigInteger('atasan_langsung_id')->nullable()->after('status_pegawai');
            $table->foreign('atasan_langsung_id')->references('id')->on('pegawais')->onDelete('set null');
        });
    }
};
