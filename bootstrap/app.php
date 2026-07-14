<?php

use App\Http\Middleware\ApiAuth;
use App\Http\Middleware\ApiCors;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\OptimizeHtml;
use App\Http\Middleware\PageCache;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Plugins\Events\Models\Event;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => CheckPermission::class,
            'role' => CheckRole::class,
            'enforce-2fa' => EnforceTwoFactor::class,
            'api.auth' => ApiAuth::class,
            'api.cors' => ApiCors::class,
        ]);

        // Run redirect rules before route matching (so 404 paths can still redirect).
        $middleware->prepend(HandleRedirects::class);

        // Set app locale from query → session → cookie → setting.
        $middleware->web(append: [
            SetLocale::class,
            PageCache::class,
            OptimizeHtml::class,
            CompressResponse::class,
            SecurityHeaders::class,
        ]);

        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Auto-complete events that have ended
        $schedule->call(function () {
            Event::where('status', 'published')
                ->where('end_date', '<', now())
                ->update(['status' => 'completed']);
        })->daily()->at('00:01');

        // Prune old audit log entries (default 90 days, configurable via setting)
        $schedule->command('activity:prune')->dailyAt('03:00')->onOneServer();

        // Flip scheduled content to published once published_at arrives.
        $schedule->command('content:publish-scheduled')->everyMinute()->withoutOverlapping();

        // Purge trash older than retention window (default 30 days).
        $schedule->command('content:purge-trash')->dailyAt('02:30')->onOneServer();

        // Cron-driven queue worker for shared hosting (no daemon allowed).
        // Each minute we drain pending jobs and exit before the next tick.
        // --stop-when-empty: exit as soon as the queue is empty
        // --max-time=55:     hard cap so we never collide with the next minute
        // --max-jobs=100:    safety cap per run
        // --tries=3 / --backoff=10: retry transient failures
        // withoutOverlapping(): RateLimiter-backed mutex so two ticks never race
        $schedule->command('queue:work', [
            '--stop-when-empty',
            '--max-time=55',
            '--max-jobs=100',
            '--tries=3',
            '--backoff=10',
        ])->everyMinute()->withoutOverlapping(60)->runInBackground();

        // Prune failed jobs older than 14 days to keep the table small.
        $schedule->command('queue:prune-failed', ['--hours=336'])->dailyAt('03:30')->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
