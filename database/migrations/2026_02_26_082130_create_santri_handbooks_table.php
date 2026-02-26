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
        Schema::create('santri_handbooks', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->string('version');
            $table->date('release_date');
        
            $table->enum('status', [
                'draft',
                'published',
                'archived'
            ])->default('draft');
        
            $table->text('description')->nullable();
        
            $table->string('file_path');
        
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
        
            $table->timestamps();
        
            $table->unique(['pondok_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_handbooks');
    }
};
