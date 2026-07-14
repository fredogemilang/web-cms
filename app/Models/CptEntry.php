<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CptEntry extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'cpt_entries';

    protected $fillable = [
        'post_type_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'author_id',
        'parent_id',
        'status',
        'published_at',
        'meta',
        'translations',
        'menu_order',
    ];

    /** Fields that can carry per-locale values via the translations JSON column. */
    protected array $translatable = ['title', 'slug', 'content', 'excerpt'];

    /**
     * Resolve a CptEntry by slug within a given post type, scanning the default
     * `slug` column first then each locale's translated slug. On a non-default
     * locale match, sets app()->setLocale() so the request renders accordingly.
     */
    public static function findByLocalizedSlug(CustomPostType|string $postType, string $slug): ?self
    {
        $postTypeId = $postType instanceof CustomPostType
            ? $postType->id
            : CustomPostType::where('slug', $postType)->value('id');

        if (! $postTypeId) {
            return null;
        }

        $base = static::query()
            ->where('post_type_id', $postTypeId)
            ->where('status', 'published');

        $entry = (clone $base)->where('slug', $slug)->first();
        if ($entry) {
            return $entry;
        }

        $defaultLocale = static::defaultLocale();
        $locales = array_filter(available_locales(), fn ($l) => $l !== $defaultLocale);

        foreach ($locales as $locale) {
            $entry = (clone $base)
                ->whereRaw('JSON_EXTRACT(translations, ?) = ?', ["$.\"{$locale}\".slug", $slug])
                ->first();
            if ($entry) {
                app()->setLocale($locale);

                return $entry;
            }
        }

        return null;
    }

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'translations' => 'array',
        'menu_order' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->slug)) {
                $entry->slug = Str::slug($entry->title);
            }
            if (empty($entry->author_id)) {
                $entry->author_id = auth()->id();
            }
        });
    }

    /**
     * Get the post type this entry belongs to
     */
    public function postType(): BelongsTo
    {
        return $this->belongsTo(CustomPostType::class, 'post_type_id');
    }

    /**
     * Get the author of this entry
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent entry (for hierarchical CPTs)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CptEntry::class, 'parent_id');
    }

    /**
     * Get the children entries (for hierarchical CPTs)
     */
    public function children(): HasMany
    {
        return $this->hasMany(CptEntry::class, 'parent_id')->orderBy('menu_order');
    }

    /**
     * Get the taxonomy terms attached to this entry
     */
    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(TaxonomyTerm::class, 'cpt_entry_term', 'entry_id', 'term_id');
    }

    /**
     * Get terms for a specific taxonomy
     */
    public function termsForTaxonomy(int $taxonomyId)
    {
        return $this->terms()->where('taxonomy_id', $taxonomyId)->get();
    }

    /**
     * Scope for published entries
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for entries of a specific post type
     */
    public function scopeOfType($query, $postTypeId)
    {
        return $query->where('post_type_id', $postTypeId);
    }

    /**
     * Scope for entries by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get a meta value
     */
    public function getMeta(string $key, $default = null)
    {
        return $this->meta[$key] ?? $default;
    }

    /**
     * Set a meta value
     */
    public function setMeta(string $key, $value): void
    {
        $meta = $this->meta ?? [];
        $meta[$key] = $value;
        $this->meta = $meta;
    }

    /**
     * Get status badge info for display
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'published' => ['color' => 'green', 'label' => 'Published'],
            'draft' => ['color' => 'gray', 'label' => 'Draft'],
            'scheduled' => ['color' => 'blue', 'label' => 'Scheduled'],
            'archived' => ['color' => 'amber', 'label' => 'Archived'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
        };
    }
}
