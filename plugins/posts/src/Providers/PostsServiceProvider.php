<?php

namespace Plugins\Posts\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use App\Events\RenderAdminMenu;
use Plugins\Posts\Livewire\PostsTable;
use Plugins\Posts\Livewire\PostForm;

class PostsServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'posts');
        
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register Livewire Components
        Livewire::component('plugins.posts-table', PostsTable::class);
        Livewire::component('plugins.post-form', PostForm::class);
        Livewire::component('plugins.categories-manager', \Plugins\Posts\Livewire\CategoriesManager::class);
        Livewire::component('plugins.posts-settings', \Plugins\Posts\Livewire\Settings::class);
        Livewire::component('plugins.wordpress-migration', \Plugins\Posts\Livewire\WordPressMigration::class);
        Livewire::component('posts.blog-list', \Plugins\Posts\Livewire\BlogList::class);

        // Register menu items via event (PRD Section 9.1)
        Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
            $event->addMenuItem([
                'title' => 'Posts',
                'route' => 'admin.posts',
                'url' => route('admin.posts.index'),
                'icon' => 'rss_feed',
                'permission' => 'posts.view',
                'is_active' => true,
                'source' => 'plugin:posts',
                'children' => [
                    [
                        'title' => 'All Posts',
                        'route' => 'admin.posts.index',
                        'url' => route('admin.posts.index'),
                        'icon' => 'list',
                        'permission' => 'posts.view',
                        'is_active' => true,
                        'source' => 'plugin:posts',
                        'children' => [],
                    ],
                    [
                        'title' => 'Create Post',
                        'route' => 'admin.posts.create',
                        'url' => route('admin.posts.create'),
                        'icon' => 'add_circle',
                        'permission' => 'posts.create',
                        'is_active' => true,
                        'source' => 'plugin:posts',
                        'children' => [],
                    ],
                    [
                        'title' => 'Categories',
                        'route' => 'admin.posts.categories',
                        'url' => route('admin.posts.categories'),
                        'icon' => 'category',
                        'permission' => 'categories.view',
                        'is_active' => true,
                        'source' => 'plugin:posts',
                        'children' => [],
                    ],
                    [
                        'title' => 'Tags',
                        'route' => 'admin.posts.tags',
                        'url' => route('admin.posts.tags'),
                        'icon' => 'label',
                        'permission' => 'tags.view',
                        'is_active' => true,
                        'source' => 'plugin:posts',
                        'children' => [],
                    ],
                    [
                        'title' => 'Settings',
                        'route' => 'admin.posts.settings',
                        'url' => route('admin.posts.settings'),
                        'icon' => 'settings',
                        'permission' => 'posts.view', // Reusing view permission for now, or could create a new one
                        'is_active' => true,
                        'source' => 'plugin:posts',
                        'children' => [],
                    ],
                ],
            ]);
        });
    }
}
