<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PluginController extends Controller
{
    protected PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Display a listing of the plugins.
     */
    public function index()
    {
        $plugins = Plugin::orderBy('name')->get();
        return view('admin.plugins.index', compact('plugins'));
    }

    /**
     * Store a newly created plugin (Upload).
     */
    public function store(Request $request)
    {
        $request->validate([
            'plugin_zip' => ['required', 'file', 'mimes:zip', 'max:10240'], // 10MB max
        ]);

        try {
            $path = $request->file('plugin_zip')->getRealPath();
            $plugin = $this->pluginManager->install($path);

            return redirect()->route('admin.plugins.index')
                ->with('success', "Plugin '{$plugin->name}' installed successfully.");
        } catch (\Exception $e) {
            Log::error("Plugin install failed: " . $e->getMessage());
            return back()->with('error', 'Failed to install plugin: ' . $e->getMessage());
        }
    }

    /**
     * Activate a plugin.
     */
    public function activate(Plugin $plugin)
    {
        try {
            $this->pluginManager->activate($plugin);
            return back()->with('success', "Plugin '{$plugin->name}' activated successfully.");
        } catch (\Exception $e) {
            Log::error("Plugin activation failed: " . $e->getMessage());
            return back()->with('error', 'Failed to activate plugin: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a plugin.
     */
    public function deactivate(Plugin $plugin)
    {
        try {
            $this->pluginManager->deactivate($plugin);
            return back()->with('success', "Plugin '{$plugin->name}' deactivated successfully.");
        } catch (\Exception $e) {
            Log::error("Plugin deactivation failed: " . $e->getMessage());
            return back()->with('error', 'Failed to deactivate plugin: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified plugin.
     */
    public function destroy(Request $request, Plugin $plugin)
    {
        try {
            $deleteData = $request->boolean('delete_data', false);
            $this->pluginManager->uninstall($plugin, $deleteData);
            
            $message = $deleteData 
                ? "Plugin '{$plugin->name}' uninstalled and all data deleted."
                : "Plugin '{$plugin->name}' uninstalled. Permissions retained.";
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Plugin uninstall failed: " . $e->getMessage());
            return back()->with('error', 'Failed to uninstall plugin: ' . $e->getMessage());
        }
    }
}
