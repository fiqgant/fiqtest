<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add type + new fields to questions
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['coding', 'multiple_choice', 'multiple_select', 'true_false', 'fill_in_blank', 'essay'])
                  ->default('coding')
                  ->after('id');
            $table->text('fill_blank_answer')->nullable()->after('test_cases');
            $table->boolean('true_false_answer')->nullable()->after('fill_blank_answer');

            // Make coding-specific columns nullable
            $table->text('starter_code')->nullable()->change();
            $table->text('test_cases')->nullable()->change();
        });

        // Options for MC / MS / TF
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        // Student answers + manual grading on attempt_questions
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->text('student_answer')->nullable()->after('code');
            $table->decimal('manual_score', 5, 2)->nullable()->after('score');
            $table->text('manual_feedback')->nullable()->after('manual_score');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->dropColumn(['student_answer', 'manual_score', 'manual_feedback']);
        });

        Schema::dropIfExists('question_options');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'fill_blank_answer', 'true_false_answer']);
        });
    }
};
