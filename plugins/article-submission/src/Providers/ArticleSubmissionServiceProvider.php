<?php

namespace Plugins\ArticleSubmission\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use App\Events\RenderAdminMenu;
use Plugins\ArticleSubmission\Livewire\ArticleSubmissionsTable;


class ArticleSubmissionServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'article-submission');
        
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register Livewire Components
        Livewire::component('plugins.article-submissions-table', ArticleSubmissionsTable::class);

        // Register menu items via event
        Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
            $event->addMenuItem([
                'title' => 'Article Submission',
                'route' => 'admin.article-submissions',
                'url' => route('admin.article-submissions.index'),
                'icon' => 'edit_document',
                'permission' => 'submissions.view',
                'is_active' => true,
                'source' => 'plugin:article-submission',
                'children' => [
                    [
                        'title' => 'All Submissions',
                        'route' => 'admin.article-submissions.index',
                        'url' => route('admin.article-submissions.index'),
                        'icon' => 'list',
                        'permission' => 'submissions.view',
                        'is_active' => true,
                        'source' => 'plugin:article-submission',
                        'children' => [],
                    ],
                ],
            ]);
        });
    }
}
