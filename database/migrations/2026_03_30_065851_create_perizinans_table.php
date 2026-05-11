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
        
            /*
            |--------------------------------------------------------------------------
            | TENANT
            |--------------------------------------------------------------------------
            */
            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();
        
            /*
            |--------------------------------------------------------------------------
            | RELASI UTAMA
            |--------------------------------------------------------------------------
            */
            $table->foreignId('santri_id')
                ->constrained()
                ->cascadeOnDelete();
        
            // 🔥 WAJIB
            $table->foreignId('template_perizinan_id')
                ->constrained('template_perizinans')
                ->cascadeOnDelete();
        
            /*
            |--------------------------------------------------------------------------
            | IDENTITAS SURAT
            |--------------------------------------------------------------------------
            */
            $table->string('kode_surat');
        
            // 🔥 nomor dari surat fisik (optional tapi penting)
            $table->string('nomor_manual')->nullable();
        
            /*
            |--------------------------------------------------------------------------
            | WAKTU IZIN
            |--------------------------------------------------------------------------
            */
            $table->dateTime('tanggal_keluar');
            $table->dateTime('batas_kembali');
            $table->dateTime('tanggal_kembali')->nullable();
        
            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'aktif',
                'kembali',
                'terlambat',
                'dibatalkan'
            ])->default('aktif');
        
            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            */
            $table->text('keperluan')->nullable();
            // Di migration perizinans
            $table->json('keterangan')->nullable();
            $table->integer('durasi_terlambat_menit')->nullable();

        
            /*
            |--------------------------------------------------------------------------
            | AUDIT
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->timestamps();
        
            /*
            |--------------------------------------------------------------------------
            | INDEX & CONSTRAINT
            |--------------------------------------------------------------------------
            */
            $table->unique(['pondok_id', 'kode_surat']);
        
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
