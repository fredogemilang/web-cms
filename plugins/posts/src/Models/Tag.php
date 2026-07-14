<?php

namespace Plugins\Posts\Models;

use App\Traits\FindsByLocalizedSlug;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use FindsByLocalizedSlug, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'translations',
    ];

    protected array $translatable = ['name', 'slug'];

    protected $casts = [
        'translations' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }
}
