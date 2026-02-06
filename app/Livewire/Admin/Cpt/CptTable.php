<?php

namespace App\Livewire\Admin\Cpt;

use App\Models\CustomPostType;
use Livewire\Component;
use Livewire\WithPagination;

class CptTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleStatus(int $id)
    {
        $cpt = CustomPostType::findOrFail($id);
        $cpt->update(['is_active' => !$cpt->is_active]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $cpt->is_active ? 'Post type activated.' : 'Post type deactivated.',
        ]);
    }

    public $targetDeleteId = null;
    public $showDeleteModal = false;

    // ... existing property definitions ...

    public function confirmDelete(int $id)
    {
        $this->targetDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->targetDeleteId = null;
    }

    public function performDelete()
    {
        if (!$this->targetDeleteId) {
            return;
        }

        $cpt = CustomPostType::findOrFail($this->targetDeleteId);
        $name = $cpt->plural_label;
        
        // Delete associated meta fields
        $cpt->metaFields()->delete();
        $cpt->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Post type '{$name}' has been deleted.",
        ]);

        $this->showDeleteModal = false;
        $this->targetDeleteId = null;
    }

    public function render()
    {
        $postTypes = CustomPostType::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('singular_label', 'like', '%' . $this->search . '%')
                      ->orWhere('plural_label', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                if ($this->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.cpt.cpt-table', [
            'postTypes' => $postTypes,
        ]);
    }
}
