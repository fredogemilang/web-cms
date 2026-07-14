<?php

namespace App\Livewire\Admin\Profile;

use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class TwoFactorSettings extends Component
{
    public string $stage = 'idle';   // idle | setup | confirmed

    public string $secret = '';

    public string $confirmCode = '';

    public array $recoveryCodes = [];

    public string $currentPassword = '';

    public function mount(): void
    {
        $tfa = app(TwoFactorService::class);
        $user = auth()->user();
        $this->stage = $tfa->isEnabled($user) ? 'confirmed' : 'idle';
    }

    public function beginSetup(TwoFactorService $tfa): void
    {
        $this->secret = $tfa->generateSecret();
        $this->recoveryCodes = $tfa->generateRecoveryCodes();
        $this->stage = 'setup';
    }

    public function confirm(TwoFactorService $tfa): void
    {
        $this->validate([
            'confirmCode' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        if (! $tfa->verify($this->secret, $this->confirmCode)) {
            $this->addError('confirmCode', 'Kode tidak cocok. Coba lagi.');

            return;
        }

        $tfa->enable(auth()->user(), $this->secret, $this->recoveryCodes);
        $this->stage = 'confirmed';
        activity()->log('auth.2fa.enabled', auth()->user(), 'Two-factor enabled');
        session()->flash('success', '2FA aktif. Simpan recovery codes Anda di tempat aman.');
    }

    public function disable(TwoFactorService $tfa): void
    {
        $this->validate(['currentPassword' => ['required']]);

        if (! Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'Password salah.');

            return;
        }

        $tfa->disable(auth()->user());
        $this->stage = 'idle';
        $this->reset(['secret', 'confirmCode', 'recoveryCodes', 'currentPassword']);
        activity()->log('auth.2fa.disabled', auth()->user(), 'Two-factor disabled');
        session()->flash('success', '2FA dinonaktifkan.');
    }

    public function getOtpauthUriProperty(): string
    {
        if (! $this->secret) {
            return '';
        }

        return app(TwoFactorService::class)->otpauthUri(auth()->user(), $this->secret);
    }

    public function render()
    {
        return view('livewire.admin.profile.two-factor-settings');
    }
}
