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
        $this->approvalTypes = $approvalTypes ?? collect();
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
     * Approve a single guest via Livewire (bypasses full page reload).
     */
    public function approve(int $id): void
    {
        $this->dispatch('open-approve-modal', registrationId: $id);
    }

    /**
     * Reject a single guest via Livewire.
     */
    public function reject(int $id): void
    {
        $this->dispatch('open-reject-modal', registrationId: $id);
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

    public function render(): \Illuminate\View\View
    {
        return view('events::livewire.event-guests-table', [
            'registrations' => $this->registrations,
            'guestCounts'   => $this->guestCounts,
        ]);
    }
}
