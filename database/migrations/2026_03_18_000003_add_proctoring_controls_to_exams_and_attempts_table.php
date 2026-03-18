<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedInteger('max_tab_switches')->default(3)->after('show_score_immediately');
            $table->unsignedInteger('inactivity_limit_seconds')->default(60)->after('max_tab_switches');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->unsignedInteger('tab_switch_count')->default(0)->after('status');
            $table->timestamp('last_activity_at')->nullable()->after('tab_switch_count');
            $table->boolean('is_disqualified')->default(false)->after('last_activity_at');
            $table->timestamp('disqualified_at')->nullable()->after('is_disqualified');
            $table->string('disqualification_reason')->nullable()->after('disqualified_at');
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn([
                'tab_switch_count',
                'last_activity_at',
                'is_disqualified',
                'disqualified_at',
                'disqualification_reason',
            ]);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'max_tab_switches',
                'inactivity_limit_seconds',
            ]);
        });
    }
};
