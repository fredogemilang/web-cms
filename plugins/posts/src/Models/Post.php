<?php

namespace Plugins\Posts\Models;

use App\Models\User;
use App\Traits\FindsByLocalizedSlug;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use FindsByLocalizedSlug, HasTranslations, SoftDeletes;

    protected static function baseLocalizedSlugQuery(): Builder
    {
        return static::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'status',
        'visibility',
        'password',
        'published_at',
        'is_featured',
        'views_count',
        'meta',
        'translations',
    ];

    /** Per-locale fields stored in the translations JSON column. */
    protected array $translatable = ['title', 'slug', 'excerpt', 'content'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'meta' => 'array',
        'translations' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if (empty($post->author_id)) {
                $post->author_id = auth()->id();
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

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
