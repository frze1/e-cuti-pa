<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel pegawais
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke tabel users
            $table->string('nip', 25)->nullable()->unique();
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->unsignedBigInteger('atasan_langsung_id')->nullable(); // Self-referencing dipindah ke sini
            $table->integer('sisa_cuti_n')->default(12);
            $table->integer('sisa_cuti_n_1')->default(0);
            $table->integer('sisa_cuti_n_2')->default(0);
            $table->timestamps();

            // Setup foreign key untuk atasan
            $table->foreign('atasan_langsung_id')->references('id')->on('pegawais')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
