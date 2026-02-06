<?php

namespace App\Livewire\Admin\Cpt\Entries;

use App\Models\CptEntry;
use App\Models\CustomPostType;
use App\Models\CustomTaxonomy;
use Livewire\Component;
use Livewire\WithPagination;

class EntriesTable extends Component
{
    use WithPagination;

    public CustomPostType $postType;
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

    public function mount(CustomPostType $postType)
    {
        $this->postType = $postType;
    }

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

    public array $selectedEntries = [];
    public bool $selectAll = false;

    // ... existing properties ...

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = CptEntry::where('post_type_id', $this->postType->id);
            
            if ($this->status === 'trash') {
                $query->onlyTrashed();
            } elseif ($this->status) {
                $query->where('status', $this->status);
            }

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
                });
            }

            $this->selectedEntries = $query->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedEntries = [];
        }
    }

    public function updatedSelectedEntries()
    {
        $this->selectAll = false;
    }

    public function clearSelection()
    {
        $this->selectedEntries = [];
        $this->selectAll = false;
    }

    public function deleteSelected()
    {
        $count = count($this->selectedEntries);
        
        CptEntry::whereIn('id', $this->selectedEntries)
            ->where('post_type_id', $this->postType->id)
            ->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} items have been moved to trash.",
        ]);

        $this->clearSelection();
    }

    public function restoreSelected()
    {
        $count = count($this->selectedEntries);
        
        CptEntry::withTrashed()
            ->whereIn('id', $this->selectedEntries)
            ->where('post_type_id', $this->postType->id)
            ->restore();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} items have been restored.",
        ]);

        $this->clearSelection();
    }

    public function forceDeleteSelected()
    {
        $count = count($this->selectedEntries);
        
        CptEntry::withTrashed()
            ->whereIn('id', $this->selectedEntries)
            ->where('post_type_id', $this->postType->id)
            ->forceDelete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} items have been permanently deleted.",
        ]);

        $this->clearSelection();
    }

    public function publishSelected()
    {
        $count = count($this->selectedEntries);
        
        CptEntry::whereIn('id', $this->selectedEntries)
            ->where('post_type_id', $this->postType->id)
            ->update(['status' => 'published']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} items have been published.",
        ]);

        $this->clearSelection();
    }

    public function draftSelected()
    {
        $count = count($this->selectedEntries);
        
        CptEntry::whereIn('id', $this->selectedEntries)
            ->where('post_type_id', $this->postType->id)
            ->update(['status' => 'draft']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} items have been moved to draft.",
        ]);

        $this->clearSelection();
    }

    public function delete(int $id)
    {
        $entry = CptEntry::where('post_type_id', $this->postType->id)->findOrFail($id);
        $title = $entry->title;
        $entry->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "'{$title}' has been moved to trash.",
        ]);
    }

    public function restore(int $id)
    {
        $entry = CptEntry::withTrashed()->where('post_type_id', $this->postType->id)->findOrFail($id);
        $entry->restore();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "'{$entry->title}' has been restored.",
        ]);
    }

    public function forceDelete(int $id)
    {
        $entry = CptEntry::withTrashed()->where('post_type_id', $this->postType->id)->findOrFail($id);
        $title = $entry->title;
        $entry->forceDelete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "'{$title}' has been permanently deleted.",
        ]);
    }

    public function getStatusCountsProperty(): array
    {
        $counts = CptEntry::where('post_type_id', $this->postType->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $trashed = CptEntry::where('post_type_id', $this->postType->id)->onlyTrashed()->count();

        return [
            'all' => array_sum($counts),
            'published' => $counts['published'] ?? 0,
            'draft' => $counts['draft'] ?? 0,
            'scheduled' => $counts['scheduled'] ?? 0,
            'archived' => $counts['archived'] ?? 0,
            'trash' => $trashed,
        ];
    }

    public function render()
    {
        $query = CptEntry::where('post_type_id', $this->postType->id)
            ->with(['author']);

        // Handle trash status
        if ($this->status === 'trash') {
            $query->onlyTrashed();
        } elseif ($this->status) {
            $query->where('status', $this->status);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        $entries = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $taxonomies = CustomTaxonomy::active()
            ->forPostType($this->postType->slug)
            ->get();

        return view('livewire.admin.cpt.entries.entries-table', [
            'entries' => $entries,
            'taxonomies' => $taxonomies,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
