# Plugin Development Guide

This guide outlines best practices and requirements for developing plugins for the Web CMS system.

## Table of Contents
- [Plugin Structure](#plugin-structure)
- [Route Configuration](#route-configuration)
- [Permission System](#permission-system)
- [Menu Registration](#menu-registration)
- [Permalink Slug Pattern](#permalink-slug-pattern)
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

## Permalink Slug Pattern

All content forms that have a permalink slug (posts, pages, events, etc.) **MUST** follow this standardized pattern for both UI design and backend behavior.

### UI Design: Click-to-Edit Permalink

The slug is displayed inline below the title as a `PERMALINK:` label with a click-to-edit badge. Do **NOT** use a regular text input or a split input with URL prefix box.

#### ✅ Correct Slug UI (Blade)

```blade
{{-- Permalink Slug --}}
@if($slug)
<div class="flex items-center gap-2 text-xs font-bold text-[#6F767E] uppercase tracking-wider pl-1">
    <span>PERMALINK:</span>
    <span class="text-[#6F767E] lowercase font-normal">{{ url('/your-base') }}/</span>
    <div x-data="{ editing: false }" class="relative flex items-center gap-2">
        <span x-show="!editing" class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#272B30]">{{ $slug }}</span>
        <input x-show="editing" wire:model.blur="slug"
            @blur="editing = false" @keydown.enter="editing = false"
            type="text"
            class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#2563EB] focus:outline-none w-auto min-w-[100px]"
            x-cloak>
        <button @click="editing = !editing; $nextTick(() => $el.previousElementSibling.focus())"
            class="text-[#6F767E] hover:text-[#FCFCFC] transition-colors">
            <span class="material-symbols-outlined text-[14px]">edit</span>
        </button>
    </div>
</div>
@endif
@error('slug')
    <p class="text-red-500 text-sm">{{ $message }}</p>
@enderror
```

**Key behaviors:**
- Slug is **hidden until generated** (wrapped in `@if($slug)`)
- Default state shows slug as a **dark badge** (read-only display)
- Clicking the **edit pencil icon** toggles to an inline input
- Uses `wire:model.blur` — commits only when user leaves the field
- `@keydown.enter` also commits and closes editing mode

> [!IMPORTANT]
> For dark-mode themed admin pages (e.g., event console), replace the hardcoded colors with theme variables:
> `bg-dark-surface`, `text-text-primary`, `border-dark-border`, etc.

#### ❌ Incorrect Slug UI

```blade
{{-- ❌ Don't use a regular full-width input --}}
<input wire:model="slug" type="text" class="w-full ..." />

{{-- ❌ Don't use a split input with URL prefix box --}}
<div class="flex">
    <span class="...">{{ url('/') }}/</span>
    <input wire:model="slug" class="flex-1 ..." />
</div>
```

### Backend: Unique Slug Generation

All Livewire components with slugs **MUST** implement these three methods:

```php
use Illuminate\Support\Str;

// 1. Auto-generate slug when title changes (for new items)
public function updatedTitle($value)
{
    // Only auto-generate if slug hasn't been manually edited
    if (!$this->manualSlug && !$this->isEdit) {
        $this->slug = $this->makeUniqueSlug(Str::slug($value));
    }
}

// 2. Sanitize slug when manually edited
public function updatedSlug($value)
{
    $this->slug = Str::slug($value);
    $this->manualSlug = true;
}

// 3. Regenerate slug from title (optional refresh button)
public function generateSlug()
{
    $this->slug = $this->makeUniqueSlug(Str::slug($this->title));
    $this->manualSlug = false;
}

// 4. Ensure slug uniqueness — appends -2, -3, etc.
protected function makeUniqueSlug(string $slug): string
{
    if (empty($slug)) return '';

    $original = $slug;
    $counter = 2;

    while (YourModel::where('slug', $slug)
        ->where('id', '!=', $this->itemId ?? 0)
        ->exists()
    ) {
        $slug = $original . '-' . $counter;
        $counter++;
    }

    return $slug;
}
```

**Rules:**
- `updatedTitle()` — auto-generates slug only for **new** items or when slug was auto-generated
- `updatedSlug()` — always sanitize via `Str::slug()`, mark as manually edited
- `makeUniqueSlug()` — query the model table, exclude the current item's ID, auto-increment suffix
- For soft-deletable models, use `YourModel::withTrashed()` in the uniqueness check

> [!WARNING]
> Never skip the `makeUniqueSlug()` step. Duplicate slugs will cause routing conflicts and 404 errors on the frontend.

### Reference Implementations

| Plugin | Livewire Component | Model |
|--------|-------------------|-------|
| Posts | `PostForm` → `ensureUniqueSlug()` | `Post` |
| Pages | `PageForm` → `makeUniqueSlug()` | `Page` |
| Events | `EventConsoleGeneral` → `makeUniqueSlug()` | `Event` |

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

**Last Updated:** 2026-06-24  
**Version:** 1.1
