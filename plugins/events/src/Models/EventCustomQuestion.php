<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCustomQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'type',
        'question',
        'question_description',
        'short_label',
        'required',
        'order',
        'image',
    ];

    protected $casts = [
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function options()
    {
        return $this->hasMany(EventCustomQuestionOption::class, 'question_id')->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(EventCustomAnswer::class, 'question_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public static function getNextOrder(int $eventId): int
    {
        return (self::where('event_id', $eventId)->max('order') ?? -1) + 1;
    }
}
