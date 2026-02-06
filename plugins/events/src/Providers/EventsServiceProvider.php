<?php

namespace Plugins\Events\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use App\Events\RenderAdminMenu;

class EventsServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'events');
        
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register Livewire Components
        Livewire::component('plugins.events-table', \Plugins\Events\Livewire\EventsTable::class);
        Livewire::component('plugins.event-categories', \Plugins\Events\Livewire\CategoriesManager::class);
        Livewire::component('plugins.event-registrations', \Plugins\Events\Livewire\RegistrationsTable::class);
        Livewire::component('plugins.event-form', \Plugins\Events\Livewire\EventForm::class);
        Livewire::component('plugins.speakers-table', \Plugins\Events\Livewire\SpeakersTable::class);

        // Register menu items
        Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
            $event->addMenuItem([
                'title' => 'Events',
                'route' => 'admin.events',
                'url' => route('admin.events.index'),
                'icon' => 'event',
                'permission' => 'events.view',
                'is_active' => true,
                'source' => 'plugin:events',
                'children' => [
                    [
                        'title' => 'All Events',
                        'route' => 'admin.events.index',
                        'url' => route('admin.events.index'),
                        'icon' => 'list',
                        'permission' => 'events.view',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                    [
                        'title' => 'Create Event',
                        'route' => 'admin.events.create',
                        'url' => route('admin.events.create'),
                        'icon' => 'add_circle',
                        'permission' => 'events.create',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                    [
                        'title' => 'Categories',
                        'route' => 'admin.events.categories',
                        'url' => route('admin.events.categories'),
                        'icon' => 'category',
                        'permission' => 'event_categories.view',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                    [
                        'title' => 'Registrations',
                        'route' => 'admin.events.registrations',
                        'url' => route('admin.events.registrations'),
                        'icon' => 'people',
                        'permission' => 'events.view',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                    [
                        'title' => 'Calendar',
                        'route' => 'admin.events.calendar',
                        'url' => route('admin.events.calendar'),
                        'icon' => 'calendar_month',
                        'permission' => 'events.view',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                    [
                        'title' => 'Speakers',
                        'route' => 'admin.events.speakers.index',
                        'url' => route('admin.events.speakers.index'),
                        'icon' => 'mic',
                        'permission' => 'events.view',
                        'is_active' => true,
                        'source' => 'plugin:events',
                        'children' => [],
                    ],
                ],
            ]);
        });
    }
}
