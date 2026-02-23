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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('pondok_id')->nullable()->index();
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
        
            $table->string('event');              // created, updated, deleted, restored
            $table->string('subject_type');       // User, Santri, etc
            $table->unsignedBigInteger('subject_id')->nullable();
        
            $table->string('description');        // Human readable text
        
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('meta')->nullable();     // IP, user agent, dll
        
            $table->timestamps();
        
            $table->index(['subject_type', 'subject_id']);
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
