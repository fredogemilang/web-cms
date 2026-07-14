<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * RFC 6238 TOTP — no external dependency.
 *
 * Secrets are persisted encrypted (Laravel Crypt) so even a DB leak does not
 * yield working OTPs without APP_KEY.
 */
class TwoFactorService
{
    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::lower(Str::random(5)).'-'.Str::lower(Str::random(5)))
            ->all();
    }

    public function otpauthUri(User $user, string $secret): string
    {
        $issuer = rawurlencode((string) (setting('site_name') ?? config('app.name', 'WebCMS')));
        $label = rawurlencode("{$issuer}:{$user->email}");
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => urldecode($issuer),
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }
        $timestamp = floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->computeOtp($secret, $timestamp + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }
        try {
            $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
        } catch (\Throwable $e) {
            return false;
        }
        // Recovery codes are generated lowercase (see generateRecoveryCodes);
        // accept user input regardless of case, surrounding whitespace, or
        // non-breaking spaces a copy-paste manager might insert.
        $normalized = strtolower(trim(preg_replace('/\s+/u', '', $code)));
        $codes = array_map(fn ($c) => strtolower(trim((string) $c)), $codes);
        $idx = array_search($normalized, $codes, true);
        if ($idx === false) {
            return false;
        }
        unset($codes[$idx]);
        $user->two_factor_recovery_codes = Crypt::encryptString(json_encode(array_values($codes)));
        $user->save();

        return true;
    }

    public function enable(User $user, string $secret, array $recoveryCodes): void
    {
        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->two_factor_recovery_codes = Crypt::encryptString(json_encode($recoveryCodes));
        $user->two_factor_confirmed_at = now();
        $user->save();
    }

    public function disable(User $user): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
    }

    public function decryptedSecret(User $user): ?string
    {
        if (! $user->two_factor_secret) {
            return null;
        }
        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isEnabled(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null && $user->two_factor_secret !== null;
    }

    public function isEnforcedFor(User $user): bool
    {
        $raw = (string) setting('auth_force_2fa_roles', '');
        $roles = array_filter(array_map('trim', explode(',', $raw)));
        if (empty($roles)) {
            return false;
        }
        if (! method_exists($user, 'roles')) {
            return false;
        }

        return $user->roles()->whereIn('name', $roles)->exists();
    }

    protected function computeOtp(string $secret, int|float $counter): string
    {
        $binary = pack('N*', 0).pack('N*', (int) $counter);
        $hash = hash_hmac('sha1', $binary, $this->base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $out = '';
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $out .= $alphabet[bindec($chunk)];
        }

        return $out;
    }

    protected function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper(rtrim($b32, '='));
        $bits = '';
        foreach (str_split($b32) as $c) {
            $idx = strpos($alphabet, $c);
            if ($idx === false) {
                continue;
            }
            $bits .= str_pad(decbin($idx), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
