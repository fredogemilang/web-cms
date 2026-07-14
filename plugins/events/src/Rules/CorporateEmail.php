<?php

namespace Plugins\Events\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\FreeEmailDomain;

/**
 * Validates that an email address is not a free/disposable email provider
 * when the event requires a corporate email address.
 *
 * Usage:
 *   'email' => [new CorporateEmail($eventId)]
 */
class CorporateEmail implements ValidationRule
{
    protected ?int $eventId = null;

    public function __construct(?int $eventId = null)
    {
        $this->eventId = $eventId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        // Only enforce if event requires corporate email
        if ($this->eventId) {
            $event = Event::find($this->eventId);
            if (! $event || ! $event->requires_corporate_email) {
                return; // Not required — allow any email
            }
        }

        if (! $this->isCorporateEmail($value)) {
            $fail('Corporate email required. Free email providers (gmail.com, yahoo.com, etc.) are not allowed for this event.');
        }
    }

    /**
     * Check if the given email is a corporate (non-free) email.
     */
    protected function isCorporateEmail(string $email): bool
    {
        $domain = strtolower(explode('@', $email)[1] ?? '');

        if (empty($domain)) {
            return false;
        }

        // Check database lookup first (managed list)
        try {
            $blocked = FreeEmailDomain::where('domain', $domain)
                ->where('is_active', true)
                ->exists();

            if ($blocked) {
                return false;
            }
        } catch (\Throwable) {
            // DB not available — fall through to hardcoded list
        }

        // Fallback hardcoded list
        $freeDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'aol.com', 'icloud.com', 'live.com', 'msn.com',
            'ymail.com', 'rocketmail.com', 'mail.com', 'gmx.com',
            'protonmail.com', 'tutanota.com', 'zoho.com',
            'inbox.com', 'rediffmail.com', 'mailinator.com',
            'tempmail.org', '10minutemail.com', 'guerrillamail.com',
        ];

        return ! in_array($domain, $freeDomains);
    }
}
