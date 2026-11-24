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
        Schema::create('pengajuan_cutis', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (Pemohon)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Data Form Cuti
            $table->string('jenis_cuti');
            $table->text('alasan');
            $table->text('alamat_selama_cuti')->nullable();
            $table->string('no_telp')->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->integer('durasi_hari');
            
            // Tracking Status & Persetujuan
            $table->enum('status', [
                'MENUNGGU_ATASAN',
                'REVISI_ATASAN',
                'DITANGGUHKAN_ATASAN',
                'MENUNGGU_PENOMORAN',
                'MENUNGGU_KETUA',
                'REVISI_KETUA',
                'DITANGGUHKAN_KETUA',
                'SELESAI_DISETUJUI',
                'DITOLAK'
            ])->default('MENUNGGU_ATASAN');
            
            $table->string('nomor_surat')->nullable(); // Diisi admin kepegawaian
            $table->text('catatan_revisi')->nullable(); // Alasan revisi/tunda dari pimpinan
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cutis');
    }
};
