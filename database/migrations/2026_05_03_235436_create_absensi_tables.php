<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Tabel Sesi (Subuh, Madrasah, Pengajian Malam, dll)
        Schema::create('absensi_sesi', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('pondok_id')
            ->constrained()
            ->cascadeOnDelete();
            $blueprint->string('nama_sesi'); // Contoh: Tahfidz Pagi
            $blueprint->time('jam_mulai');
            $blueprint->time('jam_selesai');
            $blueprint->softDeletes();
            $blueprint->timestamps();
        });

        // 2. Tabel Absensi (Data Utama)
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')
            ->constrained()
            ->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->onDelete('cascade');
            $table->foreignId('sesi_id')->constrained('absensi_sesi');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alfa', 'terlambat']);
            $table->enum('metode', ['scan', 'manual'])->default('manual');
            $table->foreignId('input_by')->constrained('users'); // Pengurus yang input
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Mencegah duplikasi absen santri di sesi & tanggal yang sama
            $table->unique(['pondok_id', 'santri_id', 'sesi_id', 'tanggal'], 'absensi_unique_tenant');
        });

        // 3. Tabel Pelanggaran (Otomatis terisi jika Alfa)
        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->onDelete('cascade');
            $table->foreignId('absensi_id')->nullable()->constrained('absensi'); // Relasi ke absen biar jelas sumbernya
            $table->string('judul_pelanggaran'); // Contoh: "Alfa Sholat Subuh"
            $table->integer('poin');
            $table->date('tanggal');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pelanggaran');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('absensi_sesi');
    }
};