<?php

namespace App\Livewire\Admin;

use App\Models\Form;
use Livewire\Component;
use Livewire\WithPagination;

class FormsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $selectedForms = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
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
            $this->sortDirection = in_array($field, ['created_at']) ? 'desc' : 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedForms = $this->forms->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedForms = [];
        }
    }

    public function getFormsProperty()
    {
        return Form::query()
            ->withCount('entries')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'trashed') {
                    $query->onlyTrashed();
                } else {
                    $query->where('is_active', $this->statusFilter === 'active');
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatusCountsProperty()
    {
        $baseQuery = Form::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            });

        return [
            'all' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
            'trashed' => (clone $baseQuery)->onlyTrashed()->count(),
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedForms = [];
        $this->selectAll = false;
    }

    public function deleteForm($formId)
    {
        $form = Form::find($formId);
        
        if ($form) {
            $form->delete();
            session()->flash('success', 'Form deleted successfully.');
        }
        
        $this->selectedForms = array_diff($this->selectedForms, [(string) $formId]);
    }

    public function deleteSelected()
    {
        $count = Form::whereIn('id', $this->selectedForms)->delete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' form(s) deleted successfully.');
    }

    public function toggleStatus($formId)
    {
        $form = Form::find($formId);
        
        if ($form) {
            $form->update(['is_active' => !$form->is_active]);
            session()->flash('success', 'Form status updated successfully.');
        }
    }

    public function activateSelected()
    {
        $count = Form::whereIn('id', $this->selectedForms)
            ->update(['is_active' => true]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' form(s) activated successfully.');
    }

    public function deactivateSelected()
    {
        $count = Form::whereIn('id', $this->selectedForms)
            ->update(['is_active' => false]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' form(s) deactivated successfully.');
    }

    public function restore($formId)
    {
        $form = Form::onlyTrashed()->find($formId);
        
        if ($form) {
            $form->restore();
            session()->flash('success', 'Form restored successfully.');
        }
    }

    public function forceDelete($formId)
    {
        $form = Form::onlyTrashed()->find($formId);
        
        if ($form) {
            $form->forceDelete();
            session()->flash('success', 'Form deleted permanently.');
        }
    }

    public function restoreSelected()
    {
        $count = Form::onlyTrashed()->whereIn('id', $this->selectedForms)->restore();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' form(s) restored successfully.');
    }

    public function forceDeleteSelected()
    {
        $count = Form::onlyTrashed()->whereIn('id', $this->selectedForms)->forceDelete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' form(s) deleted permanently.');
    }

    public function render()
    {
        return view('livewire.admin.forms-table', [
            'forms' => $this->forms,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
