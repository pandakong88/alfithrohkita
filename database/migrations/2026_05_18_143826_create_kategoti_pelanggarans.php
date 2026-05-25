<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_pelanggarans', function (Blueprint $table) {
            $table->id();
            // Relasi ke pondok wajib untuk SaaS (Multi-tenant)
            $table->foreignId('pondok_id')->constrained('pondoks')->cascadeOnDelete();
            
            $table->string('nama_pelanggaran'); // Contoh: "Membawa HP", "Rambut Gondrong"
            $table->integer('poin');            // Bobot poin yang ditentukan pondok tersebut
            $table->enum('tingkat', ['ringan', 'sedang', 'berat']);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_pelanggarans');
    }
};