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
        // 1. Rename 'keterangan' to 'variables' in 'perizinans' table
        Schema::table('perizinans', function (Blueprint $table) {
            $table->renameColumn('keterangan', 'variables');
        });

        // 2. Add 'rules' json column to 'template_perizinans' table
        Schema::table('template_perizinans', function (Blueprint $table) {
            $table->json('rules')->nullable()->after('required_variables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perizinans', function (Blueprint $table) {
            $table->renameColumn('variables', 'keterangan');
        });

        Schema::table('template_perizinans', function (Blueprint $table) {
            $table->dropColumn('rules');
        });
    }
};
