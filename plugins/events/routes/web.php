<?php

use Illuminate\Support\Facades\Route;
use Plugins\Events\Http\Controllers\EventController;
use Plugins\Events\Http\Controllers\EventRegistrationController;

// Admin Routes
Route::prefix(config('admin.path', 'admin'))->name('admin.')->middleware(['web', 'auth'])->group(function () {
    
    Route::prefix('events')->name('events.')->middleware('permission:events.view')->group(function () {
        // Events CRUD
        Route::get('/', [EventController::class, 'index'])->name('index');
        
        // WordPress Migration
        Route::get('/migration/wordpress', function () {
            return view('events::admin.migration');
        })->name('migration.wordpress');
        
        Route::get('/migration/wordpress/speakers', function () {
            return view('events::admin.speaker-migration');
        })->name('migration.wordpress.speakers');

        Route::get('/speakers', function () {
            return view('events::admin.speakers.index');
        })->name('speakers.index')->middleware('permission:events.view');

        Route::get('/create', [EventController::class, 'create'])->name('create')->middleware('permission:events.create');
        Route::post('/', [EventController::class, 'store'])->name('store')->middleware('permission:events.create');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit')->middleware('permission:events.edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update')->middleware('permission:events.edit');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy')->middleware('permission:events.delete');
        
        // Categories
        Route::get('/categories', function () {
            return view('events::admin.categories.index');
        })->name('categories')->middleware('permission:event_categories.view');
        
        // Registrations
        Route::get('/registrations', function () {
            return view('events::admin.registrations.index');
        })->name('registrations');
        
        Route::get('/{event}/registrations', [EventRegistrationController::class, 'index'])->name('registrations.event');
        Route::get('/{event}/registrations/export', [EventRegistrationController::class, 'export'])->name('registrations.export');
        
        // Calendar View
        Route::get('/calendar', function () {
            return view('events::admin.calendar');
        })->name('calendar');
        
        // Calendar Data API
        Route::get('/calendar/data', function () {
            $events = \Plugins\Events\Models\Event::with('category')
                ->whereNotNull('start_date')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->start_date->toIso8601String(),
                        'end' => $event->end_date ? $event->end_date->toIso8601String() : null,
                        'backgroundColor' => $event->category->color ?? '#2563EB',
                        'borderColor' => $event->category->color ?? '#2563EB',
                        'url' => route('admin.events.edit', $event->id),
                    ];
                });
            
            return response()->json($events);
        })->name('calendar.data');
    });
});

// Frontend Routes
Route::prefix('event')->name('events.')->middleware(['web'])->group(function () {
    // Events listing
    Route::get('/', function () {
        // Fetch the nearest upcoming event
        $upcoming = \Plugins\Events\Models\Event::published()
            ->upcoming()
            ->first();

        // Fetch other events, excluding the upcoming one if it exists
        $query = \Plugins\Events\Models\Event::published();
        
        if ($upcoming) {
            $query->where('id', '!=', $upcoming->id);
        }

        // Apply filters
        if (request('category')) {
            $query->whereHas('category', function($q) {
                $q->where('slug', request('category'));
            });
        }
        
        if (request('type')) {
            $query->where('event_type', request('type'));
        }
        
        // Handle time filter
        if (request('time') == 'upcoming') {
            $query->upcoming();
        } elseif (request('time') == 'past') {
            $query->past();
        } elseif (request('time') == 'ongoing') {
            $query->ongoing();
        } else {
             // Default sort if no specific time filter is applied, maybe strictly upcoming or just latest
             // If we just want a general list, let's order by start_date desc to show latest additions or upcoming
             $query->orderBy('start_date', 'desc');
        }
        
        $events = $query->paginate(12);
        
        return view('iccom::events.index', compact('events', 'upcoming'));
    })->name('index');
    
    // Single event
    Route::get('/{slug}', function ($slug) {
        $event = \Plugins\Events\Models\Event::where('slug', $slug)->published()->firstOrFail();
        // return view('events::frontend.show', compact('event'));
        
        // Check if event is completed (past end date)
        if ($event->is_past) {
             return view('iccom::events.completed', compact('event'));
        }
        return view('iccom::events.single', compact('event'));
    })->name('show');
    
    // Event registration
    Route::post('/{slug}/register', [EventRegistrationController::class, 'register'])->name('register');
    Route::post('/{slug}/cancel', [EventRegistrationController::class, 'cancel'])->name('cancel');
});
