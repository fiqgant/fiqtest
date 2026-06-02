<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->loadMailSettingsFromDb();
    }

    private function loadMailSettingsFromDb(): void
    {
        try {
            $mailer = SystemSetting::getValue('mail.mailer');

            if (! $mailer) {
                return;
            }

            // Common: from address & name
            config([
                'mail.default'      => $mailer,
                'mail.from.address' => SystemSetting::getValue('mail.from_address', config('mail.from.address')),
                'mail.from.name'    => SystemSetting::getValue('mail.from_name', config('mail.from.name')),
            ]);

            if ($mailer === 'resend') {
                $encKey = SystemSetting::getValue('mail.resend_api_key');
                if ($encKey) {
                    try {
                        $apiKey = Crypt::decryptString($encKey);
                    } catch (\Throwable) {
                        $apiKey = null;
                    }
                    config(['resend.api_key' => $apiKey]);
                }
                return;
            }

            // SMTP
            $password    = null;
            $encPassword = SystemSetting::getValue('mail.password');
            if ($encPassword) {
                try {
                    $password = Crypt::decryptString($encPassword);
                } catch (\Throwable) {}
            }

            config([
                'mail.mailers.smtp.host'       => SystemSetting::getValue('mail.host', ''),
                'mail.mailers.smtp.port'       => (int) SystemSetting::getValue('mail.port', '587'),
                'mail.mailers.smtp.encryption' => SystemSetting::getValue('mail.encryption', 'tls') ?: null,
                'mail.mailers.smtp.username'   => SystemSetting::getValue('mail.username', ''),
                'mail.mailers.smtp.password'   => $password,
            ]);
        } catch (\Throwable) {
            // DB not ready — fall back to .env
        }
    }
}
