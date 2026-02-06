<?php

namespace Plugins\Membership\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipBenefit extends Model
{
    protected $fillable = [
        'tier_id',
        'benefit_type',
        'name',
        'description',
        'value',
        'order',
    ];

    protected $casts = [
        'value' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the tier.
     */
    public function tier()
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id');
    }

    /**
     * Get formatted value based on type.
     */
    public function getFormattedValueAttribute()
    {
        if ($this->benefit_type === 'discount' && isset($this->value['percentage'])) {
            return $this->value['percentage'] . '%';
        }

        if (isset($this->value['text'])) {
            return $this->value['text'];
        }

        return '';
    }
}
