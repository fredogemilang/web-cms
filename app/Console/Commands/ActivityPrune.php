<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('activity:prune {--older-than=90 : Days to keep; rows older than this are deleted} {--dry-run : Count only, no delete}')]
#[Description('Delete audit log entries older than the retention window. Default: 90 days. Set via setting activity_retention_days.')]
class ActivityPrune extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('older-than');
        // Respect setting if no explicit --older-than was passed
        if ($days === 90) {
            $days = (int) setting('activity_retention_days', 90);
        }
        if ($days < 1) {
            $this->error("Retention days must be >= 1 (got {$days}).");

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $query = Activity::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("No activities older than {$days} days. Nothing to prune.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} activities older than {$cutoff->toDateString()} ({$days} days).");

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Pruned {$deleted} activities older than {$cutoff->toDateString()} ({$days} days).");

        return self::SUCCESS;
    }
}
