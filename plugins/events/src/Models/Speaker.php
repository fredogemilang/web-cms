<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Media;

class Speaker extends Model
{
    use SoftDeletes;

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
