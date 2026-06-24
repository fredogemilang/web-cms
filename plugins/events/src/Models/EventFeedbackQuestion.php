<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventFeedbackQuestion extends Model
{
    protected $table = 'event_feedback_questions';

    protected $fillable = [
        'event_id',
        'step',
        'sort_order',
        'question',
        'short_label',
        'type',
        'is_required',
        'rating_min_value',
        'rating_max_value',
        'is_conditional',
        'parent_question_id',
        'condition_operator',
        'condition_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_conditional' => 'boolean',
        'step' => 'integer',
        'sort_order' => 'integer',
        'rating_min_value' => 'integer',
        'rating_max_value' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(EventFeedbackOption::class, 'question_id')->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EventFeedbackResponse::class, 'question_id');
    }

    public function parentQuestion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_question_id');
    }

    public function childQuestions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_question_id');
    }

    /**
     * Scope: ordered by step then sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('step')->orderBy('sort_order');
    }

    /**
     * Scope: filter by step.
     */
    public function scopeForStep($query, int $step)
    {
        return $query->where('step', $step);
    }
}
