<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\ThemesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnershipController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Models\CustomPostType;
use App\Models\CustomTaxonomy;
use App\Services\SettingsRegistry;
use Illuminate\Support\Facades\Route;

// Get admin path from config
$adminPath = config('admin.path', 'admin');

// Public homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// Public Form Submission
Route::prefix('forms')->name('forms.')->group(function () {
    Route::get('/{slug}', [FormSubmissionController::class, 'show'])->name('show');
    Route::post('/{slug}/submit', [FormSubmissionController::class, 'submit'])->name('submit');
    Route::post('/{slug}/ajax', [FormSubmissionController::class, 'submitAjax'])->name('submit.ajax');
    Route::get('/{slug}/success', [FormSubmissionController::class, 'success'])->name('success');
});

// Public Partnership Inquiry
Route::post('/partner', [PartnershipController::class, 'store'])->name('partner.store');

// Admin base path redirect
Route::get("/{$adminPath}", [AdminController::class, 'index'])->name('admin.index');

// Authentication Routes (under admin path)
Route::prefix($adminPath)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])
            ->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])
            ->name('password.email');
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])
            ->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
            ->name('password.update');

        Route::get('/two-factor', [TwoFactorController::class, 'showChallenge'])
            ->name('two-factor.challenge');
        Route::post('/two-factor', [TwoFactorController::class, 'verify'])
            ->name('two-factor.verify');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Admin Routes
Route::prefix($adminPath)->name('admin.')->middleware(['auth', 'enforce-2fa'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:dashboard.view');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile.index');

    // Users Management
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Roles Management
    // Route::middleware('permission:roles.view')->group(function () {
    //     Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    //     Route::get('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'editPermissions'])
    //         ->name('roles.permissions.edit')
    //         ->middleware('permission:roles.assign-permissions');
    //     Route::put('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])
    //         ->name('roles.permissions.update')
    //         ->middleware('permission:roles.assign-permissions');
    // });

    // Role & Permission Merged View
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('role-permission', [RolePermissionController::class, 'index'])
            ->name('role-permission.index');
        Route::post('role-permission/role', [RolePermissionController::class, 'storeRole'])
            ->name('role-permission.store-role')
            ->middleware('permission:roles.create');
        Route::put('role-permission/role/{role}', [RolePermissionController::class, 'updateRole'])
            ->name('role-permission.update-role')
            ->middleware('permission:roles.edit');
        Route::delete('role-permission/role/{role}', [RolePermissionController::class, 'deleteRole'])
            ->name('role-permission.delete-role')
            ->middleware('permission:roles.delete');
        Route::post('role-permission/clone/{role}', [RolePermissionController::class, 'cloneRole'])
            ->name('role-permission.clone-role')
            ->middleware('permission:roles.create');
        Route::post('role-permission/toggle/{role}', [RolePermissionController::class, 'togglePermission'])
            ->name('role-permission.toggle-permission')
            ->middleware('permission:roles.assign-permissions');
    });

    // Permissions Management
    // Route::middleware('permission:permissions.view')->group(function () {
    //     Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    // });

    // Menus Management
    Route::middleware('permission:menus.view')->group(function () {
        Route::resource('menus', MenuController::class);
        Route::post('menus/reorder', [MenuController::class, 'reorder'])
            ->name('menus.reorder');
    });

    // Media Management
    Route::middleware('permission:media.view')->group(function () {
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::get('media/create', [MediaController::class, 'create'])
            ->name('media.create')
            ->middleware('permission:media.upload');
        Route::post('media/upload', [MediaController::class, 'upload'])
            ->name('media.upload')
            ->middleware('permission:media.upload');
        Route::put('media/{media}', [MediaController::class, 'update'])
            ->name('media.update')
            ->middleware('permission:media.edit');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])
            ->name('media.destroy')
            ->middleware('permission:media.delete');
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])
            ->name('media.bulk-delete')
            ->middleware('permission:media.delete');
    });

    // Plugin Management
    Route::middleware('permission:plugins.view')->group(function () {
        Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
        Route::post('plugins', [PluginController::class, 'store'])->name('plugins.store');
        Route::post('plugins/{plugin}/activate', [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
        Route::delete('plugins/{plugin}', [PluginController::class, 'destroy'])->name('plugins.destroy');
    });

    // Theme Management
    Route::prefix('appearance')->name('themes.')->middleware('permission:themes.view')->group(function () {
        Route::get('/themes', [ThemesController::class, 'index'])->name('index');
        Route::post('/themes/upload', [ThemesController::class, 'upload'])->name('upload');
        Route::post('/themes/{theme}/activate', [ThemesController::class, 'activate'])->name('activate');
        Route::delete('/themes/{theme}', [ThemesController::class, 'destroy'])->name('destroy');
    });

    // Custom Post Types Management
    Route::prefix('cpt')->name('cpt.')->group(function () {
        Route::get('/', function () {
            return view('admin.cpt.index');
        })->name('index');
        Route::get('/create', function () {
            return view('admin.cpt.create');
        })->name('create');
        Route::get('/{id}/edit', function ($id) {
            return view('admin.cpt.edit', ['id' => $id]);
        })->name('edit');

        // WordPress CPT Migration
        Route::get('/migration/wordpress', function () {
            return view('admin.cpt.wordpress-migration');
        })->name('wordpress-migration');

        // CPT Entries (Content) Management
        Route::prefix('entries/{postTypeSlug}')->name('entries.')->group(function () {
            Route::get('/', function ($postTypeSlug) {
                $postType = CustomPostType::where('slug', $postTypeSlug)->firstOrFail();

                return view('admin.cpt.entries.index', ['postType' => $postType]);
            })->name('index');
            Route::get('/create', function ($postTypeSlug) {
                $postType = CustomPostType::where('slug', $postTypeSlug)->firstOrFail();

                return view('admin.cpt.entries.create', ['postType' => $postType]);
            })->name('create');
            Route::get('/{id}/edit', function ($postTypeSlug, $id) {
                $postType = CustomPostType::where('slug', $postTypeSlug)->firstOrFail();

                return view('admin.cpt.entries.edit', ['postType' => $postType, 'id' => $id]);
            })->name('edit');
        });
    });

    // Pages Management
    Route::prefix('pages')->name('pages.')->middleware('permission:pages.view')->group(function () {
        Route::get('/', function () {
            return view('admin.pages.index');
        })->name('index');
        Route::get('/create', function () {
            return view('admin.pages.create');
        })->name('create')->middleware('permission:pages.create');
        Route::get('/{id}/edit', function ($id) {
            return view('admin.pages.edit', ['id' => $id]);
        })->name('edit')->middleware('permission:pages.edit');
        Route::get('/{id}/preview', [PageController::class, 'preview'])
            ->name('preview')->middleware('permission:pages.edit');
    });

    // Forms Management
    Route::prefix('forms')->name('forms.')->middleware('permission:forms.view')->group(function () {
        Route::get('/', [FormController::class, 'index'])->name('index');
        Route::get('/create', [FormController::class, 'create'])
            ->name('create')
            ->middleware('permission:forms.create');
        Route::post('/', [FormController::class, 'store'])
            ->name('store')
            ->middleware('permission:forms.create');
        Route::get('/{form}', [FormController::class, 'show'])->name('show');
        Route::get('/{form}/edit', [FormController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:forms.edit');
        Route::put('/{form}', [FormController::class, 'update'])
            ->name('update')
            ->middleware('permission:forms.edit');
        Route::delete('/{form}', [FormController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:forms.delete');
        Route::get('/{form}/entries', [FormController::class, 'entries'])
            ->name('entries');
        Route::get('/{form}/export', [FormController::class, 'exportEntries'])
            ->name('export');
        Route::post('/{form}/toggle', [FormController::class, 'toggleStatus'])
            ->name('toggle')
            ->middleware('permission:forms.edit');
        Route::delete('/entries/{entry}', [FormController::class, 'deleteEntry'])
            ->name('entries.delete')
            ->middleware('permission:forms.delete');
    });

    // Audit log
    Route::middleware('permission:activity.view')->group(function () {
        Route::get('/activity', function () {
            return view('admin.activity.index');
        })->name('activity.index');
    });

    // Trash
    Route::middleware('permission:content.trash.view')->group(function () {
        Route::get('/trash', function () {
            return view('admin.trash.index');
        })->name('trash.index');
    });

    // Email Templates
    Route::middleware('permission:email-templates.view')->group(function () {
        Route::get('/email-templates', function () {
            return view('admin.email-templates.index');
        })->name('email-templates.index');
        Route::get('/email-templates/{id}/edit', function ($id) {
            return view('admin.email-templates.edit', ['id' => (int) $id]);
        })->name('email-templates.edit')->middleware('permission:email-templates.edit');
    });

    // Queue Dashboard
    Route::middleware('permission:queue.view')->group(function () {
        Route::get('/queue', function () {
            return view('admin.queue.index');
        })->name('queue.index');
    });

    // API Tokens
    Route::middleware('permission:api-tokens.view')->group(function () {
        Route::get('/api-tokens', function () {
            return view('admin.api-tokens.index');
        })->name('api-tokens.index');
    });

    // Webhooks
    Route::middleware('permission:webhooks.view')->group(function () {
        Route::get('/webhooks', function () {
            return view('admin.webhooks.index');
        })->name('webhooks.index');
    });

    // Settings (generic, group-based)
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.settings.show', 'general');
        })->name('index');
        Route::get('/{group}', function (string $group) {
            abort_unless(app(SettingsRegistry::class)->hasGroup($group), 404);

            return view('admin.settings.show', ['group' => $group]);
        })->name('show');
    });

    // Custom Taxonomies Management
    Route::prefix('taxonomies')->name('taxonomies.')->group(function () {
        Route::get('/', function () {
            return view('admin.taxonomies.index');
        })->name('index');
        Route::get('/create', function () {
            return view('admin.taxonomies.create');
        })->name('create');
        Route::get('/{id}/edit', function ($id) {
            return view('admin.taxonomies.edit', ['id' => $id]);
        })->name('edit');

        // Taxonomy Terms Management
        Route::prefix('{taxonomy}/terms')->name('terms.')->group(function () {
            Route::get('/', function ($taxonomyId) {
                $taxonomy = CustomTaxonomy::findOrFail($taxonomyId);

                return view('admin.taxonomies.terms.index', ['taxonomy' => $taxonomy]);
            })->name('index');

        });
    });
});

// Frontend Page Route is now handled in PluginServiceProvider to ensure it runs after plugin routes
// Route::get('/{slug}', [\App\Http\Controllers\PageController::class, 'show'])
//     ->where('slug', '(?!' . preg_quote(config('admin.path', 'admin'), '/') . ')[a-zA-Z0-9\-]+')
//     ->name('pages.show');
