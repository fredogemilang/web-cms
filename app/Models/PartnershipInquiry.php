<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipInquiry extends Model
{
    protected $fillable = [
        'company_name',
        'website',
        'contact_name',
        'email',
        'partnership_type',
        'message',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const TYPES = [
        'corporate' => 'Corporate Partner (Sponsorship)',
        'university' => 'University Partner (Education)',
        'community' => 'Community Partner (Collaboration)',
        'media' => 'Media Partner',
        'other' => 'Other',
    ];
}
