<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Auto-complete events that have ended
        $schedule->call(function () {
            \Plugins\Events\Models\Event::where('status', 'published')
                ->where('end_date', '<', now())
                ->update(['status' => 'completed']);
        })->daily()->at('00:01');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
