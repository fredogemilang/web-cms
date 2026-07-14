<?php

namespace App\Settings\Actions;

use App\Mail\Transport\BrevoApiTransport;
use App\Settings\Contracts\SettingsAction;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class BrevoTestEmailAction implements SettingsAction
{
    public function handle(array $values): array
    {
        $apiKey = (string) ($values['brevo_api_key'] ?? '');
        $senderEmail = (string) ($values['brevo_sender_email'] ?? '');
        $senderName = (string) ($values['brevo_sender_name'] ?? '');

        $user = auth()->user();
        if (! $user?->email) {
            return ['type' => 'error', 'message' => 'No logged-in user email to send the test to.'];
        }
        if ($apiKey === '' || $senderEmail === '') {
            return ['type' => 'error', 'message' => 'API key and sender email are required. Save the form first if you just edited them.'];
        }

        try {
            $transport = new BrevoApiTransport($apiKey, $senderEmail, $senderName ?: null);

            $email = (new Email)
                ->from(new Address($senderEmail, $senderName ?: $senderEmail))
                ->to(new Address($user->email, $user->name))
                ->subject('Brevo test from '.config('app.name'))
                ->text('If you got this, Brevo API delivery is wired up correctly.')
                ->html('<p>If you got this, Brevo API delivery is wired up correctly.</p><p style="color:#6F767E;font-size:12px">Sent '.now()->toAtomString().'</p>');

            $envelope = new Envelope($email->getFrom()[0], $email->getTo());
            $transport->send($email, $envelope);

            return ['type' => 'success', 'message' => "Test email sent to {$user->email}. Check your inbox."];
        } catch (\Throwable $e) {
            return ['type' => 'error', 'message' => 'Send failed: '.$e->getMessage()];
        }
    }
}
