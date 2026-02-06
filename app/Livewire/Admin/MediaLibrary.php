<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use Livewire\Component;
use Livewire\WithPagination;

class MediaLibrary extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all'; // all, images, documents
    public $sortBy = 'latest'; // latest, oldest, name, size
    public $viewMode = 'grid'; // grid, list
    public $perPage = 24;
    public $selectedMedia = [];
    public $showDeleteModal = false;
    public $mediaToDelete = null;

    protected $listeners = [
        'media-uploaded' => '$refresh',
        'media-deleted' => '$refresh',
        'media-updated' => '$refresh',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'sortBy' => ['except' => 'latest'],
        'viewMode' => ['except' => 'grid'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function selectMedia($mediaId)
    {
        if (in_array($mediaId, $this->selectedMedia)) {
            $this->selectedMedia = array_diff($this->selectedMedia, [$mediaId]);
        } else {
            $this->selectedMedia[] = $mediaId;
        }
    }

    public function selectAll()
    {
        $mediaIds = $this->getQuery()->pluck('id')->toArray();
        $this->selectedMedia = $mediaIds;
    }

    public function deselectAll()
    {
        $this->selectedMedia = [];
    }

    public function confirmDelete($mediaId = null)
    {
        $this->mediaToDelete = $mediaId;
        $this->showDeleteModal = true;
    }

    public function deleteSelected()
    {
        if (!auth()->user()->can('media.delete')) {
            session()->flash('error', 'You do not have permission to delete media.');
            return;
        }

        $count = 0;
        $mediaIds = $this->mediaToDelete ? [$this->mediaToDelete] : $this->selectedMedia;

        foreach ($mediaIds as $id) {
            $media = Media::find($id);
            if ($media) {
                $media->delete();
                $count++;
            }
        }

        $this->selectedMedia = [];
        $this->showDeleteModal = false;
        $this->mediaToDelete = null;

        session()->flash('success', "{$count} media file(s) deleted successfully.");
        $this->dispatch('media-deleted');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->mediaToDelete = null;
    }

    protected function getQuery()
    {
        $query = Media::query()->with('uploader');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('original_filename', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply type filter
        if ($this->filterType === 'images') {
            $query->images();
        } elseif ($this->filterType === 'documents') {
            $query->documents();
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name':
                $query->orderBy('original_filename');
                break;
            case 'size':
                $query->orderBy('size', 'desc');
                break;
            default: // latest
                $query->latest();
                break;
        }

        return $query;
    }

    public function render()
    {
        $media = $this->getQuery()->paginate($this->perPage);

        return view('livewire.admin.media-library', [
            'media' => $media,
        ]);
    }
}
