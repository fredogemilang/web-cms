<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Redirect extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'from_path', 'to_url', 'status_code',
        'is_regex', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_regex' => 'boolean',
        'is_active' => 'boolean',
        'hit_count' => 'integer',
        'status_code' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    protected const CACHE_KEY = 'redirects:active';

    protected const CACHE_TTL = 3600;

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
        static::restored(fn () => static::forgetCache());
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Find the first active redirect that matches the given request path.
     * Path is the URL-decoded path *without* query string, normalized to a leading slash.
     */
    public static function matchRequestPath(string $path): ?self
    {
        $path = '/'.ltrim($path, '/');
        $rules = static::loadActive();

        // Exact (non-regex) first — cheaper and more predictable
        $exact = $rules->first(fn ($r) => ! $r->is_regex && $r->from_path === $path);
        if ($exact) {
            return $exact;
        }

        // Regex rules — first match wins
        foreach ($rules as $rule) {
            if (! $rule->is_regex) {
                continue;
            }
            $pattern = static::delimitPattern($rule->from_path);
            if (@preg_match($pattern, $path) === 1) {
                return $rule;
            }
        }

        return null;
    }

    public function resolveTarget(string $requestPath): string
    {
        $to = $this->to_url;

        // Apply regex substitution if pattern uses capture groups
        if ($this->is_regex && str_contains($to, '$')) {
            $to = @preg_replace(static::delimitPattern($this->from_path), $to, '/'.ltrim($requestPath, '/'))
                ?? $this->to_url;
        }

        return $to;
    }

    public function recordHit(): void
    {
        // Avoid touching updated_at so the rule mtime stays meaningful
        DB::table($this->getTable())
            ->where('id', $this->id)
            ->update([
                'hit_count' => DB::raw('hit_count + 1'),
                'last_hit_at' => now(),
            ]);
    }

    protected static function loadActive(): Collection
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            // Tolerate missing table (fresh install before migrate) — no rules in that case.
            try {
                return static::query()
                    ->where('is_active', true)
                    ->orderByDesc('id') // newer rules win on overlap
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }

    protected static function delimitPattern(string $pattern): string
    {
        // Already delimited by user
        if (preg_match('/^([\/#~|!@%&]).+\1[imsxuADSUXJ]*$/', $pattern)) {
            return $pattern;
        }

        return '#'.str_replace('#', '\\#', $pattern).'#';
    }
}
