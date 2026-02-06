<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Services\MediaService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class MediaPicker extends Component
{
    use WithFileUploads, WithPagination;

    // Configuration
    public string $field = '';
    public ?string $value = null;
    public string $label = 'Select Media';
    public bool $multiple = false;
    public string $accept = 'image/*';
    public bool $shouldClearAfterSelection = false;
    public bool $compact = false;
    
    // Modal state
    public bool $showModal = false;
    public string $activeTab = 'library'; // 'library' or 'upload'
    
    // Library state
    public string $search = '';
    public string $filterType = 'images';
    public ?int $selectedMediaId = null;
    public ?array $selectedMedia = null;
    
    // Upload state
    public $uploadFile = null;
    public bool $uploading = false;

    public function mount(string $field, ?string $value = null, string $label = 'Select Media', bool $multiple = false, string $accept = 'image/*', bool $shouldClearAfterSelection = false, bool $compact = false)
    {
        $this->field = $field;
        $this->value = $value;
        $this->label = $label;
        $this->multiple = $multiple;
        $this->accept = $accept;
        $this->shouldClearAfterSelection = $shouldClearAfterSelection;
        $this->compact = $compact;
        
        // Load existing media if value is set (check both path and webp_path)
        if ($this->value) {
            $media = Media::where('path', $this->value)
                ->orWhere('webp_path', $this->value)
                ->first();
            if ($media) {
                $this->selectedMediaId = $media->id;
                $this->selectedMedia = [
                    'id' => $media->id,
                    'path' => $media->path,
                    'webp_path' => $media->webp_path,
                    'url' => $media->url,
                    'webp_url' => $media->webp_url,
                    'original_filename' => $media->original_filename,
                ];
            }
        }
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->activeTab = 'library';
        $this->search = '';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['uploadFile', 'uploading']);
    }

    public function selectMedia(int $mediaId)
    {
        $media = Media::find($mediaId);
        if ($media) {
            $this->selectedMediaId = $mediaId;
            $this->selectedMedia = [
                'id' => $media->id,
                'path' => $media->path,
                'webp_path' => $media->webp_path,
                'url' => $media->url,
                'webp_url' => $media->webp_url,
                'original_filename' => $media->original_filename,
            ];
        }
    }

    public function confirmSelection()
    {
        if ($this->selectedMedia) {
            // Prioritize WebP path if available
            $mediaPath = $this->selectedMedia['webp_path'] ?? $this->selectedMedia['path'];
            $mediaUrl = $this->selectedMedia['webp_url'] ?? $this->selectedMedia['url'];
            
            $this->dispatch('media-selected', 
                field: $this->field,
                mediaId: $this->selectedMedia['id'],
                mediaPath: $mediaPath,
                mediaUrl: $mediaUrl
            );
            
            if (!$this->shouldClearAfterSelection) {
                $this->value = $mediaPath;
            } else {
                $this->value = null;
                $this->selectedMedia = null;
                $this->selectedMediaId = null;
            }
        }
        $this->closeModal();
    }

    public function removeMedia()
    {
        $this->selectedMediaId = null;
        $this->selectedMedia = null;
        $this->value = null;
        $this->dispatch('media-removed', field: $this->field);
    }

    public function uploadAndSelect()
    {
        if (!$this->uploadFile) {
            session()->flash('picker-error', 'Please select a file to upload.');
            return;
        }

        if (!auth()->user()->can('media.upload')) {
            session()->flash('picker-error', 'You do not have permission to upload media.');
            return;
        }

        $this->validate([
            'uploadFile' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip,rar',
        ]);

        $this->uploading = true;

        try {
            $mediaService = app(MediaService::class);
            $media = $mediaService->upload($this->uploadFile);

            // Auto-select the uploaded media
            $this->selectedMediaId = $media->id;
            $this->selectedMedia = [
                'id' => $media->id,
                'path' => $media->path,
                'webp_path' => $media->webp_path,
                'url' => $media->url,
                'webp_url' => $media->webp_url,
                'original_filename' => $media->original_filename,
            ];

            // Switch to library tab to show selection
            $this->activeTab = 'library';
            $this->reset(['uploadFile', 'uploading']);
            
            session()->flash('picker-success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Media picker upload error: ' . $e->getMessage());
            session()->flash('picker-error', 'Upload failed: ' . $e->getMessage());
            $this->uploading = false;
        }
    }

    public function clearUpload()
    {
        $this->reset(['uploadFile', 'uploading']);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getMediaProperty()
    {
        $query = Media::query();

        // Apply type filter
        if ($this->filterType === 'images') {
            $query->images();
        } elseif ($this->filterType === 'documents') {
            $query->documents();
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('original_filename', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('alt_text', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(18);
    }

    public function render()
    {
        return view('livewire.admin.media-picker', [
            'mediaItems' => $this->media,
        ]);
    }
}
