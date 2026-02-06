<?php

namespace Plugins\Membership\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Membership extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'joined_at',
        'notes',
        'metadata',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Pending approval.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Rejected memberships.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Suspended memberships.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Check if membership is active.
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'suspended' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Approve membership.
     */
    public function approve($approverId = null)
    {
        $this->update([
            'status' => 'active',
            'joined_at' => now(),
            'approved_by' => $approverId ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject membership.
     */
    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    /**
     * Suspend membership.
     */
    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Reactivate membership.
     */
    public function reactivate()
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Export to array.
     */
    public function toExportArray()
    {
        return [
            'ID' => $this->id,
            'Member Name' => $this->user->name,
            'Email' => $this->user->email,
            'Status' => ucfirst($this->status),
            'Joined Date' => $this->joined_at ? $this->joined_at->format('Y-m-d') : 'N/A',
            'Approved By' => $this->approver?->name ?? 'N/A',
            'Registered At' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
