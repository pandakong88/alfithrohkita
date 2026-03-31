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
        Schema::create('import_fields', function (Blueprint $table) {

            $table->id();
        
            $table->string('field_key')->unique();
            $table->string('label');
        
            $table->string('entity');
            // santri
            // wali
            // kamar
            // lemari
            // lemari_slot
        
            $table->string('column_name')->nullable();
            // kolom database
        
            $table->boolean('is_required')->default(false);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_fields');
    }
};
