<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TaxonomyTerm extends Model
{
    protected $table = 'taxonomy_terms';

    protected $fillable = [
        'taxonomy_id',
        'name',
        'slug',
        'description',
        'parent_id',
        'order',
        'meta',
    ];

    protected $casts = [
        'order' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($term) {
            if (empty($term->slug)) {
                $term->slug = Str::slug($term->name);
            }
        });
    }

    /**
     * Get the taxonomy this term belongs to
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(CustomTaxonomy::class, 'taxonomy_id');
    }

    /**
     * Get the parent term
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class, 'parent_id');
    }

    /**
     * Get the children terms
     */
    public function children(): HasMany
    {
        return $this->hasMany(TaxonomyTerm::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all entries with this term
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(CptEntry::class, 'cpt_entry_term', 'term_id', 'entry_id');
    }

    /**
     * Scope for terms of a specific taxonomy
     */
    public function scopeOfTaxonomy($query, $taxonomyId)
    {
        return $query->where('taxonomy_id', $taxonomyId);
    }

    /**
     * Scope for root terms (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get count of entries with this term
     */
    public function getEntriesCountAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Get all descendants (recursive children)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (recursive parents)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors->reverse();
    }
}
