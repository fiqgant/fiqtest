<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add hint content to questions
        Schema::table('questions', function (Blueprint $table) {
            $table->text('hint')->nullable()->after('starter_code');
        });

        // Add hint settings to exams
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('hints_enabled')->default(false)->after('show_score_immediately');
            $table->unsignedTinyInteger('max_hints_per_question')->default(1)->after('hints_enabled');
        });

        // Track how many times each student used hint per question
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->unsignedTinyInteger('hint_used_count')->default(0)->after('total_tests');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->dropColumn('hint_used_count');
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['hints_enabled', 'max_hints_per_question']);
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('hint');
        });
    }
};
