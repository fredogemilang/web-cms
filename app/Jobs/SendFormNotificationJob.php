<?php

namespace App\Jobs;

use App\Models\Form;
use App\Models\FormEntry;
use App\Services\FormNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches all configured notifications (admin + user confirmation) for one form
 * submission. Runs on the queue so the public POST returns quickly even if mail
 * delivery is slow (e.g. Brevo API round-trip).
 *
 * Backward-compatible: when QUEUE_CONNECTION=sync the job runs inline, identical
 * to the pre-queue behaviour.
 */
class SendFormNotificationJob implements ShouldQueue
{
    use Queueable;

    /** Retry the whole notification chain up to 3 times with backoff. */
    public int $tries = 3;

    public array $backoff = [60, 300, 900]; // 1m → 5m → 15m

    public function __construct(
        public int $formId,
        public int $entryId,
    ) {}

    public function handle(FormNotificationService $service): void
    {
        $form = Form::with('fields')->find($this->formId);
        $entry = FormEntry::find($this->entryId);

        if (! $form || ! $entry) {
            // Entry/form deleted between dispatch and handling — nothing to do.
            return;
        }

        $service->sendNotifications($form, $entry);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Form notification job permanently failed', [
            'form_id' => $this->formId,
            'entry_id' => $this->entryId,
            'error' => $e->getMessage(),
        ]);
    }
}
