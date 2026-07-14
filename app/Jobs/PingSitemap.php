<?php

namespace App\Jobs;

use App\Models\SitemapPing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class PingSitemap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public ?string $changedUrl = null) {}

    public function handle(): void
    {
        $sitemapUrl = url('/sitemap.xml');

        // Google deprecated their ping endpoint in 2023; we still log the intent
        // for tracking and emit IndexNow which both Bing & Yandex honor.
        $this->record('google', $sitemapUrl, 200, 'noop (Google deprecated ping endpoint)');

        // IndexNow protocol — informs Bing, Yandex, Seznam, etc.
        if ($key = setting('seo_indexnow_key')) {
            $host = parse_url(url('/'), PHP_URL_HOST);
            $payload = [
                'host' => $host,
                'key' => $key,
                'urlList' => $this->changedUrl ? [$this->changedUrl] : [$sitemapUrl],
            ];
            try {
                $resp = Http::timeout(10)->post('https://api.indexnow.org/IndexNow', $payload);
                $this->record('indexnow', $sitemapUrl, $resp->status(), $resp->body());
            } catch (\Throwable $e) {
                $this->record('indexnow', $sitemapUrl, 0, $e->getMessage(), 'failed');
            }
        }

        // Bing direct ping
        try {
            $resp = Http::timeout(10)->get('https://www.bing.com/ping', ['sitemap' => $sitemapUrl]);
            $this->record('bing', $sitemapUrl, $resp->status(), $resp->body());
        } catch (\Throwable $e) {
            $this->record('bing', $sitemapUrl, 0, $e->getMessage(), 'failed');
        }
    }

    protected function record(string $target, string $url, int $code, string $body, string $status = 'sent'): void
    {
        SitemapPing::create([
            'target' => $target,
            'url' => $url,
            'status' => $status,
            'response_code' => $code,
            'response_body' => mb_substr($body, 0, 1000),
            'pinged_at' => now(),
        ]);
    }
}
