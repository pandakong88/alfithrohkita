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
        // 1. Create perizinan_approvals table
        Schema::create('perizinan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')->constrained()->cascadeOnDelete();
            $table->foreignId('perizinan_id')->constrained()->cascadeOnDelete();
            $table->integer('step_index');
            $table->string('step_name');
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 2. Add current_step to perizinans table
        Schema::table('perizinans', function (Blueprint $table) {
            $table->integer('current_step')->default(1)->after('template_perizinan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinan_approvals');

        Schema::table('perizinans', function (Blueprint $table) {
            $table->dropColumn('current_step');
        });
    }
};
