<?php

namespace Plugins\Events\Livewire;

use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;

class RegistrationsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $eventFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $selectedRegistrations = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'eventFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEventFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRegistrations = $this->registrations->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRegistrations = [];
        }
    }

    public function getRegistrationsProperty()
    {
        return EventRegistration::query()
            ->with(['event', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->eventFilter, function ($query) {
                $query->where('event_id', $this->eventFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getEventsProperty()
    {
        return Event::orderBy('start_date', 'desc')->get();
    }

    public function getStatusCountsProperty()
    {
        $baseQuery = EventRegistration::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->eventFilter, function ($query) {
                $query->where('event_id', $this->eventFilter);
            });

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'attended' => (clone $baseQuery)->where('status', 'attended')->count(),
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->eventFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedRegistrations = [];
        $this->selectAll = false;
    }

    public function updateStatus($registrationId, $status)
    {
        $registration = EventRegistration::find($registrationId);
        
        if ($registration) {
            $registration->update(['status' => $status]);
            session()->flash('success', 'Registration status updated successfully.');
        }
    }

    public function confirmSelected()
    {
        $count = EventRegistration::whereIn('id', $this->selectedRegistrations)
            ->update(['status' => 'confirmed']);
        
        $this->clearSelection();
        session()->flash('success', $count . ' registration(s) confirmed.');
    }

    public function cancelSelected()
    {
        $count = EventRegistration::whereIn('id', $this->selectedRegistrations)
            ->update(['status' => 'cancelled']);
        
        $this->clearSelection();
        session()->flash('success', $count . ' registration(s) cancelled.');
    }

    public function deleteSelected()
    {
        $count = EventRegistration::whereIn('id', $this->selectedRegistrations)->delete();
        
        $this->clearSelection();
        session()->flash('success', $count . ' registration(s) deleted.');
    }

    public function render()
    {
        return view('events::livewire.registrations-table', [
            'registrations' => $this->registrations,
            'events' => $this->events,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
