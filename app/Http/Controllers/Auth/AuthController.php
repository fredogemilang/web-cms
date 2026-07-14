<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginThrottle;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected LoginThrottle $throttle,
        protected TwoFactorService $tfa,
    ) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($this->throttle->tooManyAttempts($request)) {
            $wait = $this->throttle->availableIn($request);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$wait} detik.",
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'email' => 'Akun terkunci sementara karena terlalu banyak login gagal. Coba lagi nanti.',
            ]);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->throttle->hit($request);
            if ($user) {
                $user->failed_login_attempts = ($user->failed_login_attempts ?? 0) + 1;
                $hardLockAfter = (int) setting('auth_login_lockout_after', 10);
                if ($user->failed_login_attempts >= $hardLockAfter) {
                    $user->locked_until = now()->addMinutes((int) setting('auth_login_lockout_minutes', 30));
                    activity()->log('auth.lockout', $user, "Account locked: {$user->email}");
                }
                $user->save();
            }
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! ($user->is_active ?? true)) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan.',
            ]);
        }

        $remember = $request->boolean('remember');

        // 2FA enforcement: if user has 2FA enabled OR their role forces it,
        // route through the challenge step instead of completing login here.
        $needsTwoFactor = $this->tfa->isEnabled($user);
        $enforceSetup = ! $needsTwoFactor && $this->tfa->isEnforcedFor($user);

        if ($needsTwoFactor) {
            $this->throttle->clear($request);
            $request->session()->put('2fa.user_id', $user->id);
            $request->session()->put('2fa.remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        $this->throttle->clear($request);
        Auth::login($user, $remember);
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();
        $request->session()->regenerate();

        if ($enforceSetup) {
            return redirect()->route('admin.profile.index')
                ->with('warning', 'Anda diwajibkan mengaktifkan Two-Factor Authentication.');
        }

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Selamat datang, '.$user->name.'!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
