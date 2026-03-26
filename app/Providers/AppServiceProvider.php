<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMailSettingsFromDb();
    }

    private function loadMailSettingsFromDb(): void
    {
        try {
            $mailer = SystemSetting::getValue('mail.mailer');

            // Only override if the admin has explicitly configured mail settings
            if (! $mailer) {
                return;
            }

            $password    = null;
            $encPassword = SystemSetting::getValue('mail.password');
            if ($encPassword) {
                try {
                    $password = Crypt::decryptString($encPassword);
                } catch (\Throwable) {
                    //
                }
            }

            config([
                'mail.default'                      => $mailer,
                'mail.mailers.smtp.host'            => SystemSetting::getValue('mail.host', ''),
                'mail.mailers.smtp.port'            => (int) SystemSetting::getValue('mail.port', '587'),
                'mail.mailers.smtp.encryption'      => SystemSetting::getValue('mail.encryption', 'tls') ?: null,
                'mail.mailers.smtp.username'        => SystemSetting::getValue('mail.username', ''),
                'mail.mailers.smtp.password'        => $password,
                'mail.from.address'                 => SystemSetting::getValue('mail.from_address', config('mail.from.address')),
                'mail.from.name'                    => SystemSetting::getValue('mail.from_name', config('mail.from.name')),
            ]);
        } catch (\Throwable) {
            // DB not ready (e.g. during migrations) — fall back to .env config
        }
    }
}
