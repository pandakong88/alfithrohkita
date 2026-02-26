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
        Schema::create('santri_snapshot_batches', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
    
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
    
            $table->date('snapshot_date');
    
            $table->string('filename');
    
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);
    
            $table->enum('status', ['preview','committed'])
                  ->default('preview');
    
            $table->timestamp('committed_at')->nullable();
            $table->foreignId('committed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
    
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_snapshot_batches');
    }
};
