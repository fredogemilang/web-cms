<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoorprizeWinner extends Model
{
    protected $table = 'event_doorprize_winners';

    protected $fillable = [
        'prize_id',
        'registration_id',
        'status',
        'won_at',
    ];

    protected $casts = [
        'won_at' => 'datetime',
    ];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(DoorprizePrize::class, 'prize_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'registration_id');
    }
}
