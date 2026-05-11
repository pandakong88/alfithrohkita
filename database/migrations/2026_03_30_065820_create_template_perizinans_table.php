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
        Schema::create('template_perizinans', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->string('nama');
            $table->string('slug', 100); // Batasi panjang slug untuk optimasi index
            $table->text('deskripsi')->nullable();
            
            // Pembeda sumber template
            $table->enum('source_type', ['html', 'upload_pdf'])->default('html');
            
            $table->longText('format_surat')->nullable(); // Untuk mode HTML/Editor
            $table->string('file_pdf')->nullable();       // Untuk mode Upload
            
            $table->tinyInteger('layout_print')->default(1);
            $table->json('required_variables')->nullable();
        
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
        
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->softDeletes();
            $table->timestamps();
        
            // Index gabungan untuk performa pencarian dan validasi unik per pondok
            $table->unique(['pondok_id', 'slug']);
            $table->index(['pondok_id', 'is_active']); // Index tambahan untuk mempercepat list dropdown
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_perizinans');
    }
};
