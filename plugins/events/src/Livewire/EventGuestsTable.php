<?php

namespace Plugins\Events\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Events\Mail\GuestApproved;
use Plugins\Events\Mail\GuestRejected;
use Plugins\Events\Models\ApprovalType;
use Plugins\Events\Models\ContactLevel;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCustomAnswer;
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

    // Check-in confirmation modal state
    public bool $showCheckinConfirmModal = false;

    public ?int $checkinRegistrationId = null;

    public string $checkinRegistrationName = '';

    public bool $showBulkCheckinConfirmModal = false;

    public int $eligibleCheckinCount = 0;

    // New frontend-matching fields
    public int $editContactLevelId = 0;

    public string $editHighestEducationLevel = '';

    public string $editIndustry = '';

    public string $editDomicile = '';

    public string $editLinkedin = '';

    // Custom questions answers
    public array $editCustomQuestions = [];

    protected $queryString = [
        'activeTab' => ['except' => 'all'],
        'search' => ['except' => ''],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 25],
    ];

    public function mount(Event $event, $approvalTypes = null): void
    {
        $this->event = $event;
        if ($approvalTypes instanceof Collection) {
            $firstItem = $approvalTypes->first();
            if ($firstItem instanceof Collection || is_array($firstItem)) {
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
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function getGuestCountsProperty(): array
    {
        $base = EventRegistration::query()->where('event_id', $this->event->id);

        return [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'checkin' => (clone $base)->where('check_in', true)->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
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
                if ($this->activeTab === 'checkin') {
                    $query->where('check_in', true);
                } else {
                    $map = ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected'];
                    if (isset($map[$this->activeTab])) {
                        $query->where('status', $map[$this->activeTab]);
                    }
                }
            })
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('organization', 'like', $term);
                });
            })
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
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

        if (! in_array($field, $allowed)) {
            return;
        }

        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (! $reg) {
            return;
        }

        // Basic email validation
        if ($field === 'email' && $value) {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
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
     * Start confirmation for checking in a guest.
     */
    public function confirmCheckin(int $id): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if ($reg) {
            if ($reg->status !== 'approved') {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Only approved guests can check in.']);

                return;
            }
            $this->checkinRegistrationId = $id;
            $this->checkinRegistrationName = $reg->full_name ?? $reg->name;
            $this->showCheckinConfirmModal = true;
        }
    }

    /**
     * Cancel/close single check-in confirmation.
     */
    public function cancelCheckin(): void
    {
        $this->showCheckinConfirmModal = false;
        $this->checkinRegistrationId = null;
        $this->checkinRegistrationName = '';
    }

    /**
     * Execute single check-in after confirmation.
     */
    public function executeCheckin(): void
    {
        if ($this->checkinRegistrationId) {
            $this->checkin($this->checkinRegistrationId);
            $this->cancelCheckin();
        }
    }

    /**
     * Start confirmation for bulk check-in.
     */
    public function confirmBulkCheckin(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No attendees selected.']);

            return;
        }

        $this->eligibleCheckinCount = EventRegistration::whereIn('id', $this->selectedItems)
            ->where('event_id', $this->event->id)
            ->where('status', 'approved')
            ->where('check_in', false)
            ->count();

        if ($this->eligibleCheckinCount === 0) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'None of the selected guests are approved (Confirmed) and eligible for check-in.']);

            return;
        }

        $this->showBulkCheckinConfirmModal = true;
    }

    /**
     * Cancel bulk check-in.
     */
    public function cancelBulkCheckin(): void
    {
        $this->showBulkCheckinConfirmModal = false;
        $this->eligibleCheckinCount = 0;
    }

    /**
     * Execute bulk check-in after confirmation.
     */
    public function executeBulkCheckin(): void
    {
        $this->bulkCheckin();
        $this->cancelBulkCheckin();
    }

    /**
     * Check-in a guest.
     */
    public function checkin(int $id): void
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (! $reg) {
            return;
        }

        if ($reg->status !== 'approved') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Only approved guests can check in.']);

            return;
        }

        $reg->checkIn();

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Guest checked in.']);
    }

    /**
     * Bulk check-in selected guests.
     */
    public function bulkCheckin(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No attendees selected.']);

            return;
        }

        $registrations = EventRegistration::whereIn('id', $this->selectedItems)
            ->where('event_id', $this->event->id)
            ->get();

        $count = 0;
        foreach ($registrations as $reg) {
            if (! $reg->check_in && $reg->status === 'approved') {
                $reg->checkIn();
                $count++;
            }
        }

        $this->selectedItems = [];
        $this->selectAll = false;

        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Successfully checked in {$count} approved attendee(s)."]);
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

            // Load frontend fields
            $this->editContactLevelId = $reg->contact_level_id ?? 0;
            $customFields = $reg->custom_fields ?? [];
            $this->editHighestEducationLevel = $customFields['highest_education_level'] ?? '';
            $this->editIndustry = $customFields['industry'] ?? '';
            $this->editDomicile = $customFields['domicile'] ?? '';
            $this->editLinkedin = $customFields['linkedin'] ?? '';

            // Load custom question answers
            $this->editCustomQuestions = [];
            foreach ($this->event->customQuestions as $question) {
                $answer = $reg->getCustomAnswer($question->short_label);
                if ($question->type === 'multi_select') {
                    $this->editCustomQuestions[$question->short_label] = is_array($answer) ? $answer : ($answer ? explode(', ', $answer) : []);
                } else {
                    $this->editCustomQuestions[$question->short_label] = $answer ?? '';
                }
            }

            $this->showEditModal = true;
        }
    }

    public function saveGuest(): void
    {
        $rules = [
            'editFullName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255',
            'editPhone' => 'nullable|string|max:20',
            'editCompany' => 'nullable|string|max:255',
            'editJobTitle' => 'nullable|string|max:255',
            'editNotes' => 'nullable|string',
            'editContactLevelId' => 'required|integer|min:1|exists:contact_levels,id',
            'editHighestEducationLevel' => 'nullable|string',
            'editIndustry' => 'required|string',
            'editDomicile' => 'required|string',
            'editLinkedin' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/.*$/i'],
        ];

        // Validate custom questions
        foreach ($this->event->customQuestions as $question) {
            $questionRules = [];
            if ($question->required) {
                if ($question->type === 'multi_select') {
                    $questionRules[] = 'required';
                    $questionRules[] = 'array';
                    $questionRules[] = 'min:1';
                } else {
                    $questionRules[] = 'required';
                }
            } else {
                $questionRules[] = 'nullable';
            }

            switch ($question->type) {
                case 'email':
                    $questionRules[] = 'email';
                    break;
                case 'phone':
                    $questionRules[] = 'regex:/^[0-9\-\+\(\)\s]{6,20}$/';
                    break;
                case 'date':
                    $questionRules[] = 'date';
                    break;
            }

            if (! empty($questionRules)) {
                $rules['editCustomQuestions.'.$question->short_label] = $questionRules;
            }
        }

        $this->validate($rules, [
            'editLinkedin.regex' => 'LinkedIn account must be a valid LinkedIn URL.',
        ]);

        $reg = EventRegistration::where('id', $this->editingGuestId)
            ->where('event_id', $this->event->id)
            ->first();

        if ($reg) {
            $customFields = [
                'highest_education_level' => $this->editHighestEducationLevel,
                'industry' => $this->editIndustry,
                'domicile' => $this->editDomicile,
                'linkedin' => $this->editLinkedin,
            ];

            $reg->update([
                'full_name' => $this->editFullName,
                'name' => $this->editFullName,
                'email' => $this->editEmail,
                'mobile_phone' => EventRegistration::formatPhoneNumber($this->editPhone),
                'phone' => $this->editPhone,
                'company_name' => $this->editCompany,
                'organization' => $this->editCompany,
                'job_title' => $this->editJobTitle,
                'notes' => $this->editNotes,
                'contact_level_id' => $this->editContactLevelId,
                'custom_fields' => $customFields,
            ]);

            // Save custom question answers
            foreach ($this->editCustomQuestions as $shortLabel => $answer) {
                $question = $this->event->customQuestions()
                    ->where('short_label', $shortLabel)
                    ->first();

                if (! $question) {
                    continue;
                }

                $storeValue = is_array($answer) ? $answer : (is_string($answer) ? trim($answer) : $answer);

                if ($storeValue === null || $storeValue === '' || (is_array($storeValue) && empty(array_filter($storeValue)))) {
                    EventCustomAnswer::where('event_registration_id', $reg->id)
                        ->where('question_id', $question->id)
                        ->delete();

                    continue;
                }

                EventCustomAnswer::updateOrCreate(
                    [
                        'event_registration_id' => $reg->id,
                        'question_id' => $question->id,
                    ],
                    ['answer' => $storeValue]
                );
            }

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

    public function updateStatus(int $id, string $status, ?int $approvalTypeId = null, ?string $note = null): array
    {
        $reg = EventRegistration::where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (! $reg) {
            return ['success' => false, 'message' => 'Attendee registration not found.'];
        }

        $oldStatus = $reg->status;

        // If no change, do nothing
        if ($oldStatus === $status) {
            return ['success' => true, 'message' => 'No status change made.'];
        }

        // Validate approval type if confirmed or cancelled is selected
        $approvalType = null;
        if (in_array($status, ['approved', 'rejected'])) {
            if (! $approvalTypeId) {
                return ['success' => false, 'message' => 'Please select a reason/approval type.'];
            }
            $approvalType = ApprovalType::find($approvalTypeId);
            if (! $approvalType) {
                return ['success' => false, 'message' => 'Selected reason/approval type not found.'];
            }
        }

        try {
            \DB::transaction(function () use ($reg, $status, $oldStatus, $approvalType, $note) {
                // Adjust event registered count
                if ($status === 'approved' && $oldStatus !== 'approved') {
                    $this->event->incrementRegisteredCount();
                } elseif ($status !== 'approved' && $oldStatus === 'approved') {
                    $this->event->decrementRegisteredCount();
                }

                if ($status === 'approved') {
                    $reg->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'verified_type' => $approvalType->type_name,
                        'verified_note' => $note,
                    ]);
                } elseif ($status === 'rejected') {
                    $reg->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'verified_type' => $approvalType->type_name,
                        'verified_note' => $note,
                    ]);
                } else {
                    // Pending
                    $reg->update([
                        'status' => 'pending',
                        'approved_at' => null,
                        'rejected_at' => null,
                        'verified_by' => null,
                        'verified_at' => null,
                        'verified_type' => null,
                        'verified_note' => null,
                    ]);
                }
            });

            // Send Email notifications
            if ($status === 'approved' && $approvalType) {
                try {
                    Mail::to($reg->email)->send(new GuestApproved($reg, $approvalType));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send guest approved email via Livewire', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
                }
            } elseif ($status === 'rejected' && $approvalType) {
                try {
                    Mail::to($reg->email)->send(new GuestRejected($reg, $approvalType));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send guest rejected email via Livewire', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
                }
            }

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Status updated successfully.']);

            return ['success' => true, 'message' => 'Status updated successfully.'];
        } catch (\Exception $e) {
            \Log::error('Error saving guest status: '.$e->getMessage());

            return ['success' => false, 'message' => 'Failed to save status: '.$e->getMessage()];
        }
    }

    public function bulkUpdateStatus(string $status, int $approvalTypeId, ?string $note = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No attendees selected.']);

            return;
        }

        $approvalType = ApprovalType::find($approvalTypeId);
        if (! $approvalType) {
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
                if ($status === 'approved' && $oldStatus !== 'approved') {
                    $this->event->incrementRegisteredCount();
                } elseif ($status !== 'approved' && $oldStatus === 'approved') {
                    $this->event->decrementRegisteredCount();
                }

                if ($status === 'approved') {
                    $reg->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'verified_type' => $approvalType->type_name,
                        'verified_note' => $note,
                    ]);
                } elseif ($status === 'rejected') {
                    $reg->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'verified_type' => $approvalType->type_name,
                        'verified_note' => $note,
                    ]);
                }
            });

            // Send Email notifications
            if ($status === 'approved') {
                try {
                    Mail::to($reg->email)->send(new GuestApproved($reg, $approvalType));
                } catch (\Exception $e) {
                    \Log::warning('Failed to send guest approved email via Livewire bulk action', ['registration_id' => $reg->id, 'error' => $e->getMessage()]);
                }
            } elseif ($status === 'rejected') {
                try {
                    Mail::to($reg->email)->send(new GuestRejected($reg, $approvalType));
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
                if ($this->activeTab === 'checkin') {
                    $query->where('check_in', true);
                } else {
                    $map = ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected'];
                    if (isset($map[$this->activeTab])) {
                        $query->where('status', $map[$this->activeTab]);
                    }
                }
            })
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('organization', 'like', $term);
                });
            })
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $headers = [
            'ID', 'UUID', 'Salutation', 'Full Name', 'Email', 'Phone', 'Company',
            'Company Type', 'Job Title', 'Status', 'Walk-in', 'Checked In',
            'Registered At', 'Approved At', 'Verified By', 'Verified At',
            'Verified Type', 'Verified Note', 'Referral Source',
        ];

        $customQuestions = $this->event->customQuestions()->ordered()->get();
        foreach ($customQuestions as $question) {
            $headers[] = $question->question;
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $rowNumber = 2;
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
                $reg->approved_at?->format('Y-m-d H:i:s') ?? '',
                $reg->verifiedBy?->name ?? '',
                $reg->verified_at?->format('Y-m-d H:i:s') ?? '',
                $reg->verified_type ?? '',
                $reg->verified_note ?? '',
                $reg->referral_source ?? '',
            ];

            // Fetch custom answers for this registration
            $answers = EventCustomAnswer::where('event_registration_id', $reg->id)->get()->keyBy('question_id');
            foreach ($customQuestions as $question) {
                $ans = $answers->get($question->id);
                $answerVal = '';
                if ($ans) {
                    $answerVal = is_array($ans->answer) ? implode(', ', $ans->answer) : $ans->answer;
                }
                $row[] = $answerVal;
            }

            $sheet->fromArray($row, null, 'A'.$rowNumber);
            $rowNumber++;
        }

        $filename = Str::slug($this->event->title).'-guests-'.date('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render(): View
    {
        return view('events::livewire.event-guests-table', [
            'registrations' => $this->registrations,
            'guestCounts' => $this->guestCounts,
            'contactLevels' => ContactLevel::orderBy('id')->get(),
        ]);
    }
}
