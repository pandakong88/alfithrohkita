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
        Schema::create('import_changes', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('batch_id')
                ->constrained('import_batches')
                ->cascadeOnDelete();
        
            $table->foreignId('row_id')
                ->constrained('import_rows')
                ->cascadeOnDelete();
        
            $table->string('entity');
            // santri
            // wali
            // kamar
            // kelas
            // lemari
        
            $table->unsignedBigInteger('entity_id')->nullable();
        
            $table->string('column_name');
        
            $table->text('old_value')->nullable();
        
            $table->text('new_value')->nullable();
        
            $table->timestamps();
        
            $table->index(['batch_id','entity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_changes');
    }
};
