<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_sesi', function (Blueprint $table) {
            // Kita letakkan setelah nama_sesi agar struktur tabel tetap logis
            $table->enum('target_tipe', ['global', 'kelas', 'kamar', 'plotting','komplek'])
                  ->default('global')
                  ->after('nama_sesi');
            
            $table->boolean('is_active')->default(true)->after('jam_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_sesi', function (Blueprint $table) {
            $table->dropColumn(['target_tipe', 'is_active']);
        });
    }
};