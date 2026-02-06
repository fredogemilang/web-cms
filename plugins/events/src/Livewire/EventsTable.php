<?php

namespace Plugins\Events\Livewire;

use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCategory;
use Livewire\Component;
use Livewire\WithPagination;

class EventsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $typeFilter = '';
    public $timeFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    
    public $selectedEvents = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'timeFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'start_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingTimeFilter()
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
            $this->sortDirection = in_array($field, ['start_date', 'created_at', 'registered_count']) ? 'desc' : 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedEvents = $this->events->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedEvents = [];
        }
    }

    public function getEventsProperty()
    {
        return Event::query()
            ->with(['category', 'author', 'featuredImage'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'trashed') {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('event_type', $this->typeFilter);
            })
            ->when($this->timeFilter, function ($query) {
                switch ($this->timeFilter) {
                    case 'upcoming':
                        $query->upcoming();
                        break;
                    case 'past':
                        $query->past();
                        break;
                    case 'ongoing':
                        $query->ongoing();
                        break;
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return EventCategory::orderBy('order')->get();
    }

    public function getStatusCountsProperty()
    {
        $baseQuery = Event::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('event_type', $this->typeFilter);
            })
            ->when($this->timeFilter, function ($query) {
                switch ($this->timeFilter) {
                    case 'upcoming':
                        $query->upcoming();
                        break;
                    case 'past':
                        $query->past();
                        break;
                    case 'ongoing':
                        $query->ongoing();
                        break;
                }
            });

        return [
            'all' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'trashed' => (clone $baseQuery)->onlyTrashed()->count(),
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->categoryFilter = '';
        $this->typeFilter = '';
        $this->timeFilter = '';
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedEvents = [];
        $this->selectAll = false;
    }

    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);
        
        if ($event) {
            $event->delete();
            session()->flash('success', 'Event deleted successfully.');
        }
        
        $this->selectedEvents = array_diff($this->selectedEvents, [(string) $eventId]);
    }

    public function deleteSelected()
    {
        $count = Event::whereIn('id', $this->selectedEvents)->delete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) deleted successfully.');
    }

    public function publishSelected()
    {
        $count = Event::whereIn('id', $this->selectedEvents)
            ->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) published successfully.');
    }

    public function draftSelected()
    {
        $count = Event::whereIn('id', $this->selectedEvents)
            ->update(['status' => 'draft']);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) moved to draft.');
    }

    public function cancelSelected()
    {
        $count = Event::whereIn('id', $this->selectedEvents)
            ->update(['status' => 'cancelled']);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) cancelled.');
    }

    public function restore($eventId)
    {
        $event = Event::onlyTrashed()->find($eventId);
        
        if ($event) {
            $event->restore();
            session()->flash('success', 'Event restored successfully.');
        }
    }

    public function forceDelete($eventId)
    {
        $event = Event::onlyTrashed()->find($eventId);
        
        if ($event) {
            $event->forceDelete();
            session()->flash('success', 'Event deleted permanently.');
        }
    }

    public function restoreSelected()
    {
        $count = Event::onlyTrashed()->whereIn('id', $this->selectedEvents)->restore();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) restored successfully.');
    }

    public function forceDeleteSelected()
    {
        $count = Event::onlyTrashed()->whereIn('id', $this->selectedEvents)->forceDelete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' event(s) deleted permanently.');
    }

    public function render()
    {
        return view('events::livewire.events-table', [
            'events' => $this->events,
            'categories' => $this->categories,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
