<?php

use App\Models\SystemSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic database backup — runs hourly, checks configured day + hour from settings
Schedule::command('db:backup --scheduled')
    ->hourly()
    ->withoutOverlapping()
    ->when(function () {
        try {
            $enabled = SystemSetting::getValue('backup.schedule_enabled', '0');
            if ($enabled !== '1') {
                return false;
            }

            $day  = (int) SystemSetting::getValue('backup.schedule_day', '5');
            $time = SystemSetting::getValue('backup.schedule_time', '08:00');
            [$h]  = explode(':', $time);

            $now = now();
            return $now->dayOfWeek === $day && $now->hour === (int) $h;
        } catch (\Throwable) {
            return false;
        }
    });
