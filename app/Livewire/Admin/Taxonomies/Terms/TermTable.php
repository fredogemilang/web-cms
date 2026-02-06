<?php

namespace App\Livewire\Admin\Taxonomies\Terms;

use App\Models\CustomTaxonomy;
use App\Models\TaxonomyTerm;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class TermTable extends Component
{
    use WithPagination;

    public CustomTaxonomy $taxonomy;
    
    public string $search = '';

    #[On('term-saved')]
    public function refreshList()
    {
        // specific logic if needed, otherwise just re-render is automatic on event call if mapped?
        // Actually, just calling a method triggers re-render.
        $this->resetPage();
    }
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(CustomTaxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function updatingSearch()
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

    public $targetDeleteId = null;
    public $showDeleteModal = false;

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

        $term = TaxonomyTerm::findOrFail($this->targetDeleteId);
        $name = $term->name;
        
        $term->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Term '{$name}' has been deleted.",
        ]);

        $this->showDeleteModal = false;
        $this->targetDeleteId = null;
    }

    public function render()
    {
        $query = TaxonomyTerm::query()
            ->where('taxonomy_id', $this->taxonomy->id)
            ->withCount('entries');

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // If taxonomy is flat or searching, or sorting by a non-name field, 
        // fallback to standard pagination (flat list)
        if (!$this->taxonomy->is_hierarchical || $this->search || $this->sortField !== 'name') {
            $terms = $query->orderBy($this->sortField, $this->sortDirection)
                          ->paginate($this->perPage);
            
            // For searches on hierarchical taxonomies, we might want to flag they are not hierarchical view
            foreach($terms as $term) {
                $term->depth = 0;
            }
        } else {
            // Get all terms to build hierarchy (Small to medium taxonomies)
            // For very large ones, this needs a different approach (like parent_id filtering)
            $allTerms = $query->orderBy('name', $this->sortDirection)->get();
            $hierarchicalTerms = $this->flattenTerms($allTerms);
            
            // Manual pagination for the hierarchical collection
            $currentPage = $this->getPage();
            $terms = new \Illuminate\Pagination\LengthAwarePaginator(
                $hierarchicalTerms->forPage($currentPage, $this->perPage),
                $hierarchicalTerms->count(),
                $this->perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        }

        return view('livewire.admin.taxonomies.terms.term-table', [
            'terms' => $terms,
        ]);
    }

    private function flattenTerms($allTerms, $parentId = null, $depth = 0)
    {
        $result = collect();
        $items = $allTerms->where('parent_id', $parentId);

        foreach ($items as $item) {
            $item->depth = $depth;
            $result->push($item);
            $result = $result->merge($this->flattenTerms($allTerms, $item->id, $depth + 1));
        }

        return $result;
    }
}
