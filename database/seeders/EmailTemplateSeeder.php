<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key_name' => 'password_reset',
                'name' => 'Password Reset',
                'subject' => 'Reset Password — {{ site.name }}',
                'description' => 'Sent when a user requests a password reset.',
                'variables' => [
                    'user.name' => 'Name of the recipient',
                    'user.email' => 'Email of the recipient',
                    'reset_url' => 'Full URL with the reset token',
                    'expires_in_minutes' => 'How long until the link expires',
                ],
                'body_html' => <<<'HTML'
                    <p>Halo {{ user.name }},</p>
                    <p>Klik tombol berikut untuk reset password Anda:</p>
                    <p><a href="{{ reset_url }}" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none">Reset Password</a></p>
                    <p>Link akan kadaluarsa dalam {{ expires_in_minutes }} menit.</p>
                    <p>— {{ site.name }}</p>
                HTML,
                'is_system' => true,
            ],
            [
                'key_name' => 'user_invited',
                'name' => 'User Invited',
                'subject' => 'Anda diundang ke {{ site.name }}',
                'variables' => [
                    'user.name' => 'Name of the recipient',
                    'inviter.name' => 'Name of the person who invited',
                    'accept_url' => 'URL to set password and accept invite',
                ],
                'body_html' => <<<'HTML'
                    <p>Halo {{ user.name }},</p>
                    <p>{{ inviter.name }} mengundang Anda untuk bergabung di {{ site.name }}.</p>
                    <p><a href="{{ accept_url }}">Terima Undangan</a></p>
                HTML,
                'is_system' => true,
            ],
            [
                'key_name' => 'form_notification',
                'name' => 'Form Submission Notification',
                'subject' => 'Submission baru di form: {{ form.name }}',
                'variables' => [
                    'form.name' => 'Form display name',
                    'form.url' => 'Public URL of the form',
                    'entry.summary' => 'Summary of the submission',
                    'entry.url' => 'Admin URL to view the entry',
                ],
                'body_html' => <<<'HTML'
                    <p>Form <strong>{{ form.name }}</strong> menerima submission baru.</p>
                    <pre>{{ entry.summary }}</pre>
                    <p><a href="{{ entry.url }}">Lihat detail di admin</a></p>
                HTML,
                'is_system' => true,
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(['key_name' => $t['key_name']], $t);
        }
    }
}
