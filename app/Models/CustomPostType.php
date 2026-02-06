<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class CustomPostType extends Model
{
    protected $fillable = [
        'name',
        'singular_label',
        'plural_label',
        'slug',
        'icon',
        'description',
        'is_hierarchical',
        'show_in_menu',
        'show_in_rest',
        'has_archive',
        'supports',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_hierarchical' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_rest' => 'boolean',
        'has_archive' => 'boolean',
        'supports' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Default supports values
     */
    public static array $defaultSupports = [
        'title',
        'editor',
        'thumbnail',
        'excerpt',
        'author',
    ];

    /**
     * Available support options
     */
    public static array $availableSupports = [
        'title' => 'Title',
        'editor' => 'Content Editor',
        'thumbnail' => 'Featured Image',
        'excerpt' => 'Excerpt',
        'author' => 'Author',
        'comments' => 'Comments',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cpt) {
            if (empty($cpt->slug)) {
                $cpt->slug = Str::slug($cpt->name, '_');
            }
            if (empty($cpt->supports)) {
                $cpt->supports = self::$defaultSupports;
            }
        });
    }

    /**
     * Get meta fields for this CPT
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(MetaField::class, 'fieldable')->orderBy('order');
    }

    /**
     * Get taxonomies attached to this CPT
     */
    public function taxonomies()
    {
        return CustomTaxonomy::where('is_active', true)
            ->whereJsonContains('post_types', $this->slug)
            ->get();
    }

    /**
     * Get the table name for this CPT's content
     */
    public function getContentTableName(): string
    {
        return 'cpt_' . $this->slug;
    }

    /**
     * Scope for active CPTs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for CPTs shown in menu
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Get the route name for this CPT
     */
    public function getRouteNameAttribute(): string
    {
        return 'admin.cpt.' . $this->slug;
    }

    /**
     * Get the admin URL for listing entries
     */
    public function getAdminUrlAttribute(): string
    {
        return route('admin.cpt.entries.index', $this->slug);
    }

    /**
     * Check if this CPT supports a feature
     */
    public function supports(string $feature): bool
    {
        return in_array($feature, $this->supports ?? []);
    }
}
