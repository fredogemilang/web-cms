<?php

namespace App\Console\Commands;

use App\Models\CptEntry;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'content:purge-trash {--dry-run : Show counts without deleting}';

    protected $description = 'Permanently delete soft-deleted records older than the configured retention window.';

    public function handle(): int
    {
        $days = (int) setting('content_trash_retention_days', 30);
        $cutoff = now()->subDays($days);
        $dry = (bool) $this->option('dry-run');

        $this->line("Purging items trashed before {$cutoff->toDateTimeString()} (retention: {$days} days)");

        $total = 0;
        foreach ([Page::class, CptEntry::class, Form::class, FormEntry::class, Media::class] as $model) {
            $q = $model::onlyTrashed()->where('deleted_at', '<', $cutoff);
            $count = $q->count();
            if ($count === 0) {
                continue;
            }

            if ($dry) {
                $this->info(class_basename($model).": would purge {$count}");
            } else {
                $q->forceDelete();
                $this->info(class_basename($model).": purged {$count}");
            }
            $total += $count;
        }

        $this->line($dry ? "Dry run: {$total} item(s) eligible." : "Purged total: {$total}");

        return self::SUCCESS;
    }
}
