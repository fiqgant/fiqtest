<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admins
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // Academic Periods
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Courses
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Course Offerings
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_period_id')->constrained()->onDelete('cascade');
            $table->string('class_name');
            $table->timestamps();
            $table->unique(['course_id', 'academic_period_id', 'class_name']);
        });

        // Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Course Offering Students (Enrollment)
        Schema::create('course_offering_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['course_offering_id', 'student_id']);
        });

        // Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->datetime('opens_at');
            $table->datetime('closes_at');
            $table->integer('duration_minutes')->default(120);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('show_score_immediately')->default(true);
            $table->integer('easy_count')->default(0);
            $table->integer('medium_count')->default(0);
            $table->integer('hard_count')->default(0);
            $table->decimal('easy_weight', 5, 2)->default(20.00);
            $table->decimal('medium_weight', 5, 2)->default(30.00);
            $table->decimal('hard_weight', 5, 2)->default(50.00);
            $table->timestamps();
        });

        // Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->text('description');
            $table->decimal('default_weight', 5, 2)->default(10.00);
            $table->json('test_cases');
            $table->text('starter_code')->nullable();
            $table->string('language')->default('python');
            $table->timestamps();
        });

        // Attempts
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->datetime('started_at');
            $table->datetime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->decimal('total_score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        // Attempt Questions
        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->text('code')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });

        // Submissions
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_question_id')->constrained()->onDelete('cascade');
            $table->text('code');
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->enum('status', ['running', 'success', 'error', 'timeout'])->default('running');
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('attempt_questions');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('course_offering_students');
        Schema::dropIfExists('students');
        Schema::dropIfExists('course_offerings');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('admins');
    }
};
