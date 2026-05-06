<?php

namespace Plugins\Events\Services;

/**
 * Service responsible for seeding default email templates when an event is created.
 * These templates map to approval_types categories: default, pending, approved, rejected.
 *
 * Referenced by: PRD 01 (Event Creation) section 1.3 and PRD 07 (Email Customization).
 */

use Plugins\Events\Models\Event;

class ApprovalTypeService
{
    /**
     * Seed default approval type templates for a newly created event.
     * Idempotent: only creates rows that don't already exist.
     *
     * Default subjects and bodies use Blade-style variable interpolation.
     * Actual body content is deferred to the email-templates plugin to render.
     */
    public function seedDefaultTemplates(Event $event): void
    {
        // Guard: skip if ApprovalType model is not yet available (email-templates plugin not loaded).
        // Bug fix #3: log a warning instead of silently skipping so developers know templates were not seeded.
        if (!class_exists(\Plugins\EmailTemplates\Models\ApprovalType::class)) {
            \Illuminate\Support\Facades\Log::warning(
                "ApprovalTypeService: EmailTemplates plugin unavailable. " .
                "Default email templates were NOT seeded for event #{$event->id} ({$event->title}). " .
                "Run again after installing the email-templates plugin."
            );
            return;
        }

        $defaults = [
            'default' => [
                'type_name' => 'Default Registration',
                'subject' => "Registration Confirmed: {$event->title}",
                'body' => $this->buildDefaultBody($event, 'default'),
            ],
            'pending' => [
                'type_name' => 'Pending Approval',
                'subject' => "Registration Pending: {$event->title}",
                'body' => $this->buildDefaultBody($event, 'pending'),
            ],
            'approved' => [
                'type_name' => 'Registration Approved',
                'subject' => "Registration Approved: {$event->title}",
                'body' => $this->buildDefaultBody($event, 'approved'),
            ],
            'rejected' => [
                'type_name' => 'Registration Rejected',
                'subject' => "Registration Declined: {$event->title}",
                'body' => $this->buildDefaultBody($event, 'rejected'),
            ],
        ];

        $ApprovalType = \Plugins\EmailTemplates\Models\ApprovalType::class;

        foreach ($defaults as $cat => $data) {
            $ApprovalType::firstOrCreate(
                ['event_id' => $event->id, 'cat' => $cat],
                [
                    'type_name' => $data['type_name'],
                    'email_subject' => $data['subject'],
                    'email_body' => $data['body'],
                ]
            );
        }
    }

    /**
     * Build default email body content based on category.
     */
    protected function buildDefaultBody(Event $event, string $category): string
    {
        $messages = [
            'default'  => "Thank you for registering for {$event->title}. We look forward to seeing you!",
            'pending'  => "Your registration for {$event->title} is pending approval. We will notify you once confirmed.",
            'approved' => "Your registration for {$event->title} has been approved. See you there!",
            'rejected' => "Unfortunately, your registration for {$event->title} has been declined.",
        ];

        $message = $messages[$category] ?? "You have registered for {$event->title}.";

        return "<p>{$message}</p><p>If you have any questions, please contact the event organizer.</p>";
    }
}
