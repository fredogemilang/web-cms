<?php

namespace Plugins\Events\Providers;

use App\Events\RenderAdminMenu;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Plugins\Events\Livewire\CategoriesManager;
use Plugins\Events\Livewire\EventConsoleDatetime;
use Plugins\Events\Livewire\EventConsoleDoorprize;
use Plugins\Events\Livewire\EventConsoleEmails;
use Plugins\Events\Livewire\EventConsoleFeedback;
use Plugins\Events\Livewire\EventConsoleGeneral;
use Plugins\Events\Livewire\EventConsoleReferrals;
use Plugins\Events\Livewire\EventForm;
use Plugins\Events\Livewire\EventGuestsTable;
use Plugins\Events\Livewire\EventRegistrationForm;
use Plugins\Events\Livewire\EventsTable;
use Plugins\Events\Livewire\EventWizard;
use Plugins\Events\Livewire\QuestionsManager;
use Plugins\Events\Livewire\RegistrationsTable;
use Plugins\Events\Livewire\SpeakersTable;
use Plugins\Events\Livewire\WalkInRegistration;
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
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Load Views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'events');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register Livewire Components
        Livewire::component('plugins.events-table', EventsTable::class);
        Livewire::component('plugins.event-categories', CategoriesManager::class);
        Livewire::component('plugins.event-registrations', RegistrationsTable::class);
        Livewire::component('plugins.event-form', EventForm::class);
        Livewire::component('plugins.event-wizard', EventWizard::class);
        Livewire::component('plugins.event-registration-form', EventRegistrationForm::class);
        Livewire::component('plugins.walk-in-registration', WalkInRegistration::class);
        Livewire::component('plugins.speakers-table', SpeakersTable::class);
        Livewire::component('plugins.events-questions-manager', QuestionsManager::class);
        Livewire::component('plugins.event-guests-table', EventGuestsTable::class);

        // Console Tab Components
        Livewire::component('plugins.event-console-general', EventConsoleGeneral::class);
        Livewire::component('plugins.event-console-datetime', EventConsoleDatetime::class);
        Livewire::component('plugins.event-console-emails', EventConsoleEmails::class);
        Livewire::component('plugins.event-console-feedback', EventConsoleFeedback::class);
        Livewire::component('plugins.event-console-doorprize', EventConsoleDoorprize::class);
        Livewire::component('plugins.event-console-referrals', EventConsoleReferrals::class);

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
