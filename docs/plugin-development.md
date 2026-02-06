# Plugin Development Guide

This guide outlines best practices and requirements for developing plugins for the Web CMS system.

## Table of Contents
- [Plugin Structure](#plugin-structure)
- [Route Configuration](#route-configuration)
- [Permission System](#permission-system)
- [Menu Registration](#menu-registration)
- [Service Provider Setup](#service-provider-setup)
- [Common Pitfalls](#common-pitfalls)

---

## Plugin Structure

A standard plugin should follow this directory structure:

```
plugins/
└── yourplugin/
    ├── plugin.json                 # Plugin manifest
    ├── routes/
    │   └── web.php                 # Route definitions
    ├── src/
    │   ├── Providers/
    │   │   └── YourPluginServiceProvider.php
    │   ├── Http/
    │   │   └── Controllers/
    │   │       └── YourController.php
    │   └── Models/
    │       └── YourModel.php
    └── resources/
        └── views/
            └── admin/
                └── index.blade.php
```

### plugin.json

```json
{
    "name": "Your Plugin Name",
    "slug": "yourplugin",
    "version": "1.0.0",
    "description": "Plugin description",
    "author": "Your Name",
    "provider": "Plugins\\Yourplugin\\Providers\\YourPluginServiceProvider"
}
```

---

## Route Configuration

### ⚠️ CRITICAL: Always Include 'web' Middleware

**All plugin routes MUST include the `'web'` middleware** to ensure proper route priority and prevent conflicts with the frontend catch-all route.

#### ✅ Correct Route Structure

```php
<?php

use Illuminate\Support\Facades\Route;
use Plugins\Yourplugin\Http\Controllers\YourController;

// Admin Routes
Route::prefix(config('admin.path', 'admin'))
    ->name('admin.')
    ->middleware(['web', 'auth'])  // ✅ Include 'web' middleware!
    ->group(function () {
        
        Route::prefix('yourplugin')
            ->name('yourplugin.')
            ->middleware('permission:yourplugin.view')
            ->group(function () {
                Route::get('/', [YourController::class, 'index'])->name('index');
                Route::get('/create', [YourController::class, 'create'])
                    ->name('create')
                    ->middleware('permission:yourplugin.create');
                // ... more routes
            });
    });
```

#### ❌ Incorrect Route Structure

```php
// Missing 'web' middleware - will cause 302 redirects!
Route::prefix(config('admin.path', 'admin'))
    ->name('admin.')
    ->middleware(['auth'])  // ❌ Missing 'web' middleware
    ->group(function () {
        // Routes here will be intercepted by catch-all route
    });
```

### Why 'web' Middleware is Required

The frontend catch-all route (`/{slug}`) is registered in the `web` middleware group. Routes without `'web'` middleware have lower priority, causing the catch-all route to intercept plugin requests before they reach plugin controllers, resulting in 302 redirects.

---

## Permission System

### Naming Convention

Use the format: `{resource}.{action}`

#### ✅ Correct Permission Names

```php
'events.view'
'events.create'
'events.edit'
'events.delete'
'memberships.view'
'membership_tiers.create'
```

#### ❌ Incorrect Permission Names

```php
'events.events.view'           // ❌ Duplicate resource name
'membership.memberships.view'  // ❌ Inconsistent naming
'event.view'                   // ❌ Singular instead of plural
```

### Creating Permissions

Permissions should be created during plugin activation. Example:

```php
use App\Models\Permission;

$permissions = [
    [
        'name' => 'yourplugin.view',
        'display_name' => 'View Your Plugin',
        'module' => 'yourplugin',
        'plugin_slug' => 'yourplugin',
        'is_active' => true,
    ],
    [
        'name' => 'yourplugin.create',
        'display_name' => 'Create Your Plugin Items',
        'module' => 'yourplugin',
        'plugin_slug' => 'yourplugin',
        'is_active' => true,
    ],
];

foreach ($permissions as $permData) {
    Permission::firstOrCreate(
        ['name' => $permData['name']],
        $permData
    );
}
```

---

## Menu Registration

### Using RenderAdminMenu Event

**Always register menus via the `RenderAdminMenu` event**, NOT by seeding the database.

```php
use Illuminate\Support\Facades\Event;
use App\Events\RenderAdminMenu;

Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
    $event->addMenuItem([
        'title' => 'Your Plugin',
        'route' => 'admin.yourplugin.index',
        'url' => route('admin.yourplugin.index'),
        'icon' => 'extension',  // Material Symbols icon name
        'permission' => 'yourplugin.view',
        'is_active' => true,
        'source' => 'plugin:yourplugin',
        'children' => [
            [
                'title' => 'All Items',
                'route' => 'admin.yourplugin.index',
                'url' => route('admin.yourplugin.index'),
                'icon' => 'list',
                'permission' => 'yourplugin.view',
                'is_active' => true,
                'source' => 'plugin:yourplugin',
                'children' => [],
            ],
            [
                'title' => 'Create New',
                'route' => 'admin.yourplugin.create',
                'url' => route('admin.yourplugin.create'),
                'icon' => 'add_circle',
                'permission' => 'yourplugin.create',
                'is_active' => true,
                'source' => 'plugin:yourplugin',
                'children' => [],
            ],
        ],
    ]);
});
```

### Icon System

The system uses **Material Symbols** icons. Browse available icons at: https://fonts.google.com/icons

Common icons:
- `dashboard` - Dashboard
- `article` - Posts/Content
- `event` - Events
- `people` - Users/Members
- `settings` - Settings
- `list` - List view
- `add_circle` - Create/Add

---

## View Development & UX

### ⚠️ Admin Layout Requirement

**All admin plugin views MUST extend `layouts.admin`:**

```blade
@extends('layouts.admin')

@section('title', 'Your Page Title')
@section('page-title', 'Your Page Title')  {{-- ⬅️ Sets the header title --}}

@section('content')
    {{-- Your content here --}}
@endsection
```

**Available sections:**

| Section | Purpose |
|---------|---------|
| `title` | Browser tab title |
| `page-title` | Header title (replaces "Dashboard" text) |
| `content` | Main page content |
| `hide-header` | Set to `true` to hide header (for create/edit pages) |

> [!CAUTION]
> Do NOT use `admin.layouts.dashboard` or any other layout path. Only `layouts.admin` is supported.

---

### Custom Headers for Create/Edit Pages

For "Create" and "Edit" pages (e.g., creating a new event, post, or form), the system uses a custom sticky header design that replaces the default dashboard header.

**Requirement:** You MUST hide the default dashboard header on these pages to avoid double headers.

#### ✅ Implementation

Add the following directive to your Blade view (usually at the top, below `@extends`):

```blade
@extends('layouts.admin')

@section('title', 'Create New Item')
@section('hide-header', true)  {{-- ⬅️ This is required --}}

@section('content')
    {{-- Your custom sticky header and form content here --}}
@endsection
```

This ensures a clean, consistent full-height layout for complex forms.

---

## Service Provider Setup

### Basic Service Provider Template

```php
<?php

namespace Plugins\Yourplugin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\RenderAdminMenu;

class YourPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings if needed
    }

    public function boot(): void
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        
        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'yourplugin');
        
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register menu items
        Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
            $event->addMenuItem([
                'title' => 'Your Plugin',
                'route' => 'admin.yourplugin.index',
                'url' => route('admin.yourplugin.index'),
                'icon' => 'extension',
                'permission' => 'yourplugin.view',
                'is_active' => true,
                'source' => 'plugin:yourplugin',
                'children' => [],
            ]);
        });
    }
}
```

---

## Common Pitfalls

### 1. ❌ Missing 'web' Middleware
**Symptom:** 302 redirects when accessing plugin pages  
**Solution:** Always include `'web'` in middleware array

### 2. ❌ Incorrect Permission Format
**Symptom:** Permission checks fail even when permission exists  
**Solution:** Use `{resource}.{action}` format, not `{plugin}.{resource}.{action}`

### 3. ❌ Seeding Menus to Database
**Symptom:** Menus don't appear or become outdated  
**Solution:** Use `RenderAdminMenu` event for dynamic menu registration

### 4. ❌ Hardcoded Admin Path
**Symptom:** Routes break when admin path is changed  
**Solution:** Always use `config('admin.path', 'admin')`

### 5. ❌ Not Using Plugin Namespace
**Symptom:** Class not found errors  
**Solution:** Ensure namespace matches: `Plugins\{PluginSlug}\...`

---

## Testing Your Plugin

### 1. Check Routes are Registered

```bash
php artisan route:list --path=ctrlpanel/yourplugin
```

### 2. Verify Menu Appears

Login as admin and check if your plugin menu appears in the sidebar.

### 3. Test Permissions

Create a test role without your plugin permissions and verify access is denied.

### 4. Check Logs

Monitor `storage/logs/laravel.log` for any errors during plugin loading.

---

## Plugin Activation Checklist

- [ ] `plugin.json` is properly configured
- [ ] Service Provider is registered in `plugin.json`
- [ ] Routes include `'web'` middleware
- [ ] Permissions follow `{resource}.{action}` format
- [ ] Menus registered via `RenderAdminMenu` event
- [ ] Views use plugin namespace (e.g., `yourplugin::index`)
- [ ] Migrations are in `database/migrations` directory
- [ ] All routes use `config('admin.path')` for prefix

---

## Example: Complete Minimal Plugin

See the Posts plugin (`plugins/posts`) for a complete working example that follows all best practices.

Key files to reference:
- `plugins/posts/routes/web.php` - Correct route structure
- `plugins/posts/src/Providers/PostsServiceProvider.php` - Menu registration
- `plugins/posts/plugin.json` - Plugin manifest

---

## Need Help?

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Verify routes are registered: `php artisan route:list`
3. Clear caches: `php artisan optimize:clear`
4. Compare with working Posts plugin structure

---

**Last Updated:** 2026-02-01  
**Version:** 1.0
