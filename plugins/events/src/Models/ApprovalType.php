<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalType extends Model
{
    protected $table = 'approval_types';

    protected $fillable = [
        'event_id',
        'cat',
        'type_name',
        'email_subject',
        'email_banner',
        'email_body',
    ];

    /**
     * Get the event this approval type belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Scope: approved category only.
     */
    public function scopeApproved($query)
    {
        return $query->where('cat', 'approved');
    }

    /**
     * Scope: rejected category only.
     */
    public function scopeRejected($query)
    {
        return $query->where('cat', 'rejected');
    }

    /**
     * Scope: by event.
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}