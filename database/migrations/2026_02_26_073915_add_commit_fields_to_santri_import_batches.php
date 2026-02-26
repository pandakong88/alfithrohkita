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
        Schema::table('santri_import_batches', function (Blueprint $table) {
            $table->foreignId('committed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
    
            $table->timestamp('committed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri_import_batches', function (Blueprint $table) {
            //
        });
    }
};
