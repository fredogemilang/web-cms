<?php

namespace App\Livewire\Admin\Taxonomies;

use App\Models\CustomTaxonomy;
use Livewire\Component;
use Livewire\WithPagination;

class TaxonomyTable extends Component
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
        $taxonomy = CustomTaxonomy::findOrFail($id);
        $taxonomy->update(['is_active' => !$taxonomy->is_active]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $taxonomy->is_active ? 'Taxonomy activated.' : 'Taxonomy deactivated.',
        ]);
    }

    public function delete(int $id)
    {
        $taxonomy = CustomTaxonomy::findOrFail($id);
        $name = $taxonomy->plural_label;
        
        // Delete associated meta fields
        $taxonomy->metaFields()->delete();
        $taxonomy->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Taxonomy '{$name}' has been deleted.",
        ]);
    }

    public function render()
    {
        $taxonomies = CustomTaxonomy::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('singular_label', 'like', '%' . $this->search . '%')
                      ->orWhere('plural_label', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.taxonomies.taxonomy-table', [
            'taxonomies' => $taxonomies,
        ]);
    }
}
