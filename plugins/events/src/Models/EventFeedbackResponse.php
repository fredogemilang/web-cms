<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFeedbackResponse extends Model
{
    protected $table = 'event_feedback_responses';

    protected $fillable = [
        'event_id',
        'event_registration_id',
        'question_id',
        'answer',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventFeedbackQuestion::class, 'question_id');
    }
}
