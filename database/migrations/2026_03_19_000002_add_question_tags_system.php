<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags master list
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Pivot: question ↔ tag
        Schema::create('question_tag', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_tag_id')->constrained('question_tags')->onDelete('cascade');
            $table->primary(['question_id', 'question_tag_id']);
        });

        // Add filter to exams
        Schema::table('exams', function (Blueprint $table) {
            $table->json('question_filter_tags')->nullable()->after('max_hints_per_question');
        });

        // Drop old pivot
        Schema::dropIfExists('exam_questions');
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('question_filter_tags');
        });
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('question_tags');
    }
};
