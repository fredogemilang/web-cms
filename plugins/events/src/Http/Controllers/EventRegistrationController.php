<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventRegistrationController extends Controller
{
    /**
     * Handle event registration.
     */
    public function register(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $isAjax = $request->ajax() || $request->wantsJson();

        // Check if registration is open
        if (!$event->is_registration_open) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is closed for this event.',
                ], 400);
            }
            return back()->with('error', 'Registration is closed for this event.');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'phone'             => 'nullable|string|max:20',
            'institution'       => 'nullable|string|max:255',
            'job_level'         => 'nullable|string|max:100',
            'domicile'          => 'nullable|string|max:100',
            'job_title'         => 'nullable|string|max:100',
            'linkedin'          => 'nullable|string|max:255',
            'highest_education' => 'nullable|string|max:100',
            'industry'          => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Check for duplicate registration
            $existingRegistration = EventRegistration::where('event_id', $event->id)
                ->where('email', $request->email)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingRegistration) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already registered for this event.',
                    ], 400);
                }
                return back()->with('error', 'You have already registered for this event.');
            }

            // Check if event requires approval for registrations
            $requiresApproval = $event->registration_requires_approval ?? false;
            $status           = $requiresApproval ? 'pending' : 'confirmed';
            $confirmedAt      = $requiresApproval ? null : now();

            // Collect extra fields into custom_fields JSON
            $customFields = array_filter([
                'job_level'         => $request->job_level,
                'domicile'          => $request->domicile,
                'job_title'         => $request->job_title,
                'linkedin'          => $request->linkedin,
                'institution'       => $request->institution,
                'highest_education' => $request->highest_education,
                'industry'          => $request->industry,
            ], fn($value) => !is_null($value) && $value !== '');

            $registration = EventRegistration::create([
                'event_id'      => $event->id,
                'user_id'       => auth()->id(),
                'name'          => $request->name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'organization'  => $request->institution, // map institution → organization
                'notes'         => $request->notes,
                'status'        => $status,
                'confirmed_at'  => $confirmedAt,
                'custom_fields' => !empty($customFields) ? $customFields : null,
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
            ]);

            // Only increment count if auto-confirmed
            if (!$requiresApproval) {
                $event->incrementRegisteredCount();
            }

            // TODO: Send confirmation email
            // event(new EventRegistrationCreated($registration));

            $message = $requiresApproval
                ? 'Registration submitted! Your registration is pending admin approval.'
                : 'Registration successful! You will receive a confirmation email shortly.';

            if ($isAjax) {
                return response()->json([
                    'success'  => true,
                    'message'  => $message,
                    'redirect' => route('events.register.success'),
                ]);
            }

            return redirect()->route('events.register.success')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Event registration failed: ' . $e->getMessage());
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed. Please try again.',
                ], 500);
            }
            return back()->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Cancel registration.
     */
    public function cancel(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('email', $request->email)
            ->whereIn('status', ['pending', 'confirmed'])
            ->firstOrFail();

        $registration->cancel();

        return back()->with('success', 'Your registration has been cancelled.');
    }

    /**
     * View all registrations for an event (admin).
     */
    public function index($eventId)
    {
        $event = Event::with('registrations.user')->findOrFail($eventId);
        return view('events::admin.registrations.event', compact('event'));
    }

    /**
     * Export registrations to CSV.
     */
    public function export($eventId)
    {
        $event = Event::findOrFail($eventId);
        $registrations = $event->registrations()->with('user')->get();
        
        if ($registrations->isEmpty()) {
            return back()->with('error', 'No registrations to export.');
        }

        $filename = \Str::slug($event->title) . '-registrations-' . now()->format('Y-m-d') . '.csv';
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
