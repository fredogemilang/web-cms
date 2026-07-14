<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventFeedbackQuestion;
use Plugins\Events\Models\EventFeedbackResponse;
use Plugins\Events\Models\EventRegistration;

class FeedbackFormController extends Controller
{
    /**
     * Generic feedback page — requires email lookup.
     */
    public function show(string $slug)
    {
        $event = $this->findEvent($slug);

        return view('events::frontend.feedback-form', [
            'event' => $event,
            'registration' => null,       // no pre-identified user
            'needsEmail' => true,
            'stepCount' => max(1, $event->feedback_step_count ?? 1),
            'questions' => $this->getQuestions($event),
        ]);
    }

    /**
     * Unique-link feedback — auto-identify by UUID, no email needed.
     */
    public function showByUuid(string $slug, string $uuid)
    {
        $event = $this->findEvent($slug);

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('uuid', $uuid)
            ->first();

        if (! $registration) {
            abort(404);
        }

        // Validate eligibility
        $error = $this->checkEligibility($event, $registration);

        // Check duplicate
        if (! $error) {
            $already = EventFeedbackResponse::where('event_id', $event->id)
                ->where('event_registration_id', $registration->id)
                ->exists();
            if ($already) {
                $error = 'You have already submitted feedback for this event.';
            }
        }

        return view('events::frontend.feedback-form', [
            'event' => $event,
            'registration' => $registration,
            'needsEmail' => false,
            'eligibilityError' => $error,
            'stepCount' => max(1, $event->feedback_step_count ?? 1),
            'questions' => $this->getQuestions($event),
        ]);
    }

    /**
     * Email verification step (POST from generic link).
     */
    public function verifyEmail(string $slug, Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $event = $this->findEvent($slug);
        $email = strtolower(trim($request->input('email')));

        $registration = EventRegistration::where('event_id', $event->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        // Generic error — don't leak registration/approval/checkin status
        if (! $registration) {
            return back()->withErrors([
                'email' => 'Email not eligible for feedback. Please make sure you are registered, approved, and checked in for this event.',
            ])->withInput();
        }

        $error = $this->checkEligibility($event, $registration);
        if ($error) {
            return back()->withErrors(['email' => $error])->withInput();
        }

        // Check duplicate
        $already = EventFeedbackResponse::where('event_id', $event->id)
            ->where('event_registration_id', $registration->id)
            ->exists();
        if ($already) {
            return back()->withErrors([
                'email' => 'You have already submitted feedback for this event.',
            ])->withInput();
        }

        // Redirect to unique link so they can fill the form
        return redirect()->route('events.feedback.uuid', [$slug, $registration->uuid]);
    }

    /**
     * Submit feedback responses (from UUID link).
     */
    public function submit(string $slug, string $uuid, Request $request)
    {
        $event = $this->findEvent($slug);

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Re-check eligibility
        $error = $this->checkEligibility($event, $registration);
        if ($error) {
            return back()->withErrors(['general' => $error]);
        }

        // Check duplicate
        $already = EventFeedbackResponse::where('event_id', $event->id)
            ->where('event_registration_id', $registration->id)
            ->exists();
        if ($already) {
            return back()->withErrors(['general' => 'You have already submitted feedback for this event.']);
        }

        // Validate required questions
        $questions = EventFeedbackQuestion::where('event_id', $event->id)->get();
        foreach ($questions as $q) {
            if ($q->is_required) {
                $answer = $request->input("answers.{$q->id}");
                if (empty($answer) && $answer !== '0') {
                    return back()->withErrors(["answers.{$q->id}" => 'This question is required.'])->withInput();
                }
            }
        }

        // Save responses
        $submittedAt = Carbon::now();
        foreach ($questions as $q) {
            $answer = $request->input("answers.{$q->id}");

            if (is_array($answer)) {
                $answer = implode(', ', $answer);
            }

            if ($answer !== null && $answer !== '') {
                EventFeedbackResponse::create([
                    'event_id' => $event->id,
                    'event_registration_id' => $registration->id,
                    'question_id' => $q->id,
                    'answer' => $answer,
                    'submitted_at' => $submittedAt,
                ]);
            }
        }

        // Mark registration as feedback submitted
        $registration->update([
            'feedback_submitted' => true,
            'feedback_submitted_at' => $submittedAt,
        ]);

        // Redirect
        if ($event->feedback_redirect_url) {
            return redirect($event->feedback_redirect_url);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }

    // ─── Helpers ───

    private function findEvent(string $slug): Event
    {
        return Event::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    private function getQuestions(Event $event)
    {
        return EventFeedbackQuestion::where('event_id', $event->id)
            ->with('options')
            ->ordered()
            ->get()
            ->groupBy('step');
    }

    /**
     * Check if a registration is eligible to give feedback.
     * Returns error message string or null if eligible.
     */
    private function checkEligibility(Event $event, EventRegistration $registration): ?string
    {
        // Must be approved
        if ($registration->status !== 'approved') {
            return 'Email not eligible for feedback. Please make sure you are registered, approved, and checked in for this event.';
        }

        // Must be checked in (if event requires it)
        if ($event->feedback_require_checkin && ! $registration->check_in) {
            return 'Email not eligible for feedback. Please make sure you are registered, approved, and checked in for this event.';
        }

        return null;
    }
}
