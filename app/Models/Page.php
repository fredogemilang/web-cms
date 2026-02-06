<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

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
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'seo' => 'array',
            'settings' => 'array',
            'menu_order' => 'integer',
        ];
    }

    // Available templates
    public static array $templates = [
        'default' => 'Default',
        'full-width' => 'Full Width',
        'landing' => 'Landing Page',
        'sidebar-left' => 'Sidebar Left',
        'sidebar-right' => 'Sidebar Right',
    ];

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
            (!$this->published_at || $this->published_at->isPast());
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
            $path = $ancestor->slug . '/' . $path;
        }

        return $path;
    }

    public function getUrl(): string
    {
        return url($this->getFullPath());
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
                $page->slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        });

        static::updating(function ($page) {
            // Ensure unique slug on update
            if ($page->isDirty('slug')) {
                $originalSlug = $page->slug;
                $counter = 1;
                while (static::where('slug', $page->slug)->where('id', '!=', $page->id)->exists()) {
                    $page->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }
}
