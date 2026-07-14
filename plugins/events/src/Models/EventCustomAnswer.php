<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventCustomAnswer extends Model
{
    protected $fillable = [
        'event_registration_id',
        'question_id',
        'answer',
    ];

    protected $casts = [
        'answer' => 'array',
    ];

    public function registration()
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function question()
    {
        return $this->belongsTo(EventCustomQuestion::class, 'question_id');
    }
}
