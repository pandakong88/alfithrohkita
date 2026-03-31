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
        Schema::create('perizinans', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('santri_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('template_perizinan_id')
                ->constrained('template_perizinans')
                ->cascadeOnDelete();
        
            $table->dateTime('tanggal_keluar');
        
            $table->dateTime('batas_kembali');
        
            $table->dateTime('tanggal_kembali')->nullable();
        
            $table->enum('status', [
                'aktif',
                'kembali',
                'terlambat',
                'dibatalkan'
            ])->default('aktif');
        
            $table->text('keperluan')->nullable();
        
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinans');
    }
};
