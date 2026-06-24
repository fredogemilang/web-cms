<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFeedbackOption extends Model
{
    protected $table = 'event_feedback_options';

    protected $fillable = [
        'question_id',
        'option_label',
        'is_leads_flag',
        'sort_order',
    ];

    protected $casts = [
        'is_leads_flag' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(EventFeedbackQuestion::class, 'question_id');
    }
}
