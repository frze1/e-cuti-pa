<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('nama_jabatan')->constrained('jabatans')->nullOnDelete();
            $table->boolean('is_struktural')->default(false)->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_struktural']);
        });
    }
};

