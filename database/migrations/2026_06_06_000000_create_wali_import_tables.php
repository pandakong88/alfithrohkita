<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wali_import_batches', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
        
            $table->string('filename');
        
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);

            $table->foreignId('committed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
    
            $table->timestamp('committed_at')->nullable();
        
            $table->enum('status', [
                'preview',
                'committed',
                'failed'
            ])->default('preview');
        
            $table->timestamps();
        
            $table->index(['pondok_id', 'status']);
        });

        Schema::create('wali_import_rows', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('batch_id')
                  ->constrained('wali_import_batches')
                  ->cascadeOnDelete();
        
            $table->integer('row_number');
        
            $table->json('payload'); // original row data
            $table->json('errors')->nullable();
        
            $table->enum('mode',['insert','update','skip','error'])->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamps();
        
            $table->index(['batch_id', 'is_valid','row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_import_rows');
        Schema::dropIfExists('wali_import_batches');
    }
};
