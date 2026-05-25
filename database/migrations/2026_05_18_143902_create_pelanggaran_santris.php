<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_santris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')->constrained('pondoks')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            
            // Menggunakan opsi Tahun Ajaran untuk mempermudah logika Reset oleh Admin
            // $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            
            // Jika otomatis dari absensi, ini terisi. Jika manual, ini NULL.
            $table->foreignId('absensi_id')->nullable()->constrained('absensi')->nullOnDelete();
            
            // Jika manual, arahkan ke master kategori pelanggaran. Jika otomatis, bisa NULL.
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_pelanggarans')->nullOnDelete();
            
            $table->enum('kategori_sumber', ['otomatis', 'manual'])->default('manual');
            $table->string('judul_pelanggaran'); // Digunakan sebagai fallback nama pelanggaran
            $table->integer('poin'); // Dicatat langsung di sini (snapshot) agar jika master diubah, poin lama tidak ikut berubah
            
            $table->date('tanggal');
            $table->text('catatan_detail')->nullable(); // Kronologi kejadian
            $table->string('foto_bukti')->nullable();    // Path foto untuk Flutter Apps
            
            // Pengurus/Admin yang mencatat pelanggaran tersebut
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); 
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_santris');
    }
};