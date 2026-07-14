<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoorprizeSession extends Model
{
    protected $table = 'event_doorprize_sessions';

    protected $fillable = [
        'event_id',
        'name',
        'require_checkin',
        'require_feedback',
        'order',
    ];

    protected $casts = [
        'require_checkin' => 'boolean',
        'require_feedback' => 'boolean',
        'order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(DoorprizePrize::class, 'session_id')->orderBy('order');
    }

    public function bans(): HasMany
    {
        return $this->hasMany(DoorprizeBan::class, 'session_id');
    }

    /**
     * Get total winners across all prizes in this session.
     */
    public function getTotalWinnersCountAttribute(): int
    {
        return $this->prizes->sum(fn ($prize) => $prize->winners()->count());
    }
}
