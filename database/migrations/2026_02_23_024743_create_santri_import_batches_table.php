<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_import_batches', function (Blueprint $table) {
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

            $table->enum('status', [
                'preview',
                'committed',
                'failed'
            ])->default('preview');

            $table->timestamps();

            $table->index('pondok_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_import_batches');
    }
};