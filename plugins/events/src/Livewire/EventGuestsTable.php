<?php

namespace Plugins\Events\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;

class EventGuestsTable extends Component
{
    use WithPagination;

    /** The event to show guests for. Set via mount(). */
    public Event $event;

    /** ApprovalTypes grouped by cat, for approve/reject modals */
    public $approvalTypes;

    /** Active tab: all | pending | approved | rejected */
    public string $activeTab = 'all';

    /** Search query */
    public string $search = '';

    /** Date range filters */
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    /** Per-page pagination */
    public int $perPage = 25;

    /** Sorting */
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    /** Bulk selection */
    public array $selectedItems = [];
    public bool $selectAll = false;

    // Edit Modal state
    public bool $showEditModal = false;
    public ?int $editingGuestId = null;
    public string $editFullName = '';
    public string $editEmail = '';
    public string $editPhone = '';
    public string $editCompany = '';
    public string $editJobTitle = '';
    public string $editNotes = '';

    protected $queryString = [
        'activeTab' => ['except' => 'all'],
        'search'    => ['except' => ''],
        'dateFrom'  => ['except' => null],
        'dateTo'    => ['except' => null],
        'sortField'  => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage'   => ['except' => 25],
    ];

    public function mount(Event $event, $approvalTypes = null): void
    {
        $this->event = $event;
        if ($approvalTypes instanceof \Illuminate\Support\Collection) {
            $firstItem = $approvalTypes->first();
            if ($firstItem instanceof \Illuminate\Support\Collection || is_array($firstItem)) {
                $grouped = $approvalTypes;
            } else {
                $grouped = $approvalTypes->groupBy('cat');
            }
            
            $normalized = [];
            foreach ($grouped as $cat => $items) {
                $normalized[$cat] = collect($items)->map(function ($item) {
                    return [
                        'id' => is_array($item) ? $item['id'] : $item->id,
                        'type_name' => is_array($item) ? $item['type_name'] : $item->type_name,
                    ];
                })->toArray();
            }
            $this->approvalTypes = $normalized;
        } elseif (is_array($approvalTypes)) {
            $this->approvalTypes = $approvalTypes;
        } else {
            $this->approvalTypes = [];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedItems = $this->registrations->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    /**
     * Count badges per tab.
     */
    public function getGuestCountsProperty(): array
    {
        $base = EventRegistration::query()->where('event_id', $this->event->id);

        return [
            'all'      => (clone $base)->count(),
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'confirmed')->count(),
            'rejected' => (clone $base)->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Paginated registrations for current tab + search.
     */
    public function getRegistrationsProperty()
    {
        return EventRegistration::query()
            ->with(['event', 'user', 'verifiedBy'])
            ->where('event_id', $this->event->id)
            ->when($this->activeTab !== 'all', function ($query) {
                $map = ['pending' => 'pending', 'approved' => 'confirmed', 'rejected' => 'cancelled'];
                $query->where('status', $map[$this->activeTab]);
            })
            ->when($this->search, function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', $term)
                      ->orWhere('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('company_name', 'like', $term)
                      ->orWhere('organization', 'like', $term);
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }



    /**
     * Inline update a field via AJAX.
     */
    public function inlineUpdate(int $id, string $field, ?string $value): void
    {
        $allowed = ['full_name', 'name', 'email', 'phone', 'mobile_phone',
                    'organization', 'company_name', 'job_title', 'notes'];

        if (!in_array($field, $allowed)) {
            return;
        }

        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (!$reg) {
            return;
        }

        // Basic email validation
        if ($field === 'email' && $value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Invalid email address.']);
                return;
            }
        }

        $reg->update([$field => $value]);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Field updated.']);
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->activeTab = 'all';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    /**
     * Clear bulk selection.
     */
    public function clearSelection(): void
    {
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    /**
     * Check-in a guest.
     */
    public function checkin(int $id): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (!$reg) {
            return;
        }

        $reg->checkIn();
        if ($reg->status === 'confirmed') {
            $reg->update(['status' => 'attended']);
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Guest checked in.']);
    }

    public function editGuest(int $id): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if ($reg) {
            $this->editingGuestId = $id;
            $this->editFullName = $reg->full_name ?? $reg->name ?? '';
            $this->editEmail = $reg->email ?? '';
            $this->editPhone = $reg->mobile_phone ?? $reg->phone ?? '';
            $this->editCompany = $reg->company_name ?? $reg->organization ?? '';
            $this->editJobTitle = $reg->job_title ?? '';
            $this->editNotes = $reg->notes ?? '';
            $this->showEditModal = true;
        }
    }

    public function saveGuest(): void
    {
        $this->validate([
            'editFullName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255',
            'editPhone' => 'nullable|string|max:20',
            'editCompany' => 'nullable|string|max:255',
            'editJobTitle' => 'nullable|string|max:255',
            'editNotes' => 'nullable|string',
        ]);

        $reg = EventRegistration::where('id', $this->editingGuestId)
            ->where('event_id', $this->event->id)
            ->first();

        if ($reg) {
            $reg->update([
                'full_name' => $this->editFullName,
                'name' => $this->editFullName,
                'email' => $this->editEmail,
                'mobile_phone' => $this->editPhone,
                'phone' => $this->editPhone,
                'company_name' => $this->editCompany,
                'organization' => $this->editCompany,
                'job_title' => $this->editJobTitle,
                'notes' => $this->editNotes,
            ]);

            $this->showEditModal = false;
            $this->editingGuestId = null;
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Attendee updated successfully.']);
        }
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingGuestId = null;
    }

    public function deleteGuest(int $id): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();
            
        if ($reg) {
            $reg->delete();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Attendee deleted successfully.']);
        }
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedItems)) {
            return;
        }

        EventRegistration::whereIn('id', $this->selectedItems)
            ->where('event_id', $this->event->id)
            ->delete();

        $this->selectedItems = [];
        $this->selectAll = false;
        
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Selected attendees deleted successfully.']);
    }

    public function updateStatus(int $id, string $status, ?int $approvalTypeId = null, ?string $note = null): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (!$reg) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Attendee not found.']);
            return;
        }

        $oldStatus = $reg->status;

        // If no change, do nothing
        if ($oldStatus === $status) {
            $this->dispatch('show-toast', ['type' => 'info', 'message' => 'No status change made.']);
            return;
        }

        // Validate approval type if confirmed or cancelled is selected
        $approvalType = null;
        if (in_array($status, ['confirmed', 'cancelled'])) {
            if (!$approvalTypeId) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Please select a reason/approval type.']);
                return;
            }
            $approvalType = \Plugins\Events\Models\ApprovalType::find($approvalTypeId);
            if (!$approvalType) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Selected reason/approval type not found.']);
                return;
            }
        }

        \DB::transaction(function () use ($reg, $status, $oldStatus, $approvalType, $note) {
            // Adjust event registered count
            if ($status === 'confirmed' && $oldStatus !== 'confirmed') {
                $this->event->incrementRegisteredCount();
            } elseif ($status !== 'confirmed' && $oldStatus === 'confirmed') {
                $this->event->decrementRegisteredCount();
            }

            if ($status === 'confirmed') {
                $reg->update([
                    'status'         => 'confirmed',
                    'confirmed_at'   => now(),
                    'verified_by'    => auth()->id(),
                    'verified_at'    => now(),
                    'verified_type'  => $approvalType->type_name,
                    'verified_note'  => $note,
                ]);
            } elseif ($status === 'cancelled') {
                $reg->update([
                    'status'         => 'cancelled',
                    'cancelled_at'   => now(),
                    'verified_by'    => auth()->id(),
                    'verified_at'    => now(),
                    'verified_type'  => $approvalType->type_name,
                    'verified_note'  => $note,
                ]);
            } else {
                // Pending
                $reg->update([
                    'status'         => 'pending',
                    'confirmed_at'   => null,
                    'cancelled_at'   => null,
                    'verified_by'    => null,
                    'verified_at'    => null,
                    'verified_type'  => null,
                    'verified_note'  => null,
                ]);
            }
        });

        // Send Email notifications
        if ($status === 'confirmed' && $approvalType) {
            try {
                \Illuminate\Support\Facades\Mail::to($reg->email)->send(new \Plugins\Events\Mail\GuestApproved($reg, $approvalType));
            } catch (\Exception $e) {
                \Log::warning('Failed to send guest approved email via Livewire', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
            }
        } elseif ($status === 'cancelled' && $approvalType) {
            try {
                \Illuminate\Support\Facades\Mail::to($reg->email)->send(new \Plugins\Events\Mail\GuestRejected($reg, $approvalType));
            } catch (\Exception $e) {
                \Log::warning('Failed to send guest rejected email via Livewire', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
            }
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Status updated successfully.']);
    }

    public function bulkUpdateStatus(string $status, int $approvalTypeId, ?string $note = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No attendees selected.']);
            return;
        }

        $approvalType = \Plugins\Events\Models\ApprovalType::find($approvalTypeId);
        if (!$approvalType) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Selected reason/approval type not found.']);
            return;
        }

        $registrations = EventRegistration::whereIn('id', $this->selectedItems)
            ->where('event_id', $this->event->id)
            ->get();

        $count = 0;
        foreach ($registrations as $reg) {
            $oldStatus = $reg->status;
            if ($oldStatus === $status) {
                continue;
            }

            \DB::transaction(function () use ($reg, $status, $oldStatus, $approvalType, $note) {
                if ($status === 'confirmed' && $oldStatus !== 'confirmed') {
                    $this->event->incrementRegisteredCount();
                } elseif ($status !== 'confirmed' && $oldStatus === 'confirmed') {
                    $this->event->decrementRegisteredCount();
                }

                if ($status === 'confirmed') {
                    $reg->update([
                        'status'         => 'confirmed',
                        'confirmed_at'   => now(),
                        'verified_by'    => auth()->id(),
                        'verified_at'    => now(),
                        'verified_type'  => $approvalType->type_name,
                        'verified_note'  => $note,
                    ]);
                } elseif ($status === 'cancelled') {
                    $reg->update([
                        'status'         => 'cancelled',
                        'cancelled_at'   => now(),
                        'verified_by'    => auth()->id(),
                        'verified_at'    => now(),
                        'verified_type'  => $approvalType->type_name,
                        'verified_note'  => $note,
                    ]);
                }
            });

            // Send Email notifications
            if ($status === 'confirmed') {
                try {
                    \Illuminate\Support\Facades\Mail::to($reg->email)->send(new \Plugins\Events\Mail\GuestApproved($reg, $approvalType));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send guest approved email via Livewire bulk action', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
                }
            } elseif ($status === 'cancelled') {
                try {
                    \Illuminate\Support\Facades\Mail::to($reg->email)->send(new \Plugins\Events\Mail\GuestRejected($reg, $approvalType));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send guest rejected email via Livewire bulk action', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
                }
            }

            $count++;
        }

        $this->selectedItems = [];
        $this->selectAll = false;

        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Successfully updated {$count} attendee(s) status."]);
    }

    public function exportExcel()
    {
        $registrations = EventRegistration::with(['event', 'user', 'verifiedBy'])
            ->where('event_id', $this->event->id)
            ->when($this->activeTab !== 'all', function ($query) {
                $map = ['pending' => 'pending', 'approved' => 'confirmed', 'rejected' => 'cancelled'];
                $query->where('status', $map[$this->activeTab]);
            })
            ->when($this->search, function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', $term)
                      ->orWhere('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('company_name', 'like', $term)
                      ->orWhere('organization', 'like', $term);
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $headers = [
            'ID', 'UUID', 'Salutation', 'Full Name', 'Email', 'Phone', 'Company',
            'Company Type', 'Job Title', 'Status', 'Walk-in', 'Checked In',
            'Registered At', 'Confirmed At', 'Verified By', 'Verified At',
            'Verified Type', 'Verified Note', 'Referral Source',
        ];

        $customQuestions = $this->event->customQuestions()->ordered()->get();
        foreach ($customQuestions as $question) {
            $headers[] = $question->question;
        }

        $callback = function () use ($registrations, $headers, $customQuestions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

            fputcsv($handle, $headers);

            foreach ($registrations as $reg) {
                $row = [
                    $reg->id,
                    $reg->uuid,
                    $reg->salutation ?? '',
                    $reg->full_name ?? $reg->name,
                    $reg->email,
                    $reg->mobile_phone ?? $reg->phone ?? '',
                    $reg->company_name ?? $reg->organization ?? '',
                    $reg->company_type ?? '',
                    $reg->job_title ?? '',
                    ucfirst($reg->status),
                    $reg->walk_in ? 'Yes' : 'No',
                    $reg->check_in ? 'Yes' : 'No',
                    $reg->created_at->format('Y-m-d H:i:s'),
                    $reg->confirmed_at?->format('Y-m-d H:i:s') ?? '',
                    $reg->verifiedBy?->name ?? '',
                    $reg->verified_at?->format('Y-m-d H:i:s') ?? '',
                    $reg->verified_type ?? '',
                    $reg->verified_note ?? '',
                    $reg->referral_source ?? '',
                ];

                // Fetch custom answers for this registration
                $answers = \Plugins\Events\Models\EventCustomAnswer::where('event_registration_id', $reg->id)->get()->keyBy('question_id');
                foreach ($customQuestions as $question) {
                    $ans = $answers->get($question->id);
                    $answerVal = '';
                    if ($ans) {
                        $answerVal = is_array($ans->answer) ? implode(', ', $ans->answer) : $ans->answer;
                    }
                    $row[] = $answerVal;
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $filename = \Illuminate\Support\Str::slug($this->event->title) . '-guests-' . date('Ymd') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('events::livewire.event-guests-table', [
            'registrations' => $this->registrations,
            'guestCounts'   => $this->guestCounts,
        ]);
    }
}
