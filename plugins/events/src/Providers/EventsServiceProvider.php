<?php

namespace Plugins\Events\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Events\RenderAdminMenu;
use Plugins\Events\Models\Event as EventModel;
use Plugins\Events\Observers\EventObserver;

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
        Livewire::component('plugins.event-wizard', \Plugins\Events\Livewire\EventWizard::class);
        Livewire::component('plugins.event-registration-form', \Plugins\Events\Livewire\EventRegistrationForm::class);
        Livewire::component('plugins.walk-in-registration', \Plugins\Events\Livewire\WalkInRegistration::class);
        Livewire::component('plugins.speakers-table', \Plugins\Events\Livewire\SpeakersTable::class);
        Livewire::component('plugins.events-questions-manager', \Plugins\Events\Livewire\QuestionsManager::class);
        Livewire::component('plugins.event-guests-table', \Plugins\Events\Livewire\EventGuestsTable::class);

        // Console Tab Components
        Livewire::component('plugins.event-console-general', \Plugins\Events\Livewire\EventConsoleGeneral::class);
        Livewire::component('plugins.event-console-datetime', \Plugins\Events\Livewire\EventConsoleDatetime::class);
        Livewire::component('plugins.event-console-emails', \Plugins\Events\Livewire\EventConsoleEmails::class);
        Livewire::component('plugins.event-console-feedback', \Plugins\Events\Livewire\EventConsoleFeedback::class);
        Livewire::component('plugins.event-console-doorprize', \Plugins\Events\Livewire\EventConsoleDoorprize::class);
        Livewire::component('plugins.event-console-referrals', \Plugins\Events\Livewire\EventConsoleReferrals::class);

        // Register model observer — fires after event create to seed email templates
        EventModel::observe(EventObserver::class);

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