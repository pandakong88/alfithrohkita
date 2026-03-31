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
        Schema::create('import_template_fields', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('template_id')
                ->constrained('import_templates')
                ->cascadeOnDelete();
        
            $table->foreignId('field_id')
                ->constrained('import_fields')
                ->cascadeOnDelete();
        
            $table->integer('order');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_template_fields');
    }
};
