<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'screenshot',
        'is_active',
        'supports',
        'installed_at',
        'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supports' => 'array',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active themes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive themes.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the path to the theme directory.
     */
    public function getPathAttribute(): string
    {
        return base_path("themes/{$this->slug}");
    }

    /**
     * Get the URL to the theme screenshot.
     */
    public function getScreenshotUrlAttribute(): ?string
    {
        if (!$this->screenshot) {
            return null;
        }

        return asset("themes/{$this->slug}/{$this->screenshot}");
    }

    /**
     * Check if theme supports a specific feature.
     */
    public function supports(string $feature): bool
    {
        return in_array($feature, $this->supports ?? []);
    }

    /**
     * Check if the theme directory exists.
     */
    public function exists(): bool
    {
        return is_dir($this->path);
    }
}
