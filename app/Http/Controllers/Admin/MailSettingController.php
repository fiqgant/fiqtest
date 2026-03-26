<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailSettingController extends Controller
{
    public function edit(): View
    {
        $mailer      = SystemSetting::getValue('mail.mailer', 'smtp');
        $host        = SystemSetting::getValue('mail.host', 'smtp.gmail.com');
        $port        = SystemSetting::getValue('mail.port', '587');
        $encryption  = SystemSetting::getValue('mail.encryption', 'tls');
        $username    = SystemSetting::getValue('mail.username', '');
        $fromAddress = SystemSetting::getValue('mail.from_address', '');
        $fromName    = SystemSetting::getValue('mail.from_name', config('app.name'));
        $hasPassword = ! empty(SystemSetting::getValue('mail.password'));

        return view('admin.settings.mail', compact(
            'mailer', 'host', 'port', 'encryption', 'username',
            'fromAddress', 'fromName', 'hasPassword'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mailer'       => ['required', 'in:smtp,log'],
            'host'         => ['required_if:mailer,smtp', 'nullable', 'string', 'max:255'],
            'port'         => ['required_if:mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'encryption'   => ['nullable', 'in:tls,ssl,'],
            'username'     => ['nullable', 'string', 'max:255'],
            'password'     => ['nullable', 'string', 'max:500'],
            'clear_password' => ['nullable', 'boolean'],
            'from_address' => ['required', 'email'],
            'from_name'    => ['required', 'string', 'max:255'],
        ]);

        SystemSetting::setValue('mail.mailer', $data['mailer']);
        SystemSetting::setValue('mail.host', $data['host'] ?? '');
        SystemSetting::setValue('mail.port', (string) ($data['port'] ?? 587));
        SystemSetting::setValue('mail.encryption', $data['encryption'] ?? 'tls');
        SystemSetting::setValue('mail.username', $data['username'] ?? '');
        SystemSetting::setValue('mail.from_address', $data['from_address']);
        SystemSetting::setValue('mail.from_name', $data['from_name']);

        if (! empty($data['clear_password'])) {
            SystemSetting::setValue('mail.password', null);
        } elseif (! empty($data['password'])) {
            SystemSetting::setValue('mail.password', Crypt::encryptString($data['password']));
        }

        return redirect()->route('admin.settings.mail.edit')
            ->with('success', 'Mail settings saved.');
    }

    public function testSend(Request $request): RedirectResponse
    {
        $to = $request->validate(['test_email' => ['required', 'email']])['test_email'];

        try {
            Mail::raw('Test email dari ' . config('app.name') . '. Konfigurasi SMTP berhasil!', function ($msg) use ($to) {
                $msg->to($to)->subject('[' . config('app.name') . '] Test Email');
            });

            return redirect()->route('admin.settings.mail.edit')
                ->with('success', "Test email berhasil dikirim ke {$to}.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.mail.edit')
                ->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
