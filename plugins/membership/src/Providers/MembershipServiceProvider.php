<?php

namespace Plugins\Membership\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use App\Events\RenderAdminMenu;

class MembershipServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'membership');
        
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register Livewire Components
        Livewire::component('plugins.members-table', \Plugins\Membership\Livewire\MembersTable::class);

        // Register menu items
        Event::listen(RenderAdminMenu::class, function (RenderAdminMenu $event) {
            $event->addMenuItem([
                'title' => 'Membership',
                'route' => 'admin.membership',
                'url' => route('admin.membership.index'),
                'icon' => 'card_membership',
                'permission' => 'memberships.view',
                'is_active' => true,
                'source' => 'plugin:membership',
                'children' => [
                    [
                        'title' => 'All Members',
                        'route' => 'admin.membership.index',
                        'url' => route('admin.membership.index'),
                        'icon' => 'people',
                        'permission' => 'memberships.view',
                        'is_active' => true,
                        'source' => 'plugin:membership',
                        'children' => [],
                    ],
                    [
                        'title' => 'Pending Approval',
                        'route' => 'admin.membership.pending',
                        'url' => route('admin.membership.pending'),
                        'icon' => 'pending',
                        'permission' => 'memberships.approve',
                        'is_active' => true,
                        'source' => 'plugin:membership',
                        'children' => [],
                    ],
                ],
            ]);
        });
    }
}
