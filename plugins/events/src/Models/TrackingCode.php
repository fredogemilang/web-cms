<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingCode extends Model
{
    protected $fillable = ['event_id', 'tracking_code', 'source', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
