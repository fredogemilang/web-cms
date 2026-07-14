<?php

namespace App\Models;

use App\Services\ThemeLoader;
use App\Traits\HasSeoMeta;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasSeoMeta, HasTranslations, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'parent_id',
        'menu_order',
        'status',
        'published_at',
        'author_id',
        'template',
        'featured_image',
        'seo',
        'settings',
        'translations',
    ];

    /** Fields that can carry per-locale values via the translations JSON column. */
    protected array $translatable = ['title', 'slug', 'seo'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'seo' => 'array',
            'settings' => 'array',
            'translations' => 'array',
            'menu_order' => 'integer',
        ];
    }

    // Available templates (static fallback when theme defines no page_templates)
    public static array $templates = [
        'default' => 'Default',
        'full-width' => 'Full Width',
        'landing' => 'Landing Page',
        'sidebar-left' => 'Sidebar Left',
        'sidebar-right' => 'Sidebar Right',
    ];

    /**
     * Get available page templates from the active theme, fallback to legacy list.
     */
    public static function getTemplates(): array
    {
        $theme = app(ThemeLoader::class)->getActiveTheme();
        if ($theme) {
            $templates = $theme->getPageTemplates();
            if (! empty($templates)) {
                return collect($templates)
                    ->mapWithKeys(fn ($t, $key) => [$key => $t['label'] ?? ucfirst($key)])
                    ->toArray();
            }
        }

        return static::$templates;
    }

    // === RELATIONSHIPS ===

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('menu_order');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->whereNull('parent_block_id')->orderBy('order');
    }

    public function allBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('order');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderBy('created_at', 'desc');
    }

    // === SCOPES ===

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePrivate($query)
    {
        return $query->where('status', 'private');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // === HELPERS ===

    public function getBlock(string $name): ?PageBlock
    {
        return $this->allBlocks()->where('name', $name)->first();
    }

    public function getBlockValue(string $name, $default = null)
    {
        $block = $this->getBlock($name);

        return $block ? $block->value : $default;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            (! $this->published_at || $this->published_at->isPast());
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isPrivate(): bool
    {
        return $this->status === 'private';
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function descendants(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    public function getFullPath(): string
    {
        $path = $this->slug;
        $ancestors = $this->ancestors();

        foreach ($ancestors->reverse() as $ancestor) {
            $path = $ancestor->slug.'/'.$path;
        }

        return $path;
    }

    public function getUrl(): string
    {
        return url($this->getFullPath());
    }

    /**
     * Resolve a Page by slug, scanning the default `slug` column first and then
     * each locale's translated slug. When the match is on a non-default locale,
     * also sets app()->setLocale() so the request renders in that language.
     */
    public static function findByLocalizedSlug(string $slug): ?self
    {
        // Default column match (default locale)
        $page = static::published()->where('slug', $slug)->first();
        if ($page) {
            return $page;
        }

        // Scan translated slugs across all configured locales
        $defaultLocale = static::defaultLocale();
        $locales = array_filter(available_locales(), fn ($l) => $l !== $defaultLocale);

        foreach ($locales as $locale) {
            // JSON_EXTRACT path: $.{locale}.slug
            $page = static::published()
                ->whereRaw('JSON_EXTRACT(translations, ?) = ?', ["$.\"{$locale}\".slug", $slug])
                ->first();
            if ($page) {
                app()->setLocale($locale);

                return $page;
            }
        }

        return null;
    }

    /**
     * Return a URL for this page in the given locale (uses that locale's slug if defined).
     */
    public function localizedUrl(string $locale): string
    {
        $slug = $this->getTranslation('slug', $locale);

        return url('/'.ltrim($slug, '/'));
    }

    public function getMetaTitle(): string
    {
        return $this->seo['meta_title'] ?? $this->title;
    }

    public function getMetaDescription(): ?string
    {
        return $this->seo['meta_description'] ?? null;
    }

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }

            // Ensure unique slug
            $originalSlug = $page->slug;
            $counter = 1;
            while (static::where('slug', $page->slug)->exists()) {
                $page->slug = $originalSlug.'-'.$counter;
                $counter++;
            }
        });

        static::updating(function ($page) {
            // Ensure unique slug on update
            if ($page->isDirty('slug')) {
                $originalSlug = $page->slug;
                $counter = 1;
                while (static::where('slug', $page->slug)->where('id', '!=', $page->id)->exists()) {
                    $page->slug = $originalSlug.'-'.$counter;
                    $counter++;
                }
            }
        });
    }
}
