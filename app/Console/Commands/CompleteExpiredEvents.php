<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $this->info('Checking for expired events...');

        $expiredEvents = \Plugins\Events\Models\Event::where('status', 'published')
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
            $this->line("✓ Completed: {$event->title} (ended on {$event->end_date->format('Y-m-d H:i')})");
        }

        $this->newLine();
        $this->info("Successfully marked {$count} event(s) as completed!");

        return self::SUCCESS;
    }
}
