<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'organization',
        'notes',
        'status',
        'confirmed_at',
        'cancelled_at',
        'custom_fields',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'custom_fields' => 'array',
    ];

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

    /**
     * Scope: Confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Cancelled registrations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Confirm the registration.
     */
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Increment event registered count
        $this->event->incrementRegisteredCount();
    }

    /**
     * Cancel the registration.
     */
    public function cancel()
    {
        $wasConfirmed = $this->status === 'confirmed';

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Decrement event registered count if was confirmed
        if ($wasConfirmed) {
            $this->event->decrementRegisteredCount();
        }
    }

    /**
     * Mark as attended.
     */
    public function markAsAttended()
    {
        $this->update(['status' => 'attended']);
    }

    /**
     * Get custom field value.
     */
    public function getCustomField($key, $default = null)
    {
        return $this->custom_fields[$key] ?? $default;
    }

    /**
     * Export to array.
     */
    public function toExportArray()
    {
        $export = [
            'ID' => $this->id,
            'Event' => $this->event->title,
            'Name' => $this->name,
            'Email' => $this->email,
            'Phone' => $this->phone,
            'Organization' => $this->organization,
            'Status' => ucfirst($this->status),
            'Registered At' => $this->created_at->format('Y-m-d H:i:s'),
        ];

        if ($this->confirmed_at) {
            $export['Confirmed At'] = $this->confirmed_at->format('Y-m-d H:i:s');
        }

        if ($this->custom_fields) {
            foreach ($this->custom_fields as $key => $value) {
                $export[ucfirst(str_replace('_', ' ', $key))] = $value;
            }
        }

        return $export;
    }
}
