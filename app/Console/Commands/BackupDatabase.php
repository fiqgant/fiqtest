<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupMail;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup {--scheduled : Only run if schedule is enabled and matches current day/hour}';
    protected $description = 'Generate a MySQL database dump and send it via email';

    public function handle(): int
    {
        if ($this->option('scheduled')) {
            if (! $this->shouldRunNow()) {
                $this->info('Scheduled backup skipped (not the configured day/time or disabled).');
                return self::SUCCESS;
            }
        }

        $email = SystemSetting::getValue('backup.email');
        if (! $email) {
            $this->error('No backup email configured. Go to Admin → Settings → Database Backup to set one.');
            return self::FAILURE;
        }

        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "{$db}_backup_{$timestamp}.sql";
        $tmpPath   = storage_path("app/backups/{$filename}");

        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $this->info("Generating database dump for [{$db}]…");

        $cmd = ['mysqldump', "--user={$username}", "--host={$host}", "--port={$port}", $db];
        if (! empty($password)) {
            $cmd[] = "--password={$password}";
        }

        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('mysqldump failed: ' . $process->getErrorOutput());
            return self::FAILURE;
        }

        file_put_contents($tmpPath, $process->getOutput());

        $this->info("Dump saved ({$filename}). Sending to {$email}…");

        try {
            Mail::to($email)->send(new DatabaseBackupMail(
                filePath:    $tmpPath,
                filename:    $filename,
                database:    $db,
                generatedAt: now()->format('d M Y H:i'),
            ));
        } finally {
            // Delete the temp file after mail is sent (or attempted)
            @unlink($tmpPath);
        }

        $this->info("Backup email sent successfully to {$email}.");
        return self::SUCCESS;
    }

    private function shouldRunNow(): bool
    {
        $enabled = SystemSetting::getValue('backup.schedule_enabled', '0');
        if ($enabled !== '1') {
            return false;
        }

        $day  = (int) SystemSetting::getValue('backup.schedule_day', '5');   // 5 = Friday
        $time = SystemSetting::getValue('backup.schedule_time', '08:00');
        [$h]  = explode(':', $time);

        $now = now();
        return $now->dayOfWeek === $day && $now->hour === (int) $h;
    }
}
