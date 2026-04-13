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
        
            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('santri_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('template_perizinan_id')
                ->nullable()
                ->constrained('template_perizinans')
                ->nullOnDelete();
        
            $table->string('kode_surat')->unique();
        
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
        
            $table->text('keterangan')->nullable();
        
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->timestamps();
        
            $table->index(['pondok_id', 'status']);
            $table->index(['santri_id', 'status']);
            $table->index('tanggal_keluar');
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
