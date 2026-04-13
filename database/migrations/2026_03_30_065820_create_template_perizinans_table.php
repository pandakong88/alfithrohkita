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
        
            $table->string('slug');
        
            $table->text('deskripsi')->nullable();
        
            $table->longText('format_surat')->nullable();
        
            $table->tinyInteger('layout_print')->default(1);

            $table->json('required_variables')->nullable();
    
            // Kolom untuk file PDF (jika user pilih upload)
            $table->string('file_pdf')->nullable();
        
            $table->boolean('is_default')->default(false);
        
            $table->boolean('is_active')->default(true);
        
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            $table->timestamps();
        
            $table->unique(['pondok_id', 'slug']);
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
