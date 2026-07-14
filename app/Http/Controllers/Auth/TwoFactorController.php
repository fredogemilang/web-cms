<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $tfa) {}

    public function showChallenge(Request $request)
    {
        if (! $request->session()->has('2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $userId = $request->session()->get('2fa.user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $key = '2fa|'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Coba lagi nanti.',
            ]);
        }

        $code = (string) $request->input('code');
        $secret = $this->tfa->decryptedSecret($user);

        $ok = false;
        if ($secret && $this->tfa->verify($secret, $code)) {
            $ok = true;
        } elseif ($this->tfa->consumeRecoveryCode($user, $code)) {
            $ok = true;
            activity()->log('auth.2fa.recovery_used', $user, "Recovery code used by {$user->email}");
        }

        if (! $ok) {
            RateLimiter::hit($key, 15 * 60);
            throw ValidationException::withMessages(['code' => 'Kode 2FA tidak valid.']);
        }

        RateLimiter::clear($key);
        $request->session()->forget('2fa.user_id');
        $remember = (bool) $request->session()->pull('2fa.remember', false);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
