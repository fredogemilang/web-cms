<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected TwoFactorService $tfa) {}

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            // Optional 2FA code submitted together with credentials. Either
            // a 6-digit TOTP or a recovery code (lowercase alnum + dashes).
            'two_factor_code' => ['nullable', 'string', 'max:64'],
        ]);

        $key = 'api-login|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Wait '.RateLimiter::availableIn($key).'s.',
            ]);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 15 * 60);
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        if (! ($user->is_active ?? true)) {
            RateLimiter::hit($key, 15 * 60);
            throw ValidationException::withMessages(['email' => 'Account disabled.']);
        }

        // 2FA gate: honor the same policy as the web login. If the user has 2FA
        // enabled OR their role enforces it, require a valid TOTP / recovery
        // code in the same request — never issue a token from password alone.
        $hasTwoFactor = $this->tfa->isEnabled($user);
        $enforced = ! $hasTwoFactor && $this->tfa->isEnforcedFor($user);

        if ($enforced) {
            // Role-enforced but user hasn't enrolled yet — must complete setup
            // through the web UI; do not let them grab an API token.
            throw ValidationException::withMessages([
                'email' => 'Two-factor authentication is required for this account. Enable it in the web admin before requesting an API token.',
            ]);
        }

        if ($hasTwoFactor) {
            $code = trim((string) ($data['two_factor_code'] ?? ''));
            if ($code === '') {
                throw ValidationException::withMessages([
                    'two_factor_code' => 'Two-factor code required.',
                ]);
            }

            $secret = $this->tfa->decryptedSecret($user);
            $ok = ($secret && $this->tfa->verify($secret, $code))
                || $this->tfa->consumeRecoveryCode($user, $code);

            if (! $ok) {
                RateLimiter::hit($key, 15 * 60);
                throw ValidationException::withMessages([
                    'two_factor_code' => 'Invalid two-factor code.',
                ]);
            }
        }

        RateLimiter::clear($key);

        $result = ApiToken::generateFor(
            $user,
            $data['device_name'] ?? 'API client',
            ['*'],
            [],
            (int) setting('api_default_rate_limit', 60),
        );

        return response()->json([
            'token' => $result['plaintext'],
            'expires_at' => $result['model']->expires_at?->toAtomString(),
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->attributes->get('api_token');
        if ($token instanceof ApiToken) {
            $token->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => method_exists($user, 'roles') ? $user->roles->pluck('slug') : [],
        ]);
    }
}
