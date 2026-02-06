<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'provider',
        'is_active',
        'permissions_registered',
        'permission_count',
        'installed_at',
        'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions_registered' => 'boolean',
        'permission_count' => 'integer',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active plugins.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive plugins.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }
}
