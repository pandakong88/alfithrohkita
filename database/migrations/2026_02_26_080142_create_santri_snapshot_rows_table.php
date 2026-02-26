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
        Schema::create('santri_snapshot_rows', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('batch_id')
                  ->constrained('santri_snapshot_batches')
                  ->cascadeOnDelete();
    
            $table->integer('row_number');
    
            $table->json('payload');
            $table->json('errors')->nullable();
    
            $table->boolean('is_valid')->default(false);
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_snapshot_rows');
    }
};
