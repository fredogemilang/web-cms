<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'icon',
        'route',
        'permission',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Scope a query to only include active menu items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order menu items.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Check if the menu item is accessible by the current user.
     */
    public function isAccessible(?User $user = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (empty($this->permission)) {
            return true;
        }

        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        return $user->hasPermission($this->permission);
    }

    /**
     * Get all accessible menu items for a user.
     */
    public static function getAccessibleMenus(?User $user = null): \Illuminate\Database\Eloquent\Collection
    {
        $user = $user ?? auth()->user();

        return static::active()
            ->ordered()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->get()
            ->filter(function ($menu) use ($user) {
                // Filter parent menus
                if (!$menu->isAccessible($user)) {
                    return false;
                }

                // Filter children
                if ($menu->children->isNotEmpty()) {
                    $menu->setRelation('children', $menu->children->filter(function ($child) use ($user) {
                        return $child->isAccessible($user);
                    }));
                }

                return true;
            });
    }
}
