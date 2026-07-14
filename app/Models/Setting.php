<?php

namespace App\Models;

use App\Services\SettingsRegistry;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'group', 'type', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    protected static array $memo = [];

    /** Cache of encrypted-key lookup, populated lazily from the SettingsRegistry. */
    protected static ?array $encryptedKeys = null;

    protected static function booted(): void
    {
        static::saved(function (self $s) {
            static::forgetCache($s->key, $s->group);

            // Audit log: record setting changes when an authenticated user is acting
            // (skip system/cli bootstraps where no user context exists)
            if (auth()->check()) {
                $changes = $s->getChanges();
                if (! empty($changes['value'])) {
                    $isEncrypted = static::isEncryptedKey($s->key);
                    $oldRaw = static::castFromStorage($s->getOriginal('value'), $s->type);
                    $newRaw = static::castFromStorage($s->value, $s->type);

                    // For encrypted keys, never expose the value to the audit log.
                    activity()->log(
                        'setting.updated',
                        null,
                        "Setting '{$s->key}' updated",
                        [
                            'key' => $s->key,
                            'group' => $s->group,
                            'old' => $isEncrypted ? '••• encrypted •••' : static::decryptIfNeeded($s->key, $oldRaw),
                            'new' => $isEncrypted ? '••• encrypted •••' : static::decryptIfNeeded($s->key, $newRaw),
                        ],
                    );
                }
            }
        });
        static::deleted(fn (self $s) => static::forgetCache($s->key, $s->group));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$memo)) {
            return static::$memo[$key];
        }

        $value = Cache::rememberForever(static::cacheKey($key), function () use ($key, $default) {
            // Tolerate missing table (fresh install before migrate) — return default.
            try {
                $row = static::query()->where('key', $key)->first();
            } catch (\Throwable $e) {
                return ['__missing' => true, 'default' => $default];
            }
            if (! $row) {
                return ['__missing' => true, 'default' => $default];
            }
            $cast = static::castFromStorage($row->value, $row->type);

            // Decrypt at the cache boundary so subsequent reads stay fast.
            return ['value' => static::decryptIfNeeded($key, $cast)];
        });

        $resolved = ($value['__missing'] ?? false) ? $default : $value['value'];
        static::$memo[$key] = $resolved;

        return $resolved;
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null): self
    {
        $type ??= static::guessType($value);
        $group ??= 'general';

        // Encrypt at rest for sensitive keys. Non-string values get cast to string
        // first since Crypt operates on strings. Empty/null values are left alone.
        $storedValue = $value;
        if (static::isEncryptedKey($key) && $value !== null && $value !== '') {
            $storedValue = Crypt::encryptString((string) $value);
        }

        $row = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'type' => $type,
                'value' => ['v' => $storedValue],
            ]
        );

        unset(static::$memo[$key]);

        return $row;
    }

    public static function forget(string $key): bool
    {
        unset(static::$memo[$key]);

        return (bool) static::query()->where('key', $key)->delete();
    }

    public static function forGroup(?string $group = null): array
    {
        $q = static::query();
        if ($group) {
            $q->where('group', $group);
        }

        return $q->get()->mapWithKeys(fn ($r) => [
            $r->key => static::decryptIfNeeded($r->key, static::castFromStorage($r->value, $r->type)),
        ])->toArray();
    }

    /** Decrypt a value if the key is marked encrypted; tolerate legacy plaintext data. */
    protected static function decryptIfNeeded(string $key, mixed $value): mixed
    {
        if (! static::isEncryptedKey($key) || $value === null || $value === '') {
            return $value;
        }
        if (! is_string($value)) {
            // We never encrypt non-string values, so non-string here means
            // legacy unencrypted data — return as-is.
            return $value;
        }
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Legacy plaintext value (before encryption was introduced) — return raw.
            return $value;
        }
    }

    /** Lazily computed set of setting keys whose values must be encrypted at rest. */
    protected static function isEncryptedKey(string $key): bool
    {
        if (static::$encryptedKeys === null) {
            static::$encryptedKeys = [];
            try {
                $registry = app(SettingsRegistry::class);
                foreach ($registry->groups() as $group) {
                    foreach (($group['fields'] ?? []) as $field) {
                        $isSensitive = ! empty($field['encrypted'])
                            || ($field['type'] ?? '') === 'password';
                        if ($isSensitive && ! empty($field['key'])) {
                            static::$encryptedKeys[$field['key']] = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Container/registry not ready (early boot) — fall back to empty set.
            }
        }

        return isset(static::$encryptedKeys[$key]);
    }

    /** For tests or after registry mutation, force reload of encrypted-key cache. */
    public static function resetEncryptedKeyCache(): void
    {
        static::$encryptedKeys = null;
    }

    public static function setMany(array $values, string $group, array $types = []): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value, $group, $types[$key] ?? null);
        }
    }

    protected static function castFromStorage(mixed $stored, string $type): mixed
    {
        $raw = is_array($stored) && array_key_exists('v', $stored) ? $stored['v'] : $stored;

        return match ($type) {
            'boolean' => (bool) $raw,
            'integer' => $raw === null ? null : (int) $raw,
            'float' => $raw === null ? null : (float) $raw,
            'array' => is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []),
            default => $raw,
        };
    }

    protected static function guessType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    protected static function cacheKey(string $key): string
    {
        return "setting:{$key}";
    }

    protected static function forgetCache(string $key, ?string $group = null): void
    {
        Cache::forget(static::cacheKey($key));
        unset(static::$memo[$key]);
    }
}
