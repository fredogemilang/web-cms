<?php

namespace Plugins\Events\Models;

use App\Models\Media;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speaker extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'title',
        'company',
        'bio',
        'email',
        'phone',
        'photo_id',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'instagram_url',
        'website',
        'order',
        'is_active',
        'translations',
    ];

    /**
     * Per-locale fields. Personal name is a proper noun (kept shared), but
     * job title, company, and bio benefit from translation.
     */
    protected array $translatable = ['title', 'company', 'bio'];

    protected $casts = [
        'translations' => 'array',
    ];

    /**
     * Get the speaker's photo.
     */
    public function photo()
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    /**
     * The events that belong to the speaker.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_speaker')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
