<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'tokenable_type', 'tokenable_id', 'name', 'token_hash', 'prefix',
        'abilities', 'allowed_ips', 'rate_limit_per_minute', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate a fresh token for a model. Returns the plaintext token —
     * persisted only as hash, so this is the one and only chance to reveal it.
     *
     * @return array{model: self, plaintext: string}
     */
    public static function generateFor(
        Model $owner,
        string $name,
        array $abilities = ['*'],
        array $allowedIps = [],
        ?int $rateLimit = null,
        ?\DateTimeInterface $expiresAt = null,
    ): array {
        $plaintext = Str::random(48);
        $prefix = substr($plaintext, 0, 6);
        $token = static::create([
            'tokenable_type' => $owner->getMorphClass(),
            'tokenable_id' => $owner->getKey(),
            'name' => $name,
            'token_hash' => hash('sha256', $plaintext),
            'prefix' => $prefix,
            'abilities' => $abilities ?: ['*'],
            'allowed_ips' => $allowedIps ?: null,
            'rate_limit_per_minute' => $rateLimit ?? 60,
            'expires_at' => $expiresAt,
        ]);

        return ['model' => $token, 'plaintext' => "{$prefix}.{$plaintext}"];
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
