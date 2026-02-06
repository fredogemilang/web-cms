<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class CustomTaxonomy extends Model
{
    protected $fillable = [
        'name',
        'singular_label',
        'plural_label',
        'slug',
        'is_hierarchical',
        'show_in_menu',
        'show_in_rest',
        'post_types',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_hierarchical' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_rest' => 'boolean',
        'post_types' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taxonomy) {
            if (empty($taxonomy->slug)) {
                $taxonomy->slug = Str::slug($taxonomy->name, '_');
            }
            if (empty($taxonomy->post_types)) {
                $taxonomy->post_types = [];
            }
        });
    }

    /**
     * Get meta fields for this taxonomy
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(MetaField::class, 'fieldable')->orderBy('order');
    }

    /**
     * Get CPTs that use this taxonomy
     */
    public function getCustomPostTypesAttribute()
    {
        return CustomPostType::where('is_active', true)
            ->whereIn('slug', $this->post_types ?? [])
            ->get();
    }

    /**
     * Get the terms table name for this taxonomy
     */
    public function getTermsTableName(): string
    {
        return 'taxonomy_' . $this->slug;
    }

    /**
     * Scope for active taxonomies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for taxonomies shown in menu
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope for taxonomies attached to a specific CPT
     */
    public function scopeForPostType($query, string $postTypeSlug)
    {
        return $query->whereJsonContains('post_types', $postTypeSlug);
    }

    /**
     * Check if this taxonomy is attached to a CPT
     */
    public function isAttachedTo(string $postTypeSlug): bool
    {
        return in_array($postTypeSlug, $this->post_types ?? []);
    }

    /**
     * Attach this taxonomy to a CPT
     */
    public function attachToPostType(string $postTypeSlug): void
    {
        $postTypes = $this->post_types ?? [];
        if (!in_array($postTypeSlug, $postTypes)) {
            $postTypes[] = $postTypeSlug;
            $this->update(['post_types' => $postTypes]);
        }
    }

    /**
     * Detach this taxonomy from a CPT
     */
    public function detachFromPostType(string $postTypeSlug): void
    {
        $postTypes = array_filter($this->post_types ?? [], fn($slug) => $slug !== $postTypeSlug);
        $this->update(['post_types' => array_values($postTypes)]);
    }
}
