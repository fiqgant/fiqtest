<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\BackupDatabase;
use App\Http\Controllers\Controller;
use App\Mail\DatabaseBackupMail;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
            'email'              => ['required', 'email'],
            'schedule_day'       => ['required', 'integer', 'min:0', 'max:6'],
            'schedule_time'      => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'schedule_enabled'   => ['nullable', 'boolean'],
        ]);

        SystemSetting::setValue('backup.email', $data['email']);
        SystemSetting::setValue('backup.schedule_day', (string) $data['schedule_day']);
        SystemSetting::setValue('backup.schedule_time', $data['schedule_time']);
        SystemSetting::setValue('backup.schedule_enabled', $request->boolean('schedule_enabled') ? '1' : '0');

        return redirect()->route('admin.settings.backup.edit')->with('success', 'Backup settings saved.');
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename = "{$db}_backup_" . now()->format('Y-m-d_H-i-s') . '.sql';

        $cmd = ['mysqldump', "--user={$username}", "--host={$host}", "--port={$port}", $db];
        if (! empty($password)) {
            $cmd[] = "--password={$password}";
        }

        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'mysqldump failed: ' . $process->getErrorOutput());
        }

        $output = $process->getOutput();

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
                ->with('error', 'Harap isi email tujuan backup terlebih dahulu.');
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

        $cmd = ['mysqldump', "--user={$username}", "--host={$host}", "--port={$port}", $db];
        if (! empty($password)) {
            $cmd[] = "--password={$password}";
        }

        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return redirect()->route('admin.settings.backup.edit')
                ->with('error', 'mysqldump failed: ' . $process->getErrorOutput());
        }

        file_put_contents($tmpPath, $process->getOutput());

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

        return redirect()->route('admin.settings.backup.edit')
            ->with('success', "Backup berhasil dikirim ke {$email}.");
    }
}
