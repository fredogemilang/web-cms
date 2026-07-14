<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventCustomQuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(EventCustomQuestion::class, 'question_id');
    }
}
