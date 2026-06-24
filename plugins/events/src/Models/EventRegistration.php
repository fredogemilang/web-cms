<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id', 'user_id', 'name', 'full_name', 'salutation',
        'company_name', 'company_type', 'job_title',
        'contact_level_id', 'contact_divisi_id', 'contact_divisi_name',
        'country_code', 'mobile_phone', 'uuid', 'qr_image',
        'referral_code', 'referral_source', 'email',
        'phone', 'organization', 'notes', 'status',
        'approved_at', 'rejected_at', 'consent_accepted_at',
        'walk_in', 'check_in', 'check_in_date', 'registration_type',
        'custom_fields', 'ip_address', 'user_agent',
        // PRD 04 — Verified tracking
        'verified_by', 'verified_at', 'verified_type', 'verified_note',
    ];

    protected $casts = [
        'approved_at'         => 'datetime',
        'rejected_at'         => 'datetime',
        'check_in_date'       => 'datetime',
        'consent_accepted_at' => 'datetime',
        'verified_at'         => 'datetime',
        'walk_in'             => 'boolean',
        'check_in'            => 'boolean',
        'custom_fields'       => 'array',
    ];

    /**
     * Boot: auto-generate UUID on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reg) {
            if (empty($reg->uuid)) {
                $reg->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Get the user (if registered user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** User who verified (approved/rejected) this registration. */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get contact level.
     */
    public function contactLevel()
    {
        return $this->belongsTo(ContactLevel::class, 'contact_level_id');
    }

    /**
     * Get contact division.
     */
    public function contactDivision()
    {
        return $this->belongsTo(ContactDivision::class, 'contact_divisi_id');
    }

    /**
     * Scope: Approved registrations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Active (non-rejected).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Approve the registration.
     */
    public function approve(): void
    {
        $this->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);
        $this->event->incrementRegisteredCount();
    }

    /**
     * Reject the registration.
     */
    public function reject(): void
    {
        $wasApproved = $this->status === 'approved';

        $this->update([
            'status'      => 'rejected',
            'rejected_at' => now(),
        ]);

        if ($wasApproved) {
            $this->event->decrementRegisteredCount();
        }
    }

    /**
     * Check in the registrant (walk-in or pre-registered).
     */
    public function checkIn(): void
    {
        $this->update([
            'check_in'      => true,
            'check_in_date' => now(),
        ]);
    }


    /**
     * Get custom field value.
     */
    public function getCustomField(string $key, mixed $default = null): mixed
    {
        return $this->custom_fields[$key] ?? $default;
    }

    /**
     * Custom question answers for this registration.
     */
    public function customAnswers()
    {
        return $this->hasMany(EventCustomAnswer::class, 'event_registration_id');
    }

    /**
     * Get answer for a specific question by short_label.
     */
    public function getCustomAnswer(string $shortLabel): mixed
    {
        return $this->customAnswers()
            ->whereHas('question', fn($q) => $q->where('short_label', $shortLabel))
            ->first()?->answer;
    }

    /**
     * Detect company type from company name.
     * Auto-detects PT, CV, Firma, UD, etc.
     */
    public static function detectCompanyType(string $companyName): ?string
    {
        $types = ['PT', 'CV', 'Firma', 'UD', 'Yayasan', 'Koperasi',
                  'Ltd', 'LLC', 'Inc', 'Corp', 'Pte Ltd', 'GmbH', 'SA', 'AG'];

        foreach ($types as $type) {
            if (stripos($companyName, $type) === 0) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Format phone number to international format.
     * Converts 08xx → +62xx format.
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^\d]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Build referral source from UTM params and tracking code.
     */
    public static function buildReferralSource(?string $trackingCode, \Illuminate\Http\Request $request): string
    {
        // Check tracking code first
        if ($trackingCode) {
            $tracking = TrackingCode::where('tracking_code', $trackingCode)->first();
            if ($tracking) {
                return $tracking->source;
            }
        }

        // Fallback to UTM parameters
        $source   = $request->query('utm_source', 'Direct');
        $campaign = $request->query('utm_campaign');
        $medium   = $request->query('utm_medium');

        if ($campaign) {
            $source .= ' - ' . $campaign;
        }
        if ($medium) {
            $source .= ' (' . $medium . ')';
        }

        return $source ?: 'Direct';
    }

    /**
     * Export to array for CSV.
     */
    public function toExportArray(): array
    {
        $export = [
            'ID'           => $this->id,
            'UUID'         => $this->uuid,
            'Event'        => $this->event->title,
            'Salutation'   => $this->salutation,
            'Full Name'    => $this->full_name ?? $this->name,
            'Company'      => $this->company_name ?? $this->organization,
            'Company Type' => $this->company_type,
            'Job Title'    => $this->job_title,
            'Email'        => $this->email,
            'Mobile Phone' => $this->mobile_phone ?? $this->phone,
            'Referral Code'=> $this->referral_code,
            'Referral Src' => $this->referral_source,
            'Status'       => ucfirst($this->status),
            'Walk-in'      => $this->walk_in ? 'Yes' : 'No',
            'Checked In'   => $this->check_in ? 'Yes' : 'No',
            'Registered At'=> $this->created_at->format('Y-m-d H:i:s'),
        ];

        if ($this->approved_at) {
            $export['Approved At'] = $this->approved_at->format('Y-m-d H:i:s');
        }

        if ($this->custom_fields) {
            foreach ($this->custom_fields as $key => $value) {
                $export[ucfirst(str_replace('_', ' ', $key))] = $value;
            }
        }

        // Add custom question answers
        foreach ($this->event->customQuestions as $question) {
            $answer = $this->getCustomAnswer($question->short_label);
            if (!is_null($answer)) {
                $export[$question->short_label] = is_array($answer)
                    ? implode(', ', $answer)
                    : $answer;
            }
        }

        return $export;
    }
}