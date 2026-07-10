<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoorprizePrize extends Model
{
    protected $table = 'event_doorprize_prizes';

    protected $fillable = [
        'session_id',
        'name',
        'gift_description',
        'max_winners',
        'image',
        'order',
    ];

    protected $casts = [
        'max_winners' => 'integer',
        'order' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DoorprizeSession::class, 'session_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(DoorprizeWinner::class, 'prize_id');
    }

    public function activeWinners(): HasMany
    {
        return $this->hasMany(DoorprizeWinner::class, 'prize_id')->where('status', 'active');
    }

    /**
     * Check if this prize still has available winner slots.
     */
    public function getHasAvailableSlotsAttribute(): bool
    {
        return $this->activeWinners()->count() < $this->max_winners;
    }

    /**
     * Get remaining winner slots.
     */
    public function getRemainingSlots(): int
    {
        return max(0, $this->max_winners - $this->activeWinners()->count());
    }
}
