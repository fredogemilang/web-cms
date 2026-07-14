<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function showForm(string $token, Request $request)
    {
        abort_unless(setting('auth_password_reset_enabled', true), 404);

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        abort_unless(setting('auth_password_reset_enabled', true), 404);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (! $row || ! Hash::check($data['token'], $row->token)) {
            throw ValidationException::withMessages(['email' => 'Token reset tidak valid.']);
        }

        $expires = (int) setting('auth_password_reset_expire_minutes', 60);
        // created_at is a raw DB string — parse to Carbon then compare against
        // wall-clock now(). diffInMinutes is signed under Carbon 3, so use a
        // monotonic isPast() on the deadline timestamp instead.
        $createdAt = Carbon::parse($row->created_at);
        if ($createdAt->copy()->addMinutes($expires)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw ValidationException::withMessages(['email' => 'Link reset sudah kadaluarsa.']);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->password = $data['password'];
        $user->password_changed_at = now();
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->setRememberToken(Str::random(60));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        activity()->log('auth.password_reset.completed', $user, "Password reset completed for {$user->email}");

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
