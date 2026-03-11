<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('lemari_slots', function (Blueprint $table) {

            $table->id();

            $table->foreignId('lemari_id')
                ->constrained('lemaris')
                ->cascadeOnDelete();

            $table->integer('nomor_slot');

            $table->foreignId('santri_id')
                ->nullable()
                ->constrained('santris')
                ->nullOnDelete();

            $table->enum('status',[
                'dipakai',
                'kosong',
                'rusak',
                'barang'
            ])->default('kosong');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique(['lemari_id','nomor_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemari_slots');
    }
};