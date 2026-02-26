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
        Schema::create('santri_snapshots', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('pondok_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('santri_id')
                  ->constrained('santris')
                  ->cascadeOnDelete();
        
            $table->date('snapshot_date');
        
            $table->enum('status', [
                'active',
                'nonaktif',
                'lulus',
                'keluar'
            ]);
        
            $table->string('kelas')->nullable();
            $table->text('catatan')->nullable();
        
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
        
            $table->timestamps();
        
            $table->unique([
                'pondok_id',
                'santri_id',
                'snapshot_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
