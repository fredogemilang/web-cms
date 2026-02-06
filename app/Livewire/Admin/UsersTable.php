<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $selectedUsers = [];
    public $selectAll = false;
    
    // Bulk change role
    public $bulkRoleId = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
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
            // Default to desc for date fields
            $this->sortDirection = in_array($field, ['created_at', 'last_login_at']) ? 'desc' : 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function getUsersProperty()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('roles.id', $this->roleFilter);
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getRolesProperty()
    {
        return Role::orderBy('name')->get();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        
        if ($user && $user->id !== auth()->id()) {
            $user->delete();
            session()->flash('success', 'User deleted successfully.');
        }
        
        $this->selectedUsers = array_diff($this->selectedUsers, [(string) $userId]);
    }

    public function deleteSelected()
    {
        $users = User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->get();
        
        foreach ($users as $user) {
            $user->delete();
        }
        
        $this->clearSelection();
        
        session()->flash('success', count($users) . ' user(s) deleted successfully.');
    }

    public function changeRoleSelected($roleId)
    {
        if (empty($roleId)) {
            return;
        }

        $role = Role::find($roleId);
        if (!$role) {
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->get();
        
        foreach ($users as $user) {
            $user->roles()->sync([$roleId]);
        }
        
        $this->clearSelection();
        $this->bulkRoleId = '';
        
        session()->flash('success', count($users) . ' user(s) role changed to "' . $role->name . '".');
    }

    public function activateSelected()
    {
        $count = User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->update(['is_active' => true]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' user(s) activated successfully.');
    }

    public function deactivateSelected()
    {
        $count = User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->update(['is_active' => false]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' user(s) deactivated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.users-table', [
            'users' => $this->users,
            'roles' => $this->roles,
        ]);
    }
}
