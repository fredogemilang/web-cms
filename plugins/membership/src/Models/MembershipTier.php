<?php

namespace Plugins\Membership\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MembershipTier extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_months',
        'benefits',
        'is_active',
        'order',
        'color',
        'icon',
        'translations',
    ];

    /**
     * Per-locale fields. Price / duration / benefits list are factual and shared.
     * `benefits` (array of strings) could be translatable too — kept shared for now
     * since it's typically used as IDs/keys in this design.
     */
    protected array $translatable = ['name', 'slug', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_months' => 'integer',
        'benefits' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'translations' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tier) {
            if (empty($tier->slug)) {
                $tier->slug = Str::slug($tier->name);
            }
        });
    }

    /**
     * Get memberships in this tier.
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class, 'tier_id');
    }

    /**
     * Get benefits for this tier.
     */
    public function tierBenefits()
    {
        return $this->hasMany(MembershipBenefit::class, 'tier_id')->orderBy('order');
    }

    /**
     * Get active memberships count.
     */
    public function getActiveMembersCountAttribute()
    {
        return $this->memberships()->where('status', 'active')->count();
    }

    /**
     * Check if tier is lifetime.
     */
    public function getIsLifetimeAttribute()
    {
        return is_null($this->duration_months);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    /**
     * Get duration text.
     */
    public function getDurationTextAttribute()
    {
        if ($this->is_lifetime) {
            return 'Lifetime';
        }

        $years = floor($this->duration_months / 12);
        $months = $this->duration_months % 12;

        $parts = [];
        if ($years > 0) {
            $parts[] = $years.' '.Str::plural('year', $years);
        }
        if ($months > 0) {
            $parts[] = $months.' '.Str::plural('month', $months);
        }

        return implode(' ', $parts);
    }

    /**
     * Scope: Active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
