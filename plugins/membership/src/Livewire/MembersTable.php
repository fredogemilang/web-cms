<?php

namespace Plugins\Membership\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Plugins\Membership\Models\Membership;

class MembersTable extends Component
{
    use WithPagination;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Bulk Actions
    public $selectedMembers = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function approveMember($id)
    {
        $membership = Membership::findOrFail($id);
        $membership->approve();
        
        session()->flash('success', 'Member approved successfully!');
    }

    public function rejectMember($id)
    {
        $membership = Membership::findOrFail($id);
        $membership->reject();
        
        session()->flash('success', 'Member rejected.');
    }

    public function suspendMember($id)
    {
        $membership = Membership::findOrFail($id);
        $membership->suspend();

        session()->flash('success', 'Member suspended.');
    }

    public function deleteMember($id)
    {
        $membership = Membership::findOrFail($id);
        $membership->delete();

        session()->flash('success', 'Member deleted successfully.');
    }

    public function bulkApprove()
    {
        foreach ($this->selectedMembers as $id) {
            $membership = Membership::find($id);
            if ($membership) {
                $membership->approve();
            }
        }
        
        $this->selectedMembers = [];
        session()->flash('success', 'Selected members approved!');
    }

    public function bulkDelete()
    {
        Membership::whereIn('id', $this->selectedMembers)->delete();
        
        $this->selectedMembers = [];
        session()->flash('success', 'Selected members deleted!');
    }

    public function render()
    {
        $query = Membership::with('user');

        // Search
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filters
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $memberships = $query->paginate($this->perPage);

        // Stats
        $stats = [
            'total' => Membership::count(),
            'active' => Membership::where('status', 'active')->count(),
            'pending' => Membership::where('status', 'pending')->count(),
            'rejected' => Membership::where('status', 'rejected')->count(),
        ];

        return view('membership::livewire.members-table', [
            'memberships' => $memberships,
            'stats' => $stats,
        ]);
    }
}
