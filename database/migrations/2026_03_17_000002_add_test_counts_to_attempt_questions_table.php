<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->unsignedInteger('passed_tests')->default(0)->after('is_correct');
            $table->unsignedInteger('total_tests')->default(0)->after('passed_tests');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->dropColumn(['passed_tests', 'total_tests']);
        });
    }
};
