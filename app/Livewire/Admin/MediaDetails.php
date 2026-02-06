<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Services\MediaService;
use Livewire\Component;

class MediaDetails extends Component
{
    public $mediaId;
    public $media;
    public $alt_text;
    public $title;
    public $description;
    public $showModal = false;

    protected $rules = [
        'alt_text' => 'nullable|string|max:255',
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    protected $listeners = ['openMediaDetails'];

    public function openMediaDetails($mediaId)
    {
        $this->mediaId = $mediaId;
        $this->media = Media::with('uploader')->find($mediaId);

        if ($this->media) {
            $this->alt_text = $this->media->alt_text;
            $this->title = $this->media->title;
            $this->description = $this->media->description;
            $this->showModal = true;
        }
    }

    public function save()
    {
        if (!auth()->user()->can('media.edit')) {
            session()->flash('error', 'You do not have permission to edit media.');
            return;
        }

        $this->validate();

        try {
            $mediaService = app(MediaService::class);
            $mediaService->updateMetadata($this->media, [
                'alt_text' => $this->alt_text,
                'title' => $this->title,
                'description' => $this->description,
            ]);

            session()->flash('success', 'Media updated successfully.');
            $this->dispatch('media-updated');
            
            // Dispatch event to close modal with animation
            $this->dispatch('closeMediaDetails');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!auth()->user()->can('media.delete')) {
            session()->flash('error', 'You do not have permission to delete media.');
            return;
        }

        try {
            $this->media->delete();
            session()->flash('success', 'Media deleted successfully.');
            $this->dispatch('media-deleted');
            
            // Dispatch event to close modal with animation
            $this->dispatch('closeMediaDetails');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function copyUrl()
    {
        $this->dispatch('copy-to-clipboard', url: $this->media->url);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['mediaId', 'media', 'alt_text', 'title', 'description']);
    }

    public function render()
    {
        return view('livewire.admin.media-details');
    }
}
