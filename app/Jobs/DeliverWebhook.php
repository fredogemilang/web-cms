<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Backoff schedule in seconds (1m, 5m, 30m, 2h, 12h). */
    public const BACKOFF = [60, 300, 1800, 7200, 43200];

    public int $tries = 1; // Manual retry inside handle() lets us persist attempts.

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::with('webhook')->find($this->deliveryId);
        if (! $delivery || ! $delivery->webhook || ! $delivery->webhook->is_active) {
            return;
        }

        $webhook = $delivery->webhook;
        $body = json_encode($delivery->payload);
        $signature = hash_hmac('sha256', $body, $webhook->signing_secret);

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Event' => $delivery->event,
            'User-Agent' => 'WebCMS-Webhooks/1.0',
        ], (array) $webhook->headers);

        try {
            $response = Http::timeout(15)->withHeaders($headers)->withBody($body, 'application/json')->post($webhook->url);
            $code = $response->status();
            $delivery->response_code = $code;
            $delivery->response_body = mb_substr($response->body(), 0, 4000);
            $delivery->attempts = $delivery->attempts + 1;

            if ($response->successful()) {
                $delivery->status = 'success';
                $delivery->delivered_at = now();
                $delivery->next_retry_at = null;
            } else {
                $this->scheduleRetryOrFail($delivery);
            }
        } catch (\Throwable $e) {
            $delivery->response_code = 0;
            $delivery->response_body = mb_substr($e->getMessage(), 0, 4000);
            $delivery->attempts = $delivery->attempts + 1;
            $this->scheduleRetryOrFail($delivery);
        }

        $delivery->save();
    }

    protected function scheduleRetryOrFail(WebhookDelivery $delivery): void
    {
        // After the Nth attempt fails, the next retry should use the Nth entry
        // of BACKOFF (0-indexed). attempts=1 → BACKOFF[0]=60s, attempts=2 →
        // BACKOFF[1]=300s, …, attempts=5 → no slot left → mark failed.
        $idx = $delivery->attempts - 1;
        if ($idx < 0 || $idx >= count(self::BACKOFF)) {
            $delivery->status = 'failed';
            $delivery->next_retry_at = null;

            return;
        }

        $delay = self::BACKOFF[$idx];
        $delivery->status = 'retrying';
        $delivery->next_retry_at = now()->addSeconds($delay);
        static::dispatch($delivery->id)->delay(now()->addSeconds($delay));
    }
}
