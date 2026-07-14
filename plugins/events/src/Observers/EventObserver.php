<?php

namespace Plugins\Events\Observers;

use Plugins\EmailTemplates\Models\ApprovalType;
use Plugins\Events\Models\Event;
use Plugins\Events\Services\ApprovalTypeService;

/**
 * Observer for Event model lifecycle.
 * Handles side-effects after Event is created/updated/deleted.
 */
class EventObserver
{
    public function __construct(
        protected ApprovalTypeService $approvalTypeService
    ) {}

    /**
     * After an Event is created, seed default email templates.
     * Idempotent — ApprovalTypeService::seedDefaultTemplates skips if rows exist.
     */
    public function created(Event $event): void
    {
        $this->approvalTypeService->seedDefaultTemplates($event);
    }

    /**
     * After an Event is deleted, cascade delete related approval types.
     * Handled by DB foreign key if set; kept here for explicit clarity.
     */
    public function deleted(Event $event): void
    {
        if (class_exists(ApprovalType::class)) {
            ApprovalType::where('event_id', $event->id)->delete();
        }
    }
}
