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
        Schema::create('import_rows', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('batch_id')
                  ->constrained('import_batches')
                  ->cascadeOnDelete();
        
            $table->integer('row_number');
        
            $table->json('payload');
            $table->json('errors')->nullable();
        
            $table->enum('mode',['insert','update','skip','error'])->nullable();
        
            $table->boolean('is_valid')->default(false);
        
            $table->timestamps();
        
            $table->index(['batch_id','is_valid','row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
