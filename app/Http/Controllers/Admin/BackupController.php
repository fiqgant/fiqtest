<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DatabaseBackupMail;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function edit(): View
    {
        $email   = SystemSetting::getValue('backup.email', '');
        $day     = SystemSetting::getValue('backup.schedule_day', '5');
        $time    = SystemSetting::getValue('backup.schedule_time', '08:00');
        $enabled = SystemSetting::getValue('backup.schedule_enabled', '0');

        return view('admin.settings.backup', compact('email', 'day', 'time', 'enabled'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'            => ['required', 'email'],
            'schedule_day'     => ['required', 'integer', 'min:0', 'max:6'],
            'schedule_time'    => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'schedule_enabled' => ['nullable', 'boolean'],
        ]);

        SystemSetting::setValue('backup.email', $data['email']);
        SystemSetting::setValue('backup.schedule_day', (string) $data['schedule_day']);
        SystemSetting::setValue('backup.schedule_time', $data['schedule_time']);
        SystemSetting::setValue('backup.schedule_enabled', $request->boolean('schedule_enabled') ? '1' : '0');

        return redirect()->route('admin.settings.backup.edit')->with('success', 'Backup settings saved.');
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $filename = config('database.connections.mysql.database') . '_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';

        [$output, $error] = $this->runDump();

        if ($error) {
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'Dump failed: ' . $error);
        }

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function sendNow(): RedirectResponse
    {
        $email = SystemSetting::getValue('backup.email');
        if (! $email) {
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'Please configure a backup recipient email first.');
        }

        $db        = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = "{$db}_backup_{$timestamp}.sql";
        $tmpPath   = storage_path("app/backups/{$filename}");

        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        [$output, $error] = $this->runDump();

        if ($error) {
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'Dump failed: ' . $error);
        }

        file_put_contents($tmpPath, $output);

        try {
            Mail::to($email)->send(new DatabaseBackupMail(
                filePath:    $tmpPath,
                filename:    $filename,
                database:    $db,
                generatedAt: now()->format('d M Y H:i'),
            ));
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'Mail delivery failed: ' . $e->getMessage());
        }

        @unlink($tmpPath);

        return redirect()->route('admin.settings.backup.edit')
            ->with('success', "Backup sent successfully to {$email}.");
    }

    /**
     * Run mysqldump/mariadb-dump and return [output, errorMessage|null].
     *
     * @return array{0: string, 1: string|null}
     */
    private function runDump(): array
    {
        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Prefer mariadb-dump if available (Alpine-based containers), fall back to mysqldump
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
            // Strip warnings, only return actual errors
            $stderr = trim($process->getErrorOutput());
            $lines  = array_filter(explode("\n", $stderr), fn($l) => ! str_contains($l, 'Deprecated') && ! str_contains($l, 'Warning'));
            $error  = implode("\n", $lines) ?: 'Unknown dump error';
            return ['', $error];
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

        return 'mysqldump'; // last resort
    }
}
