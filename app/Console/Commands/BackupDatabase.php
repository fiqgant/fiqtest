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
    protected $description = 'Generate a MySQL/MariaDB database dump and send it via email';

    public function handle(): int
    {
        if ($this->option('scheduled') && ! $this->shouldRunNow()) {
            $this->info('Scheduled backup skipped (not the configured day/time or disabled).');
            return self::SUCCESS;
        }

        $email = SystemSetting::getValue('backup.email');
        if (! $email) {
            $this->error('No backup email configured. Go to Admin → Settings → Database Backup.');
            return self::FAILURE;
        }

        $db        = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "{$db}_backup_{$timestamp}.sql";
        $tmpPath   = storage_path("app/backups/{$filename}");

        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $this->info("Generating dump for [{$db}]…");

        [$output, $error] = $this->runDump();

        if ($error) {
            $this->error("Dump failed: {$error}");
            return self::FAILURE;
        }

        file_put_contents($tmpPath, $output);

        $this->info("Dump saved. Sending to {$email}…");

        try {
            Mail::to($email)->send(new DatabaseBackupMail(
                filePath:    $tmpPath,
                filename:    $filename,
                database:    $db,
                generatedAt: now()->format('d M Y H:i'),
            ));
        } finally {
            @unlink($tmpPath);
        }

        $this->info("Backup sent to {$email}.");
        return self::SUCCESS;
    }

    private function shouldRunNow(): bool
    {
        if (SystemSetting::getValue('backup.schedule_enabled', '0') !== '1') {
            return false;
        }

        $day = (int) SystemSetting::getValue('backup.schedule_day', '5');
        [$h] = explode(':', SystemSetting::getValue('backup.schedule_time', '08:00'));
        $now = now();

        return $now->dayOfWeek === $day && $now->hour === (int) $h;
    }

    /** @return array{0: string, 1: string|null} */
    private function runDump(): array
    {
        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $binary = $this->resolveDumpBinary();

        $cmd = [
            $binary,
            "--user={$username}",
            "--host={$host}",
            "--port={$port}",
            '--no-tablespaces',
            '--skip-ssl',
            $db,
        ];

        if (! empty($password)) {
            $cmd[] = "--password={$password}";
        }

        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $lines  = array_filter(explode("\n", $stderr), fn($l) => ! str_contains($l, 'Deprecated') && ! str_contains($l, 'Warning'));
            return ['', implode("\n", $lines) ?: 'Unknown error'];
        }

        return [$process->getOutput(), null];
    }

    private function resolveDumpBinary(): string
    {
        foreach (['mariadb-dump', 'mysqldump'] as $bin) {
            $check = new Process(['which', $bin]);
            $check->run();
            if ($check->isSuccessful() && trim($check->getOutput()) !== '') {
                return $bin;
            }
        }

        return 'mysqldump';
    }
}
