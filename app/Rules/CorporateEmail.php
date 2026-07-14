<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that an email address is not a free/disposable email provider.
 *
 * When the Events plugin is active, this rule delegates to the canonical
 * implementation in Plugins\Events\Rules\CorporateEmail (which includes
 * a managed free-domain list from the database). When Events is inactive,
 * it falls back to a hardcoded domain list.
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

        // Delegate to the Events plugin's canonical implementation if available
        if (class_exists(\Plugins\Events\Rules\CorporateEmail::class)) {
            $rule = new \Plugins\Events\Rules\CorporateEmail($this->eventId);
            $rule->validate($attribute, $value, $fail);

            return;
        }

        // Fallback: hardcoded domain check only (no DB lookup)
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
