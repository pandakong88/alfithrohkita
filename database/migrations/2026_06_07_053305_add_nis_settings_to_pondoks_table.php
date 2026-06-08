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
        Schema::table('pondoks', function (Blueprint $table) {
            $table->string('nis_pattern')->default('[YEAR][SEQ:4]')->nullable()->after('is_active');
            $table->boolean('nis_auto_generate')->default(false)->after('nis_pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pondoks', function (Blueprint $table) {
            $table->dropColumn(['nis_pattern', 'nis_auto_generate']);
        });
    }
};
