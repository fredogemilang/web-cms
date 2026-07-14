<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use App\Models\Setting;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Support\ServiceProvider;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Defer touching settings until boot to avoid hitting the DB during package discovery.
    }

    public function boot(): void
    {
        // Register the 'brevo' mail driver with Laravel's MailManager.
        // Always available — only used when the Brevo mailer is referenced.
        $this->app->afterResolving(MailFactory::class, function (MailFactory $mailer) {
            $mailer->extend('brevo', function (array $config = []) {
                return new BrevoApiTransport(
                    apiKey: (string) Setting::get('brevo_api_key', ''),
                    defaultSenderEmail: Setting::get('brevo_sender_email') ?: null,
                    defaultSenderName: Setting::get('brevo_sender_name') ?: null,
                );
            });
        });

        // When the toggle is on, point Laravel at the brevo mailer + inject from-address.
        // Wrap in try/catch so tests / fresh installs (before settings table is migrated)
        // don't fail to boot.
        try {
            if (Setting::get('brevo_enabled', false)) {
                $this->configureMailUsingBrevo();
            }
        } catch (\Throwable $e) {
            // Settings table not yet migrated — silently skip Brevo configuration.
        }
    }

    protected function configureMailUsingBrevo(): void
    {
        // Make sure config has a 'brevo' mailer entry the factory can resolve
        config([
            'mail.mailers.brevo' => ['transport' => 'brevo'],
            'mail.default' => 'brevo',
        ]);

        // Override From if sender configured
        $senderEmail = Setting::get('brevo_sender_email');
        $senderName = Setting::get('brevo_sender_name');
        if ($senderEmail) {
            config([
                'mail.from.address' => $senderEmail,
                'mail.from.name' => $senderName ?: config('mail.from.name'),
            ]);
        }
    }
}
