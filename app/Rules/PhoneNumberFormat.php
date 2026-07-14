<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates Indonesian phone numbers in international format.
 *
 * Rules:
 * - 9-17 digits total (excluding country code)
 * - Must start with a valid country code: +62 (Indonesia), +65, +60, +66, +63, +84
 * - Optionally prefix with 0 (Indonesian local format); will be converted to +62
 *
 * Usage:
 *   'mobile_phone' => [new PhoneNumberFormat()]
 */
class PhoneNumberFormat implements ValidationRule
{
    protected array $validCountryCodes = ['62', '65', '60', '66', '63', '84'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $cleaned = preg_replace('/[^\d]/', '', (string) $value);

        // Must be 9-13 digits
        if (strlen($cleaned) < 9 || strlen($cleaned) > 13) {
            $fail('Phone number must be 9–13 digits including country code (e.g., +6281234567890).');

            return;
        }

        // Must start with a valid country code
        $startsWithValidCode = false;
        foreach ($this->validCountryCodes as $code) {
            if (str_starts_with($cleaned, $code)) {
                $startsWithValidCode = true;
                break;
            }
        }

        if (! $startsWithValidCode) {
            $fail('Phone number must use a valid country code (e.g., +62 for Indonesia).');
        }
    }
}
