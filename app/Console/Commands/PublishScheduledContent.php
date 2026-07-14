<?php

namespace App\Console\Commands;

use App\Models\CptEntry;
use App\Models\Page;
use Illuminate\Console\Command;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';

    protected $description = 'Flip scheduled pages/CPT entries to published when their published_at time arrives';

    public function handle(): int
    {
        $total = 0;

        foreach ([Page::class, CptEntry::class] as $model) {
            // Iterate + ->save() per row instead of mass update() so that
            // saved() listeners fire: PageCache::purgeAll, sitemap.xml cache
            // invalidation, PingSitemap dispatch, and the page.published
            // webhook all depend on Eloquent events.
            $items = $model::query()
                ->where('status', 'scheduled')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->get();

            foreach ($items as $item) {
                $item->status = 'published';
                $item->save();
            }

            $count = $items->count();
            if ($count > 0) {
                $this->info(class_basename($model).": {$count} scheduled items published.");
                $total += $count;
            }
        }

        if ($total === 0) {
            $this->line('Nothing to publish.');
        }

        return self::SUCCESS;
    }
}
