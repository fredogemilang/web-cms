<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Services\MediaService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TiptapMediaPicker extends Component
{
    use WithFileUploads, WithPagination;
    
    // Modal state
    public bool $showModal = false;
    public string $activeTab = 'library';
    
    // Library state
    public string $search = '';
    public string $filterType = 'images';
    public ?int $selectedMediaId = null;
    public ?array $selectedMedia = null;
    
    // Upload state
    public $uploadFile = null;
    public bool $uploading = false;

    protected $listeners = ['openTiptapMediaPicker' => 'openModal'];

    public function openModal()
    {
        $this->showModal = true;
        $this->activeTab = 'library';
        $this->search = '';
        $this->selectedMediaId = null;
        $this->selectedMedia = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['uploadFile', 'uploading', 'selectedMediaId', 'selectedMedia']);
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
                'alt_text' => $media->alt_text,
            ];
        }
    }

    public function confirmSelection()
    {
        if ($this->selectedMedia) {
            $imageUrl = $this->selectedMedia['webp_url'] ?? $this->selectedMedia['url'];
            $altText = $this->selectedMedia['alt_text'] ?? $this->selectedMedia['original_filename'];
            
            // Dispatch browser event for TipTap to listen
            $this->dispatch('tiptap-media-selected', 
                url: $imageUrl,
                alt: $altText
            );
        }
        $this->closeModal();
    }

    public function uploadAndSelect()
    {
        if (!$this->uploadFile) {
            session()->flash('tiptap-picker-error', 'Please select a file to upload.');
            return;
        }

        if (!auth()->user()->can('media.upload')) {
            session()->flash('tiptap-picker-error', 'You do not have permission to upload media.');
            return;
        }

        $this->validate([
            'uploadFile' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg',
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
                'alt_text' => $media->alt_text,
            ];

            // Switch to library tab to show selection
            $this->activeTab = 'library';
            $this->reset(['uploadFile', 'uploading']);
            
            session()->flash('tiptap-picker-success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('TipTap media picker upload error: ' . $e->getMessage());
            session()->flash('tiptap-picker-error', 'Upload failed: ' . $e->getMessage());
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

        // Only show images for TipTap
        $query->images();

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
        return view('livewire.admin.tiptap-media-picker', [
            'mediaItems' => $this->media,
        ]);
    }
}
