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
        Schema::create('template_assets', function (Blueprint $table) {
            $table->id();
            
            // Sesuaikan dengan pondok_id agar relasi aman
            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();
                
            $table->string('file_path'); // Path di storage
            $table->string('file_name'); // Nama file asli (misal: logo-pondok.png)
            $table->string('file_type')->default('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_assets');
    }
};
