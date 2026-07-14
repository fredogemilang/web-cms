<?php

namespace App\Livewire\Admin\ApiTokens;

use App\Models\ApiToken;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';

    public string $allowedIps = '';

    public int $rateLimit = 60;

    public ?string $newPlaintextToken = null;

    public function create(): void
    {
        $this->checkPermission();
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'rateLimit' => ['required', 'integer', 'min:1', 'max:6000'],
        ]);

        $ips = array_filter(array_map('trim', explode(',', $this->allowedIps)));

        $result = ApiToken::generateFor(
            auth()->user(),
            $this->name,
            ['*'],
            $ips,
            $this->rateLimit,
        );

        $this->newPlaintextToken = $result['plaintext'];
        $this->reset(['name', 'allowedIps']);
        $this->rateLimit = 60;
        activity()->log('api-token.created', auth()->user(), "API token created: {$result['model']->name}");
    }

    public function revoke(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('api-tokens.revoke'), 403);
        $token = ApiToken::where('tokenable_type', auth()->user()->getMorphClass())
            ->where('tokenable_id', auth()->id())
            ->findOrFail($id);
        $token->delete();
        activity()->log('api-token.revoked', auth()->user(), "API token revoked: {$token->name}");
        session()->flash('success', 'Token revoked.');
    }

    protected function checkPermission(): void
    {
        abort_unless(auth()->user()?->hasPermission('api-tokens.create'), 403);
    }

    public function render()
    {
        $tokens = ApiToken::where('tokenable_type', auth()->user()->getMorphClass())
            ->where('tokenable_id', auth()->id())
            ->latest()->get();

        return view('livewire.admin.api-tokens.index', compact('tokens'));
    }
}
