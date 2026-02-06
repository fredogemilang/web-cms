<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Livewire\Component;
use Livewire\WithPagination;

class PagesTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public array $selectedPages = [];
    public bool $selectAll = false;

    public bool $showDeleteModal = false;
    public ?int $pageToDelete = null;
    public bool $showBulkDeleteModal = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPages = $this->getPageIds();
        } else {
            $this->selectedPages = [];
        }
    }

    protected function getPageIds(): array
    {
        return $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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

    public function setStatus(string $status)
    {
        $this->status = $status;
        $this->resetPage();
    }

    protected function buildQuery()
    {
        $query = Page::with(['author']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    // === DELETE OPERATIONS ===

    public function confirmDelete(int $id)
    {
        $this->pageToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->pageToDelete = null;
    }

    public function delete()
    {
        if ($this->pageToDelete) {
            $page = Page::find($this->pageToDelete);
            if ($page) {
                $page->delete();
                $this->dispatch('notify', type: 'success', message: 'Page moved to trash!');
            }
        }

        $this->cancelDelete();
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedPages) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    public function cancelBulkDelete()
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete()
    {
        Page::whereIn('id', $this->selectedPages)->delete();
        $this->selectedPages = [];
        $this->selectAll = false;
        $this->showBulkDeleteModal = false;
        $this->dispatch('notify', type: 'success', message: 'Selected pages moved to trash!');
    }

    // === BULK ACTIONS ===

    public function bulkPublish()
    {
        if (count($this->selectedPages) > 0) {
            Page::whereIn('id', $this->selectedPages)->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            $this->selectedPages = [];
            $this->selectAll = false;
            $this->dispatch('notify', type: 'success', message: 'Selected pages published!');
        }
    }

    public function bulkDraft()
    {
        if (count($this->selectedPages) > 0) {
            Page::whereIn('id', $this->selectedPages)->update(['status' => 'draft']);
            $this->selectedPages = [];
            $this->selectAll = false;
            $this->dispatch('notify', type: 'success', message: 'Selected pages set to draft!');
        }
    }

    // === QUICK ACTIONS ===

    public function quickPublish(int $id)
    {
        $page = Page::find($id);
        if ($page) {
            $page->update([
                'status' => 'published',
                'published_at' => $page->published_at ?? now(),
            ]);
            $this->dispatch('notify', type: 'success', message: 'Page published!');
        }
    }

    public function quickDraft(int $id)
    {
        $page = Page::find($id);
        if ($page) {
            $page->update(['status' => 'draft']);
            $this->dispatch('notify', type: 'success', message: 'Page set to draft!');
        }
    }

    public function duplicate(int $id)
    {
        $page = Page::with('allBlocks')->find($id);
        if ($page) {
            // Create duplicate page
            $newPage = $page->replicate();
            $newPage->title = $page->title . ' (Copy)';
            $newPage->slug = $page->slug . '-copy';
            $newPage->status = 'draft';
            $newPage->published_at = null;
            $newPage->save();

            // Duplicate blocks
            foreach ($page->blocks as $block) {
                $newBlock = $block->replicate();
                $newBlock->page_id = $newPage->id;
                $newBlock->save();

                // Duplicate child blocks for repeaters
                foreach ($block->childBlocks as $childBlock) {
                    $newChildBlock = $childBlock->replicate();
                    $newChildBlock->page_id = $newPage->id;
                    $newChildBlock->parent_block_id = $newBlock->id;
                    $newChildBlock->save();
                }
            }

            $this->dispatch('notify', type: 'success', message: 'Page duplicated!');
        }
    }

    public function render()
    {
        $pages = $this->buildQuery()->paginate($this->perPage);

        $statusCounts = [
            'all' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'scheduled' => Page::where('status', 'scheduled')->count(),
            'private' => Page::where('status', 'private')->count(),
            'trash' => Page::onlyTrashed()->count(),
        ];

        return view('livewire.admin.pages.pages-table', compact('pages', 'statusCounts'));
    }
}
