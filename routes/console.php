<?php

use App\Models\SystemSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic database backup — runs every minute, fires only on the configured day + exact time
Schedule::command('db:backup --scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->when(function () {
        try {
            if (SystemSetting::getValue('backup.schedule_enabled', '0') !== '1') {
                return false;
            }

            $day        = (int) SystemSetting::getValue('backup.schedule_day', '5');
            [$h, $m]    = explode(':', SystemSetting::getValue('backup.schedule_time', '08:00'));
            $now        = now();

            return $now->dayOfWeek === $day
                && $now->hour   === (int) $h
                && $now->minute === (int) $m;
        } catch (\Throwable) {
            return false;
        }
    });
