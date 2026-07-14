<?php

namespace Plugins\Events\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Plugins\Events\Mail\GuestApproved;
use Plugins\Events\Mail\GuestRejected;
use Plugins\Events\Models\ApprovalType;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;

class EventGuestService
{
    /**
     * Approve a single guest registration.
     *
     * @throws \Exception if capacity is reached
     */
    public function approve(
        Event $event,
        EventRegistration $registration,
        ApprovalType $approvalType,
        ?string $note = null
    ): void {
        // Capacity check
        if ($event->registration_requires_approval && $event->max_participants > 0) {
            $approvedCount = $this->getApprovedCount($event);
            if ($approvedCount >= $event->max_participants) {
                throw new \Exception('Event capacity has been reached.');
            }
        }

        DB::transaction(function () use ($event, $registration, $approvalType, $note) {
            $registration->update([
                'status' => 'approved',
                'approved_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verified_type' => $approvalType->type_name,
                'verified_note' => $note,
            ]);

            $event->incrementRegisteredCount();
        });

        // Send approval email (dispatched to queue via ShouldQueue)
        try {
            Mail::to($registration->email)->send(new GuestApproved($registration, $approvalType));
        } catch (\Exception $e) {
            // Log but don't fail the approval action
            \Log::warning('Failed to send guest approved email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reject a guest registration.
     */
    public function reject(
        Event $event,
        EventRegistration $registration,
        ApprovalType $approvalType,
        ?string $note = null
    ): void {
        $wasApproved = $registration->status === 'approved';

        DB::transaction(function () use ($event, $registration, $approvalType, $note, $wasApproved) {
            $registration->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verified_type' => $approvalType->type_name,
                'verified_note' => $note,
            ]);

            if ($wasApproved) {
                $event->decrementRegisteredCount();
            }
        });

        // Send rejection email
        try {
            Mail::to($registration->email)->send(new GuestRejected($registration, $approvalType));
        } catch (\Exception $e) {
            \Log::warning('Failed to send guest rejected email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk approve a collection of registrations.
     * Returns the number successfully approved.
     */
    public function bulkApprove(Event $event, Collection $registrations, ApprovalType $approvalType): int
    {
        $count = 0;
        $maxParticipants = $event->max_participants ?? 0;
        $approvedCount = $this->getApprovedCount($event);
        $requiresCapacityCheck = $event->registration_requires_approval && $maxParticipants > 0;

        foreach ($registrations as $registration) {
            // Stop if capacity reached
            if ($requiresCapacityCheck && $approvedCount >= $maxParticipants) {
                break;
            }

            try {
                $this->approve($event, $registration, $approvalType);
                $count++;
                $approvedCount++;
            } catch (\Exception $e) {
                // Skip individual failures
                continue;
            }
        }

        return $count;
    }

    /**
     * Bulk reject a collection of registrations.
     * Returns the number successfully rejected.
     */
    public function bulkReject(Event $event, Collection $registrations, ApprovalType $approvalType): int
    {
        $count = 0;

        foreach ($registrations as $registration) {
            try {
                $this->reject($event, $registration, $approvalType);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $count;
    }

    /**
     * Get the current approved registration count for an event.
     */
    public function getApprovedCount(Event $event): int
    {
        return EventRegistration::where('event_id', $event->id)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Get remaining slots for an event.
     * Returns null if no capacity limit is set.
     */
    public function getRemainingSlots(Event $event): ?int
    {
        if (! $event->registration_requires_approval || $event->max_participants <= 0) {
            return null;
        }

        return max(0, $event->max_participants - $this->getApprovedCount($event));
    }
}
