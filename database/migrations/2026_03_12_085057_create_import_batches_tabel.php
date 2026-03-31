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
        
        Schema::create('import_batches', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('import_templates')
                ->nullOnDelete();

            $table->string('template_name')->nullable();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('filename');
            $table->string('file_path')->nullable();

            $table->string('entity')->nullable();

            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);

            $table->string('mode_missing_nis')->nullable();
            $table->string('mode_existing_nis')->nullable();

            $table->enum('status', [
                'preview',
                'committed',
                'failed',
                'rolled_back'
            ])->default('preview');

            $table->foreignId('committed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('committed_at')->nullable();

            $table->timestamps();

            $table->index(['pondok_id','status']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
