<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module',
        'resource',
        'action',
        'description',
        'source',
        'plugin_slug',
        'is_active',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the plugin that owns this permission.
     */
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'plugin_slug', 'slug');
    }

    /**
     * Scope to only include active permissions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by source.
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to filter by plugin slug.
     */
    public function scopeByPlugin(Builder $query, string $pluginSlug): Builder
    {
        return $query->where('plugin_slug', $pluginSlug);
    }

    /**
     * Scope to only core permissions.
     */
    public function scopeCore(Builder $query): Builder
    {
        return $query->where('source', 'core');
    }

    /**
     * Scope to only plugin permissions.
     */
    public function scopeFromPlugins(Builder $query): Builder
    {
        return $query->where('source', 'like', 'plugin:%');
    }

    /**
     * Scope to filter by module.
     */
    public function scopeByModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Get all unique modules.
     */
    public static function getModules(): array
    {
        return static::active()->distinct()->pluck('module')->toArray();
    }

    /**
     * Get modules grouped by source.
     */
    public static function getModulesGroupedBySource(): array
    {
        // Get all active permissions and process in PHP to avoid MySQL DISTINCT + ORDER BY issue
        $permissions = static::active()
            ->select('module', 'source', 'sort_order')
            ->get()
            ->sortBy('sort_order')
            ->unique(fn($p) => $p->module . '|' . $p->source);

        return [
            'core' => $permissions->where('source', 'core')->pluck('module')->unique()->values()->toArray(),
            'plugins' => $permissions->filter(fn($p) => str_starts_with($p->source, 'plugin:'))
                ->groupBy(fn($p) => str_replace('plugin:', '', $p->source))
                ->map(fn($group) => $group->pluck('module')->unique()->values()->toArray())
                ->toArray(),
        ];
    }

    /**
     * Check if this is a core permission.
     */
    public function isCore(): bool
    {
        return $this->source === 'core';
    }

    /**
     * Check if this is a plugin permission.
     */
    public function isFromPlugin(): bool
    {
        return str_starts_with($this->source, 'plugin:');
    }

    /**
     * Get the plugin slug from source.
     */
    public function getPluginSlugFromSource(): ?string
    {
        if (!$this->isFromPlugin()) {
            return null;
        }
        return str_replace('plugin:', '', $this->source);
    }
}
