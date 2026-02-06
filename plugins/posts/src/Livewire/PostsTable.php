<?php

namespace Plugins\Posts\Livewire;

use Plugins\Posts\Models\Post;
use Plugins\Posts\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class PostsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $selectedPosts = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
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

    public function updatingCategoryFilter()
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
            $this->sortDirection = in_array($field, ['created_at', 'published_at', 'views_count']) ? 'desc' : 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPosts = $this->posts->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedPosts = [];
        }
    }

    public function getPostsProperty()
    {
        return Post::query()
            ->with(['author', 'categories'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('excerpt', 'like', '%' . $this->search . '%');
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
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', $this->categoryFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function getStatusCountsProperty()
    {
        $baseQuery = Post::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('excerpt', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', $this->categoryFilter);
                });
            });

        return [
            'all' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'scheduled' => (clone $baseQuery)->where('status', 'scheduled')->count(),
            'archived' => (clone $baseQuery)->where('status', 'archived')->count(),
            'trashed' => (clone $baseQuery)->onlyTrashed()->count(),
        ];
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->categoryFilter = '';
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedPosts = [];
        $this->selectAll = false;
    }

    public function deletePost($postId)
    {
        $post = Post::find($postId);
        
        if ($post) {
            $post->delete();
            session()->flash('success', 'Post deleted successfully.');
        }
        
        $this->selectedPosts = array_diff($this->selectedPosts, [(string) $postId]);
    }

    public function deleteSelected()
    {
        $count = Post::whereIn('id', $this->selectedPosts)->delete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' post(s) deleted successfully.');
    }

    public function publishSelected()
    {
        $count = Post::whereIn('id', $this->selectedPosts)
            ->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' post(s) published successfully.');
    }

    public function draftSelected()
    {
        $count = Post::whereIn('id', $this->selectedPosts)
            ->update(['status' => 'draft']);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' post(s) moved to draft.');
    }

    public function restore($postId)
    {
        $post = Post::onlyTrashed()->find($postId);
        
        if ($post) {
            $post->restore();
            session()->flash('success', 'Post restored successfully.');
        }
    }

    public function forceDelete($postId)
    {
        $post = Post::onlyTrashed()->find($postId);
        
        if ($post) {
            $post->forceDelete();
            session()->flash('success', 'Post deleted permanently.');
        }
    }

    public function restoreSelected()
    {
        $count = Post::onlyTrashed()->whereIn('id', $this->selectedPosts)->restore();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' post(s) restored successfully.');
    }

    public function forceDeleteSelected()
    {
        $count = Post::onlyTrashed()->whereIn('id', $this->selectedPosts)->forceDelete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' post(s) deleted permanently.');
    }

    public function render()
    {
        return view('posts::livewire.posts-table', [
            'posts' => $this->posts,
            'categories' => $this->categories,
            'statusCounts' => $this->statusCounts,
            'archiveSlug' => \Plugins\Posts\Models\Setting::get('archive_slug', 'blog'),
        ]);
    }
}
