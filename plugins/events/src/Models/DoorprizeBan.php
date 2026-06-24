<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoorprizeBan extends Model
{
    protected $table = 'event_doorprize_bans';

    protected $fillable = [
        'session_id',
        'registration_id',
        'reason',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DoorprizeSession::class, 'session_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'registration_id');
    }
}
