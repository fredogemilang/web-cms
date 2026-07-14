<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event dispatched while building /sitemap.xml.
 * Plugins can listen to this event to inject their public URLs.
 *
 * Each entry shape:
 *   [
 *     'loc'        => 'https://example.com/...',  // required, absolute URL
 *     'lastmod'    => \Carbon\Carbon|string|null, // optional ISO 8601
 *     'changefreq' => 'daily'|'weekly'|...|null,  // optional
 *     'priority'   => 0.0–1.0|null,               // optional
 *   ]
 */
class BuildSitemap
{
    use Dispatchable;

    /** @var array<int, array{loc:string, lastmod?:mixed, changefreq?:string, priority?:float}> */
    public array $urls = [];

    public function add(string $loc, mixed $lastmod = null, ?string $changefreq = null, ?float $priority = null): self
    {
        $this->urls[] = array_filter([
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ], fn ($v) => $v !== null);

        return $this;
    }

    public function getUrls(): array
    {
        return $this->urls;
    }
}
