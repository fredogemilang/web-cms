<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThemesController extends Controller
{
    protected ThemeManager $themeManager;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Display a listing of themes.
     */
    public function index()
    {
        $themes = Theme::orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('admin.themes.index', compact('themes'));
    }

    /**
     * Upload and install a new theme.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'theme_zip' => ['required', 'file', 'mimes:zip', 'max:10240'], // 10MB max
        ]);

        try {
            $path = $request->file('theme_zip')->getRealPath();
            $theme = $this->themeManager->install($path);

            return redirect()->route('admin.themes.index')
                ->with('success', "Theme '{$theme->name}' installed successfully.");
        } catch (\Exception $e) {
            Log::error("Theme install failed: " . $e->getMessage());
            return back()->with('error', 'Failed to install theme: ' . $e->getMessage());
        }
    }

    /**
     * Activate a theme.
     */
    public function activate(Theme $theme)
    {
        try {
            $this->themeManager->activate($theme);

            return back()->with('success', "Theme '{$theme->name}' activated successfully. Frontend will now use this theme.");
        } catch (\Exception $e) {
            Log::error("Theme activation failed: " . $e->getMessage());
            return back()->with('error', 'Failed to activate theme: ' . $e->getMessage());
        }
    }

    /**
     * Delete a theme.
     */
    public function destroy(Theme $theme)
    {
        try {
            // Prevent deleting active theme
            if ($theme->is_active) {
                return back()->with('error', 'Cannot delete the active theme. Please activate a different theme first.');
            }

            $themeName = $theme->name;
            $this->themeManager->delete($theme);

            return back()->with('success', "Theme '{$themeName}' deleted successfully.");
        } catch (\Exception $e) {
            Log::error("Theme deletion failed: " . $e->getMessage());
            return back()->with('error', 'Failed to delete theme: ' . $e->getMessage());
        }
    }
}
