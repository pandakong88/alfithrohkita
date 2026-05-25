<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambang_sanksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')->constrained('pondoks')->cascadeOnDelete();
            
            $table->string('nama_sanksi'); // Contoh: "Surat Peringatan 1", "Skorsing", "Drop Out"
            $table->integer('minimal_poin'); // Contoh: 50, 100, 250
            $table->text('konsekuensi')->nullable(); // Deskripsi sanksi nya apa
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambang_sanksis');
    }
};
