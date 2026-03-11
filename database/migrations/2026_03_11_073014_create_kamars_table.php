<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('kamars', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('komplek_id')
                ->constrained('kompleks')
                ->cascadeOnDelete();

            $table->string('nama');

            $table->integer('kapasitas')->nullable();

            $table->timestamps();

            $table->unique(['komplek_id','nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamars');
    }
};