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
        
            $table->string('key')->unique(); 
            // contoh: santri.nama
        
            $table->string('label'); 
            // contoh: Nama Santri
        
            $table->string('source')->nullable(); 
            // contoh: santri.nama (mapping ke database)
        
            $table->enum('type', ['auto', 'manual'])->default('auto');
            // auto = dari database
            // manual = input user
        
            $table->string('input_type')->nullable(); 
            // text, date, textarea, select (untuk manual input)
        
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
