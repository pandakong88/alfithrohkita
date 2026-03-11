<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('nama');

            $table->timestamps();

            $table->unique(['pondok_id','nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};