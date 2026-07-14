<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginThrottle
{
    public function key(Request $request): string
    {
        return Str::lower((string) $request->input('email')).'|'.$request->ip();
    }

    public function maxAttempts(): int
    {
        return (int) setting('auth_login_max_attempts', 5);
    }

    public function decayMinutes(): int
    {
        return (int) setting('auth_login_decay_minutes', 15);
    }

    public function tooManyAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts($this->key($request), $this->maxAttempts());
    }

    public function hit(Request $request): int
    {
        return RateLimiter::hit($this->key($request), $this->decayMinutes() * 60);
    }

    public function clear(Request $request): void
    {
        RateLimiter::clear($this->key($request));
    }

    public function availableIn(Request $request): int
    {
        return RateLimiter::availableIn($this->key($request));
    }
}
