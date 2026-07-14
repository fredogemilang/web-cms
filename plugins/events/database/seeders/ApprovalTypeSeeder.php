<?php

namespace Plugins\Events\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Events\Models\ApprovalType;

class ApprovalTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Approved types
            [
                'event_id' => 0, // placeholder — seeded per-event in runForEvent()
                'cat' => 'approved',
                'type_name' => 'Approved - General',
                'email_subject' => 'Your registration has been approved!',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nYour registration for {{event_title}} has been approved.\n\nWe look forward to seeing you!\n\nBest regards,\n{{event_organizer}}",
            ],
            // Rejected types
            [
                'event_id' => 0,
                'cat' => 'rejected',
                'type_name' => 'Rejected - Capacity Full',
                'email_subject' => 'Registration update for {{event_title}}',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nThank you for your interest in {{event_title}}. Unfortunately, the event has reached full capacity and we are unable to confirm your registration at this time.\n\nWe encourage you to stay tuned for future events.\n\nBest regards,\n{{event_organizer}}",
            ],
            [
                'event_id' => 0,
                'cat' => 'rejected',
                'type_name' => 'Rejected - Invalid Information',
                'email_subject' => 'Registration update for {{event_title}}',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nThank you for registering for {{event_title}}. Unfortunately, we were unable to verify some of the information provided, and your registration could not be confirmed.\n\nPlease feel free to contact us if you believe this is an error.\n\nBest regards,\n{{event_organizer}}",
            ],
        ];

        // Store templates to be used by EventObserver when new events are created
        // Seed a global fallback row for events that don't have their own templates yet
        foreach ($defaults as $default) {
            ApprovalType::firstOrCreate(
                [
                    'event_id' => 0,
                    'cat' => $default['cat'],
                    'type_name' => $default['type_name'],
                ],
                [
                    'email_subject' => $default['email_subject'],
                    'email_banner' => $default['email_banner'],
                    'email_body' => $default['email_body'],
                ]
            );
        }
    }

    /**
     * Seed default approval types for a specific event.
     * Called by EventObserver after a new event is created.
     */
    public static function seedForEvent(int $eventId): void
    {
        $templates = [
            [
                'cat' => 'approved',
                'type_name' => 'Approved - General',
                'email_subject' => 'Your registration has been approved!',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nYour registration for {{event_title}} has been approved.\n\nWe look forward to seeing you!\n\nBest regards,\n{{event_organizer}}",
            ],
            [
                'cat' => 'rejected',
                'type_name' => 'Rejected - Capacity Full',
                'email_subject' => 'Registration update for {{event_title}}',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nThank you for your interest in {{event_title}}. Unfortunately, the event has reached full capacity.\n\nBest regards,\n{{event_organizer}}",
            ],
            [
                'cat' => 'rejected',
                'type_name' => 'Rejected - Invalid Information',
                'email_subject' => 'Registration update for {{event_title}}',
                'email_banner' => null,
                'email_body' => "Dear {{name}},\n\nThank you for registering for {{event_title}}. Unfortunately, we were unable to verify the information provided.\n\nBest regards,\n{{event_organizer}}",
            ],
        ];

        foreach ($templates as $template) {
            ApprovalType::firstOrCreate(
                [
                    'event_id' => $eventId,
                    'cat' => $template['cat'],
                    'type_name' => $template['type_name'],
                ],
                [
                    'email_subject' => $template['email_subject'],
                    'email_banner' => $template['email_banner'],
                    'email_body' => $template['email_body'],
                ]
            );
        }
    }
}
