<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('lemaris', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pondok_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('kamar_id')
                ->constrained('kamars')
                ->cascadeOnDelete();

            $table->string('nama');

            $table->string('tipe')->nullable(); 
            // lemari, rak_buku, rak_barang

            $table->integer('jumlah_slot')->default(4);

            $table->timestamps();

            $table->unique(['kamar_id','nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemaris');
    }
};