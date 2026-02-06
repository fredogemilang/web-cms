<?php

namespace App\Livewire\Admin\Themes;

use App\Models\Theme;
use App\Services\ThemeManager as ThemeManagerService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class ThemeManager extends Component
{
    use WithFileUploads;

    public $themes;
    public $activeTheme;
    public $themeZip;
    public $uploading = false;
    public $uploadProgress = 0;

    // Confirmation modals
    public $showDeleteModal = false;
    public $themeToDelete = null;

    protected $listeners = ['refreshThemes' => '$refresh'];

    public function mount()
    {
        $this->loadThemes();
    }

    public function loadThemes()
    {
        $this->themes = Theme::orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $this->activeTheme = Theme::active()->first();
    }

    public function activateTheme($themeId)
    {
        try {
            $theme = Theme::findOrFail($themeId);
            $themeManager = app(ThemeManagerService::class);
            $themeManager->activate($theme);

            $this->loadThemes();
            $this->dispatch('notify', type: 'success', message: "Theme '{$theme->name}' activated successfully!");

            // Refresh page to apply new theme
            return redirect()->route('admin.themes.index');
        } catch (\Exception $e) {
            Log::error("Theme activation failed: " . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Failed to activate theme: ' . $e->getMessage());
        }
    }

    public function confirmDelete($themeId)
    {
        $theme = Theme::find($themeId);

        if ($theme && $theme->is_active) {
            $this->dispatch('notify', type: 'error', message: 'Cannot delete the active theme. Please activate a different theme first.');
            return;
        }

        $this->themeToDelete = $theme;
        $this->showDeleteModal = true;
    }

    public function deleteTheme()
    {
        try {
            if (!$this->themeToDelete) {
                throw new \Exception('No theme selected for deletion.');
            }

            if ($this->themeToDelete->is_active) {
                throw new \Exception('Cannot delete the active theme.');
            }

            $themeName = $this->themeToDelete->name;
            $themeManager = app(ThemeManagerService::class);
            $themeManager->delete($this->themeToDelete);

            $this->loadThemes();
            $this->showDeleteModal = false;
            $this->themeToDelete = null;

            $this->dispatch('notify', type: 'success', message: "Theme '{$themeName}' deleted successfully.");
        } catch (\Exception $e) {
            Log::error("Theme deletion failed: " . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Failed to delete theme: ' . $e->getMessage());
            $this->showDeleteModal = false;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->themeToDelete = null;
    }

    public function uploadTheme()
    {
        $this->validate([
            'themeZip' => 'required|file|mimes:zip|max:10240', // 10MB
        ]);

        try {
            $this->uploading = true;
            $themeManager = app(ThemeManagerService::class);
            $theme = $themeManager->install($this->themeZip->getRealPath());

            $this->loadThemes();
            $this->themeZip = null;
            $this->uploading = false;

            $this->dispatch('notify', type: 'success', message: "Theme '{$theme->name}' installed successfully!");
        } catch (\Exception $e) {
            Log::error("Theme upload failed: " . $e->getMessage());
            $this->uploading = false;
            $this->dispatch('notify', type: 'error', message: 'Failed to install theme: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.themes.theme-manager');
    }
}
