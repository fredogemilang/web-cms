<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request)
    {
        \Log::info('EventController::index called', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'request_path' => $request->path(),
            'request_url' => $request->fullUrl(),
        ]);
        
        $query = Event::with(['category', 'author', 'featuredImage']);

        // Filtering
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('type')) {
            $query->where('event_type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('time_filter')) {
            switch ($request->time_filter) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'ongoing':
                    $query->ongoing();
                    break;
            }
        }

        $events = $query->latest('start_date')->paginate(20);
        $categories = EventCategory::orderBy('order')->get();

        \Log::info('EventController::index rendering view', [
            'events_count' => $events->count(),
            'categories_count' => $categories->count(),
        ]);

        return view('events::admin.events.index', compact('events', 'categories'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $categories = EventCategory::orderBy('order')->get();
        return view('events::admin.events.create', compact('categories'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'event_type' => 'required|in:online,offline,hybrid',
            'category_id' => 'nullable|exists:event_categories,id',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = Event::create([
                'title' => $request->title,
                'slug' => $request->slug ?: Str::slug($request->title),
                'description' => $request->description,
                'content' => $request->content,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_all_day' => $request->is_all_day ?? false,
                'timezone' => $request->timezone ?? 'Asia/Jakarta',
                'location' => $request->location,
                'location_address' => $request->location_address,
                'location_url' => $request->location_url,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'event_type' => $request->event_type,
                'online_meeting_url' => $request->online_meeting_url,
                'category_id' => $request->category_id,
                'requires_registration' => $request->requires_registration ?? false,
                'max_participants' => $request->max_participants,
                'registration_deadline' => $request->registration_deadline,
                'featured_image_id' => $request->featured_image_id,
                'gallery_images' => $request->gallery_images,
                'status' => $request->status,
                'published_at' => $request->status === 'published' ? now() : null,
                'author_id' => auth()->id(),
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'settings' => $request->settings,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            \Log::error('Event creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Event creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['category', 'author', 'featuredImage', 'registrations']);
        return view('events::admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $categories = EventCategory::orderBy('order')->get();
        return view('events::admin.events.edit', compact('event', 'categories'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug,' . $event->id,
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'event_type' => 'required|in:online,offline,hybrid',
            'status' => 'required|in:draft,published,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event->update([
                'title' => $request->title,
                'slug' => $request->slug ?: Str::slug($request->title),
                'description' => $request->description,
                'content' => $request->content,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_all_day' => $request->is_all_day ?? false,
                'timezone' => $request->timezone ?? 'Asia/Jakarta',
                'location' => $request->location,
                'location_address' => $request->location_address,
                'location_url' => $request->location_url,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'event_type' => $request->event_type,
                'online_meeting_url' => $request->online_meeting_url,
                'category_id' => $request->category_id,
                'requires_registration' => $request->requires_registration ?? false,
                'max_participants' => $request->max_participants,
                'registration_deadline' => $request->registration_deadline,
                'featured_image_id' => $request->featured_image_id,
                'gallery_images' => $request->gallery_images,
                'status' => $request->status,
                'published_at' => $request->status === 'published' && !$event->published_at ? now() : $event->published_at,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'settings' => $request->settings,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        try {
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event deletion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export event registrations to CSV.
     */
    public function exportRegistrations(Event $event)
    {
        $registrations = $event->registrations()->with('user')->get();
        
        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No registrations to export',
            ], 404);
        }

        $filename = Str::slug($event->title) . '-registrations-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // Write header row
            $firstRegistration = $registrations->first();
            fputcsv($file, array_keys($firstRegistration->toExportArray()));
            
            // Write data rows
            foreach ($registrations as $registration) {
                fputcsv($file, $registration->toExportArray());
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
