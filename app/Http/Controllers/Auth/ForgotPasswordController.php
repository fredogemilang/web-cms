<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        abort_unless(setting('auth_password_reset_enabled', true), 404);

        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        abort_unless(setting('auth_password_reset_enabled', true), 404);

        $request->validate(['email' => ['required', 'email']]);

        $key = 'pw-reset|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $wait = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$wait} detik.",
            ]);
        }
        RateLimiter::hit($key, 15 * 60);

        $user = User::where('email', $request->input('email'))->first();

        // Don't leak account existence — always look like success.
        if ($user && $user->is_active !== false) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
            $user->notify(new PasswordResetNotification($token));
            activity()->log('auth.password_reset.requested', $user, "Password reset requested for {$user->email}");
        }

        return back()->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
    }
}
