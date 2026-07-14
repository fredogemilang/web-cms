<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Models\User;
use App\Services\MediaUsageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class MediaLibrary extends Component
{
    use WithPagination;

    public $search = '';

    public $filterType = 'all';        // all, images, documents

    public $filterExtension = '';      // specific extension: jpg, png, pdf, ...

    public $filterAltStatus = '';      // empty=any, 'missing', 'has' (images only)

    public $filterUploader = '';       // user id

    public $filterUsage = '';          // empty=any, 'used', 'orphan'

    public $dateFrom = '';

    public $dateTo = '';

    public $sortBy = 'latest';         // latest, oldest, name, size

    public $viewMode = 'grid';         // grid, list

    public $perPage = 24;

    public $showAdvancedFilters = false;

    public $selectedMedia = [];

    public $showDeleteModal = false;

    public $mediaToDelete = null;

    public $showBulkAltModal = false;

    public $bulkAltText = '';

    protected $listeners = [
        'media-uploaded' => '$refresh',
        'media-deleted' => '$refresh',
        'media-updated' => '$refresh',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'filterExtension' => ['except' => ''],
        'filterAltStatus' => ['except' => ''],
        'filterUploader' => ['except' => ''],
        'filterUsage' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'sortBy' => ['except' => 'latest'],
        'viewMode' => ['except' => 'grid'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterExtension(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAltStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUploader(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUsage(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterExtension', 'filterAltStatus', 'filterUploader', 'filterUsage', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search || $this->filterType !== 'all' || $this->filterExtension
            || $this->filterAltStatus || $this->filterUploader || $this->filterUsage
            || $this->dateFrom || $this->dateTo;
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

    /** Toggle: select all on the current page if any unselected; otherwise clear. */
    public function toggleSelectCurrentPage(array $currentPageIds): void
    {
        $unselected = array_diff($currentPageIds, $this->selectedMedia);
        if (! empty($unselected)) {
            $this->selectedMedia = array_values(array_unique(array_merge($this->selectedMedia, $currentPageIds)));
        } else {
            $this->selectedMedia = array_values(array_diff($this->selectedMedia, $currentPageIds));
        }
    }

    public function deselectAll()
    {
        $this->selectedMedia = [];
    }

    // === Bulk: set alt text ===

    public function openBulkAlt(): void
    {
        if (empty($this->selectedMedia)) {
            return;
        }
        $this->bulkAltText = '';
        $this->showBulkAltModal = true;
    }

    public function applyBulkAlt(): void
    {
        if (! auth()->user()?->hasPermission('media.edit')) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'You do not have permission to edit media.']);
            $this->showBulkAltModal = false;

            return;
        }

        $count = Media::whereIn('id', $this->selectedMedia)
            ->where('mime_type', 'like', 'image/%')
            ->update(['alt_text' => $this->bulkAltText]);

        // Invalidate usage cache (alt change doesn't affect usage but content health % does)
        app(MediaUsageService::class)->clearCache();

        $this->showBulkAltModal = false;
        $this->bulkAltText = '';
        $this->selectedMedia = [];

        $this->dispatch('notify', ['type' => 'success', 'message' => "Alt text applied to {$count} image(s)."]);
        $this->dispatch('media-updated');
    }

    // === Bulk: download as ZIP ===

    public function downloadSelectedZip()
    {
        if (empty($this->selectedMedia)) {
            return null;
        }

        $media = Media::whereIn('id', $this->selectedMedia)->get();
        if ($media->isEmpty()) {
            return null;
        }

        $zipName = 'media-export-'.now()->format('Y-m-d-His').'.zip';
        $tmpPath = tempnam(sys_get_temp_dir(), 'mediazip_');

        $zip = new \ZipArchive;
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to create ZIP archive.']);

            return null;
        }

        $disk = Storage::disk('public');
        $usedNames = [];
        foreach ($media as $m) {
            if (! $disk->exists($m->path)) {
                continue;
            }
            // Avoid collisions when two rows share the same original_filename
            $baseName = $m->original_filename ?: basename($m->path);
            $name = $baseName;
            $i = 1;
            while (isset($usedNames[$name])) {
                $name = pathinfo($baseName, PATHINFO_FILENAME)."-{$i}.".pathinfo($baseName, PATHINFO_EXTENSION);
                $i++;
            }
            $usedNames[$name] = true;
            $zip->addFile($disk->path($m->path), $name);
        }
        $zip->close();

        $this->dispatch('notify', ['type' => 'success', 'message' => "Downloading {$media->count()} file(s)."]);

        return response()->download($tmpPath, $zipName)->deleteFileAfterSend();
    }

    public function confirmDelete($mediaId = null)
    {
        $this->mediaToDelete = $mediaId;
        $this->showDeleteModal = true;
    }

    public function deleteSelected()
    {
        if (! auth()->user()->can('media.delete')) {
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
        $query = Media::query()->with('uploader:id,name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('original_filename', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%')
                    ->orWhere('alt_text', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType === 'images') {
            $query->images();
        } elseif ($this->filterType === 'documents') {
            $query->documents();
        }

        if ($this->filterExtension) {
            $query->where('file_extension', strtolower($this->filterExtension));
        }

        if ($this->filterAltStatus === 'missing') {
            $query->where('mime_type', 'like', 'image/%')
                ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''));
        } elseif ($this->filterAltStatus === 'has') {
            $query->where('mime_type', 'like', 'image/%')
                ->whereNotNull('alt_text')->where('alt_text', '!=', '');
        }

        if ($this->filterUploader) {
            $query->where('uploaded_by', $this->filterUploader);
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo.' 23:59:59');
        }

        // Usage filter applied after fetch (see getOrphanIds()) — too dynamic to encode in SQL cleanly
        if ($this->filterUsage === 'orphan') {
            $query->whereIn('id', $this->getOrphanIds());
        } elseif ($this->filterUsage === 'used') {
            $query->whereNotIn('id', $this->getOrphanIds());
        }

        switch ($this->sortBy) {
            case 'oldest': $query->oldest();
                break;
            case 'name':   $query->orderBy('original_filename');
                break;
            case 'size':   $query->orderBy('size', 'desc');
                break;
            default:       $query->latest();
                break;
        }

        return $query;
    }

    protected function getOrphanIds(): array
    {
        return app(MediaUsageService::class)->orphanIds();
    }

    public function render()
    {
        $media = $this->getQuery()->paginate($this->perPage);

        // Distinct extension list for filter dropdown
        $availableExtensions = Media::query()
            ->whereNotNull('file_extension')
            ->where('file_extension', '!=', '')
            ->distinct()
            ->orderBy('file_extension')
            ->pluck('file_extension')
            ->all();

        $uploaders = User::whereIn('id',
            Media::query()->whereNotNull('uploaded_by')->distinct()->pluck('uploaded_by')
        )->orderBy('name')->get(['id', 'name']);

        $usageMap = app(MediaUsageService::class)->usageMap();
        $currentPageIds = $media->getCollection()->pluck('id')->all();

        return view('livewire.admin.media-library', [
            'media' => $media,
            'availableExtensions' => $availableExtensions,
            'uploaders' => $uploaders,
            'usageMap' => $usageMap,
            'currentPageIds' => $currentPageIds,
        ]);
    }
}
