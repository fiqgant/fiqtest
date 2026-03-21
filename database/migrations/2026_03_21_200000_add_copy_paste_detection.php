<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add detect_copy_paste toggle to exams
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'detect_copy_paste')) {
                $table->boolean('detect_copy_paste')->default(false)->after('disable_inspect');
            }
        });

        // Create copy_paste_logs table
        Schema::create('copy_paste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['copy', 'cut', 'paste']);
            $table->text('content')->nullable();
            $table->unsignedBigInteger('attempt_question_id')->nullable();
            $table->timestamp('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copy_paste_logs');
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('detect_copy_paste');
        });
    }
};
