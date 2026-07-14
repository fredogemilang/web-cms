<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $adminPath = trim(config('admin.path', 'admin'), '/');
        $url = url("/{$adminPath}/reset-password/{$this->token}?email=".urlencode($notifiable->email));
        $expires = (int) setting('auth_password_reset_expire_minutes', 60);
        $site = setting('site_name', config('app.name', 'Web CMS'));

        // Prefer the admin-editable `password_reset` EmailTemplate when present
        // so customisations made via /admin/email-templates actually take effect.
        $template = EmailTemplate::findByKey('password_reset');
        if ($template) {
            $renderer = app(EmailTemplateRenderer::class);
            $vars = [
                'site' => ['name' => $site, 'url' => url('/')],
                'user' => [
                    'name' => $notifiable->name ?? '',
                    'email' => $notifiable->email,
                ],
                'reset_url' => $url,
                'expires_in_minutes' => $expires,
            ];

            return (new MailMessage)
                ->subject($renderer->renderRaw($template->subject, $vars))
                ->html($renderer->render($template->body_html, $vars));
        }

        return (new MailMessage)
            ->subject("Reset Password — {$site}")
            ->greeting("Halo {$notifiable->name},")
            ->line('Anda menerima email ini karena ada permintaan reset password untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line("Link ini akan kadaluarsa dalam {$expires} menit.")
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }
}
