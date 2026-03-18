<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedInteger('tab_switch_warning_count')
                ->default(1)
                ->after('max_tab_switches');

            $table->unsignedInteger('inactivity_warning_seconds')
                ->default(15)
                ->after('inactivity_limit_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'tab_switch_warning_count',
                'inactivity_warning_seconds',
            ]);
        });
    }
};
