<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'image_id',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the category image.
     */
    public function image()
    {
        return $this->belongsTo(\App\Models\Media::class, 'image_id');
    }

    /**
     * Get events in this category.
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'category_id');
    }

    /**
     * Get active events count.
     */
    public function getActiveEventsCountAttribute()
    {
        return $this->events()->where('status', 'published')->count();
    }
}
