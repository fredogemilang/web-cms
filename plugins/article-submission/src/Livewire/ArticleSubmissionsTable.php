<?php

namespace Plugins\ArticleSubmission\Livewire;

use Plugins\ArticleSubmission\Models\ArticleSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleSubmissionsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $selectedSubmissions = [];
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
            $this->sortDirection = $field === 'created_at' ? 'desc' : 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSubmissions = $this->submissions->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedSubmissions = [];
        }
    }

    public function getSubmissionsProperty()
    {
        return ArticleSubmission::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'trashed') {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatusCountsProperty()
    {
        $baseQuery = ArticleSubmission::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            });

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'reviewed' => (clone $baseQuery)->where('status', 'reviewed')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
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
        $this->selectedSubmissions = [];
        $this->selectAll = false;
    }

    public function deleteSubmission($id)
    {
        $submission = ArticleSubmission::find($id);
        
        if ($submission) {
            $submission->delete();
            session()->flash('success', 'Submission deleted successfully.');
        }
        
        $this->selectedSubmissions = array_diff($this->selectedSubmissions, [(string) $id]);
    }

    public function deleteSelected()
    {
        $count = ArticleSubmission::whereIn('id', $this->selectedSubmissions)->delete();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' submission(s) deleted successfully.');
    }

    public function updateStatus($id, $status)
    {
        $submission = ArticleSubmission::find($id);
        
        if ($submission) {
            $submission->update(['status' => $status]);
            session()->flash('success', 'Submission status updated to ' . ucfirst($status) . '.');
        }
    }

    public function updateSelectedStatus($status)
    {
        $count = ArticleSubmission::whereIn('id', $this->selectedSubmissions)
            ->update(['status' => $status]);
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' submission(s) marked as ' . ucfirst($status) . '.');
    }

    public function restore($id)
    {
        $submission = ArticleSubmission::onlyTrashed()->find($id);
        
        if ($submission) {
            $submission->restore();
            session()->flash('success', 'Submission restored successfully.');
        }
    }

    public function forceDelete($id)
    {
        $submission = ArticleSubmission::onlyTrashed()->find($id);
        
        if ($submission) {
            // Delete the file if exists
            if ($submission->article_file && \Storage::disk('public')->exists($submission->article_file)) {
                \Storage::disk('public')->delete($submission->article_file);
            }
            $submission->forceDelete();
            session()->flash('success', 'Submission deleted permanently.');
        }
    }

    public function restoreSelected()
    {
        $count = ArticleSubmission::onlyTrashed()->whereIn('id', $this->selectedSubmissions)->restore();
        
        $this->clearSelection();
        
        session()->flash('success', $count . ' submission(s) restored successfully.');
    }

    public function forceDeleteSelected()
    {
        $submissions = ArticleSubmission::onlyTrashed()->whereIn('id', $this->selectedSubmissions)->get();
        
        foreach ($submissions as $submission) {
            if ($submission->article_file && \Storage::disk('public')->exists($submission->article_file)) {
                \Storage::disk('public')->delete($submission->article_file);
            }
            $submission->forceDelete();
        }
        
        $this->clearSelection();
        
        session()->flash('success', count($submissions) . ' submission(s) deleted permanently.');
    }

    public function render()
    {
        return view('article-submission::livewire.submissions-table', [
            'submissions' => $this->submissions,
            'statusCounts' => $this->statusCounts,
        ]);
    }
}
