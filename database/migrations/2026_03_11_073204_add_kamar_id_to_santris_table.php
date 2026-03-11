<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {

            $table->foreignId('kamar_id')
                ->nullable()
                ->after('wali_id')
                ->constrained('kamars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {

            $table->dropForeign(['kamar_id']);
            $table->dropColumn('kamar_id');

        });
    }
};
