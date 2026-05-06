<?php

namespace Plugins\Events\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Plugins\Events\Models\ApprovalType;
use Plugins\Events\Models\EventRegistration;

class GuestApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventRegistration $registration,
        public ApprovalType $approvalType
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->resolveTemplateString($this->approvalType->email_subject);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'events::mail.guest-approved',
            with: [
                'registration' => $this->registration,
                'approvalType' => $this->approvalType,
                'bodyHtml' => $this->buildHtmlBody(),
            ]
        );
    }

    /**
     * Build the HTML body by replacing placeholders in the template.
     */
    protected function buildHtmlBody(): string
    {
        $body = $this->approvalType->email_body;
        $reg = $this->registration;
        $event = $reg->event;

        $placeholders = [
            '{{name}}' => $reg->full_name ?? $reg->name,
            '{{email}}' => $reg->email,
            '{{event_title}}' => $event->title ?? '',
            '{{event_date}}' => $event->start_date ? $event->start_date->format('d M Y, H:i') : '',
            '{{event_location}}' => $event->location ?? '',
            '{{event_organizer}}' => config('app.name', 'Event Team'),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $body);
    }

    /**
     * Resolve template string (supports {{placeholder}} syntax).
     */
    protected function resolveTemplateString(string $template): string
    {
        $reg = $this->registration;
        $event = $reg->event;

        $placeholders = [
            '{{name}}' => $reg->full_name ?? $reg->name,
            '{{event_title}}' => $event->title ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
}