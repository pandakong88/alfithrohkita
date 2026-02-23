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
        Schema::create('walis', function (Blueprint $table) {
            $table->id();
        
            // Tenant
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            // Data utama
            $table->string('nama');
            $table->string('nik')->nullable();
            $table->string('no_hp')->index();
            $table->text('alamat')->nullable();
            $table->string('pekerjaan')->nullable();
        
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
        
            // Index penting
            $table->index('pondok_id');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walis');
    }
};
