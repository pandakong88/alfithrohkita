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
        Schema::create('santri_kamar_histories', function (Blueprint $table) {
            $table->id();
            
            // Tenant isolation
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
                  
            // Relations
            $table->foreignId('santri_id')
                  ->constrained()
                  ->cascadeOnDelete();
                  
            $table->foreignId('kamar_id')
                  ->constrained()
                  ->cascadeOnDelete();
                  
            // Timeline
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Audit log
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
                  
            $table->timestamps();
            
            // Indices
            $table->index(['pondok_id', 'santri_id']);
            $table->index(['pondok_id', 'kamar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_kamar_histories');
    }
};
