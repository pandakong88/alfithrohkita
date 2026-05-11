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
        Schema::create('template_variables', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Contoh: santri.nama_lengkap
            $table->string('label');         // Contoh: Nama Santri
            $table->string('source')->nullable(); // Column name di DB
            
            // TAMBAHKAN INI:
            $table->enum('type', ['auto', 'manual', 'formula']); 
            $table->string('input_type')->nullable(); // text, date, select, textarea
            $table->json('options')->nullable();      // Untuk isi dropdown (L/P, dll)
            $table->boolean('is_required')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_variables');
    }
};
