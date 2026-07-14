<?php

namespace Plugins\Membership\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class MembershipBenefit extends Model
{
    use HasTranslations;

    protected $fillable = [
        'tier_id',
        'benefit_type',
        'name',
        'description',
        'value',
        'order',
        'translations',
    ];

    protected array $translatable = ['name', 'description'];

    protected $casts = [
        'value' => 'array',
        'order' => 'integer',
        'translations' => 'array',
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
            return $this->value['percentage'].'%';
        }

        if (isset($this->value['text'])) {
            return $this->value['text'];
        }

        return '';
    }
}
