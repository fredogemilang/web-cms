<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Symfony Mailer transport that delivers via Brevo's transactional HTTP API
 * (POST https://api.brevo.com/v3/smtp/email). Used when outbound SMTP is blocked.
 */
class BrevoApiTransport extends AbstractTransport
{
    protected const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(
        protected string $apiKey,
        protected ?string $defaultSenderEmail = null,
        protected ?string $defaultSenderName = null,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'brevo+api://'.substr($this->apiKey, 0, 12).'...';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $envelope = $message->getEnvelope();

        $payload = $this->buildPayload($email, $envelope);

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])
            ->timeout(30)
            ->retry(2, 250, throw: false)
            ->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            $body = $response->json() ?: ['error' => $response->body()];
            $code = $body['code'] ?? 'http_'.$response->status();
            $msg = $body['message'] ?? 'Unknown Brevo error';

            throw new TransportException(
                "Brevo API send failed [{$code}]: {$msg}",
                $response->status(),
            );
        }

        // Brevo returns { "messageId": "<...>" }
        if ($id = $response->json('messageId')) {
            $message->getOriginalMessage()->getHeaders()->addHeader('X-Brevo-Message-Id', $id);
        }
    }

    protected function buildPayload(Email $email, Envelope $envelope): array
    {
        // Prefer named addresses from the Email; fall back to envelope recipients
        // (envelope is plain addresses without display names).
        $to = $email->getTo() ?: $envelope->getRecipients();

        $payload = [
            'sender' => $this->resolveSender($email, $envelope),
            'to' => $this->mapAddresses($to),
            'subject' => $email->getSubject() ?? '(no subject)',
        ];

        // CC/BCC if present
        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->mapAddresses($cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->mapAddresses($bcc);
        }

        // Reply-To: Brevo accepts a single replyTo
        if ($replyTo = $email->getReplyTo()) {
            $first = $replyTo[0];
            $payload['replyTo'] = ['email' => $first->getAddress()];
            if ($first->getName()) {
                $payload['replyTo']['name'] = $first->getName();
            }
        }

        // Body
        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = (string) $html;
        }
        if ($text = $email->getTextBody()) {
            $payload['textContent'] = (string) $text;
        }
        if (! isset($payload['htmlContent']) && ! isset($payload['textContent'])) {
            // Brevo requires at least one — fall back to subject as body
            $payload['textContent'] = $payload['subject'];
        }

        // Attachments
        $attachments = [];
        foreach ($email->getAttachments() as $part) {
            $headers = $part->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename') ?: 'attachment';
            $attachments[] = [
                'name' => $filename,
                'content' => base64_encode($part->getBody()),
            ];
        }
        if ($attachments) {
            $payload['attachment'] = $attachments;
        }

        // Custom headers (X-*) → Brevo's `headers` map
        $customHeaders = [];
        foreach ($email->getHeaders()->all() as $header) {
            $name = $header->getName();
            if (str_starts_with(strtolower($name), 'x-') && strtolower($name) !== 'x-brevo-message-id') {
                $customHeaders[$name] = $header->getBodyAsString();
            }
        }
        if ($customHeaders) {
            $payload['headers'] = $customHeaders;
        }

        return $payload;
    }

    protected function resolveSender(Email $email, Envelope $envelope): array
    {
        $sender = $email->getFrom()[0] ?? $envelope->getSender();

        $out = [
            'email' => $sender?->getAddress() ?: $this->defaultSenderEmail,
        ];

        $name = $sender?->getName() ?: $this->defaultSenderName;
        if ($name) {
            $out['name'] = $name;
        }

        if (! $out['email']) {
            throw new TransportException('Brevo API send failed: no sender email configured.');
        }

        return $out;
    }

    /**
     * @param  Address[]  $addresses
     * @param  Address[]  $excluding
     */
    protected function mapAddresses(array $addresses, array $excluding = []): array
    {
        $excludeKeys = array_map(fn (Address $a) => strtolower($a->getAddress()), $excluding);

        $out = [];
        foreach ($addresses as $a) {
            if (in_array(strtolower($a->getAddress()), $excludeKeys, true)) {
                continue;
            }
            $entry = ['email' => $a->getAddress()];
            if ($a->getName()) {
                $entry['name'] = $a->getName();
            }
            $out[] = $entry;
        }

        return $out;
    }
}
