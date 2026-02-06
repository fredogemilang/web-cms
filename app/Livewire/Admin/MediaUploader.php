<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Services\MediaService;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaUploader extends Component
{
    use WithFileUploads;

    public $isModal = true;
    public $files = [];
    public $uploading = false;
    public $uploadProgress = 0;

    public function mount($isModal = true)
    {
        $this->isModal = $isModal;
    }

    public function save()
    {
        // Check if files exist
        if (empty($this->files)) {
            session()->flash('error', 'Please select files to upload.');
            return;
        }

        if (!auth()->user()->can('media.upload')) {
            session()->flash('error', 'You do not have permission to upload media.');
            return;
        }

        // Validate files
        $this->validate([
            'files.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,zip,rar',
        ]);

        $this->uploading = true;
        $mediaService = app(MediaService::class);
        $uploadedCount = 0;

        try {
            foreach ($this->files as $file) {
                $mediaService->upload($file);
                $uploadedCount++;
            }

            session()->flash('success', "{$uploadedCount} file(s) uploaded successfully.");
            
            // Redirect to media page
            return $this->redirect(route('admin.media.index'), navigate: true);
        } catch (\Exception $e) {
            \Log::error('Media upload error: ' . $e->getMessage());
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
            $this->uploading = false;
            $this->reset(['files', 'uploadProgress']);
        }
    }

    public function removeFile($index)
    {
        array_splice($this->files, $index, 1);
    }

    public function clearAll()
    {
        $this->reset(['files', 'uploading', 'uploadProgress']);
    }

    public function render()
    {
        return view('livewire.admin.media-uploader');
    }
}
