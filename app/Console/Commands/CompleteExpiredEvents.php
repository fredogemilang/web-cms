<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CompleteExpiredEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:complete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired events as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventClass = 'Plugins\\Events\\Models\\Event';

        if (! class_exists($eventClass)) {
            $this->warn('Events plugin is not installed — skipping.');

            return self::SUCCESS;
        }

        $this->info('Checking for expired events...');

        $expiredEvents = $eventClass::where('status', 'published')
            ->where('end_date', '<', now())
            ->get();

        if ($expiredEvents->isEmpty()) {
            $this->info('No expired events found.');

            return self::SUCCESS;
        }

        $count = $expiredEvents->count();
        $this->info("Found {$count} expired event(s).");

        foreach ($expiredEvents as $event) {
            $event->update(['status' => 'completed']);
            $endDate = Carbon::parse($event->getAttribute('end_date'));
            $this->line('✓ Completed: '.(string) $event->getAttribute('title').' (ended on '.$endDate->format('Y-m-d H:i').')');
        }

        $this->newLine();
        $this->info("Successfully marked {$count} event(s) as completed!");

        return self::SUCCESS;
    }
}
