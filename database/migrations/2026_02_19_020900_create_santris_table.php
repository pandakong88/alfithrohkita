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
        Schema::create('santris', function (Blueprint $table) {
            $table->id();
        
            // Tenant
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            // Relasi wali
            $table->foreignId('wali_id')
                  ->constrained('walis')
                  ->restrictOnDelete();
        
            // Identitas utama
            $table->string('nis');
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
        
            // Kontak & alamat
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
        
            // Status lifecycle
            $table->enum('status', [
                'active',
                'nonaktif',
                'lulus',
                'keluar'
            ])->default('active');
        
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
        
            // Audit
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
        
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
        
            $table->softDeletes();
            $table->timestamps();
        
            // Constraint penting SaaS
            $table->unique(['pondok_id', 'nis']);
        
            // Index performa
            $table->index(['pondok_id', 'status']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santris');
    }
};
