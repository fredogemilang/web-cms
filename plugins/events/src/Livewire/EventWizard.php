<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCategory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Multi-step event creation/edit wizard.
 * 4 steps:
 *   1. Event Details  — title, category, type, description, content
 *   2. Date & Schedule — start/end dates, registration window, timezone
 *   3. Properties     — quota, approval flags, email sender, banner upload
 *   4. Success Page   — success_title, success_desc, success_button, redirect
 *
 * Each step validates before advancing. Data is saved as a draft after every step.
 * Ref: PRD 01 section 1.1
 */
class EventWizard extends Component
{
    use WithFileUploads;

    // ─── Wizard State ─────────────────────────────────────────────────────────
    public int $currentStep = 1;
    public int $totalSteps  = 4;

    /** @var Event|null Null = create mode; Event instance = edit mode */
    public ?Event $event = null;
    public int $eventId = 0;

    // ─── Step 1: Event Details ────────────────────────────────────────────────
    public string $title       = '';
    public string $slug        = '';
    public ?int $category_id   = null;
    public string $event_type  = 'offline';
    public ?string $description      = null;
    public ?string $content          = null;
    public ?string $online_meeting_url = null;
    public ?string $location           = null;
    public ?string $location_address   = null;
    public ?string $location_url       = null;

    // ─── Step 2: Date & Schedule ──────────────────────────────────────────────
    public string $start_date = '';
    public string $start_time = '09:00';
    public ?string $end_date   = null;
    public ?string $end_time   = null;
    public bool $is_all_day   = false;
    public string $timezone   = 'Asia/Jakarta';

    public ?string $registration_start_date = null;
    public ?string $registration_end_date   = null;

    // ─── Step 3: Properties ──────────────────────────────────────────────────
    public bool $requires_registration          = true;
    public bool $registration_requires_approval = false;
    public bool $requires_corporate_email       = false;
    public bool $sending_email                  = true;
    public ?string $sender_email = null;
    public ?string $sender_name  = null;
    public ?string $cc_to_email  = null;
    public ?int $max_participants = null;
    public ?string $banner_image = null;  // temporary uploaded path

    // ─── Step 4: Success Page ───────────────────────────────────────────────
    public ?string $success_title       = null;
    public ?string $success_desc        = null;
    public string $success_button       = 'Back to Event';
    public string $success_link_type    = 'event';
    public ?string $success_link        = null;
    public bool $show_registered_count  = false;
    public bool $enable_track_session   = false;

    // ─── UI State ─────────────────────────────────────────────────────────────
    public string $status = 'draft';

    // Step titles for the progress indicator
    protected array $stepTitles = [
        1 => 'Event Details',
        2 => 'Date & Schedule',
        3 => 'Properties',
        4 => 'Success Page',
    ];

    // ─── Lifecycle ──────────────────────────────────────────────────────────────

    public function mount(int $eventId = 0)
    {
        $this->eventId = $eventId;

        if ($eventId > 0) {
            $this->event = Event::findOrFail($eventId);
            $this->loadEventData();
        } else {
            $this->start_date = now()->format('Y-m-d');
            $this->timezone   = config('app.timezone', 'Asia/Jakarta');
        }
    }

    // ─── Step Navigation ──────────────────────────────────────────────────────

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        // Only allow going back or to an already-visited step
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function submit(): void
    {
        $this->validateCurrentStep();
        $this->saveEvent();
        session()->flash('success', 'Event saved successfully.');
        $this->redirect(route('admin.events.index'), navigate: true);
    }

    public function saveDraft(): void
    {
        $this->saveEvent(true);
        session()->flash('message', 'Draft saved.');
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    protected function validateCurrentStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => $this->step1Rules(),
            2 => $this->step2Rules(),
            3 => $this->step3Rules(),
            4 => $this->step4Rules(),
            default => [],
        };

        $this->validate($rules);
    }

    protected function step1Rules(): array
    {
        $uniqueSlug = $this->eventId
            ? "unique:events,slug,{$this->eventId}"
            : 'unique:events,slug';

        return [
            'title'       => 'required|string|max:255',
            'slug'        => "nullable|string|max:255|{$uniqueSlug}",
            'category_id' => 'nullable|exists:event_categories,id',
            'event_type'  => 'required|in:online,offline,hybrid',
            'description' => 'nullable|string',
            'content'     => 'nullable|string',
        ];
    }

    protected function step2Rules(): array
    {
        return [
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'end_time'   => 'nullable',
            'timezone'   => 'required|timezone',
            'registration_start_date' => 'nullable|date',
            'registration_end_date'   => 'nullable|date|after_or_equal:registration_start_date',
        ];
    }

    protected function step3Rules(): array
    {
        return [
            'max_participants'          => 'nullable|integer|min:1',
            'sender_email'             => 'nullable|email',
            'sender_name'              => 'nullable|string|max:255',
            'cc_to_email'              => 'nullable|email',
            'banner_image'             => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    protected function step4Rules(): array
    {
        return [
            'success_title'   => 'nullable|string|max:255',
            'success_desc'   => 'nullable|string',
            'success_button' => 'nullable|string|max:100',
            'success_link'   => 'nullable|url|max:500',
        ];
    }

    // ─── Slug Auto-generation ─────────────────────────────────────────────────

    public function updatedTitle(string $value): void
    {
        if (!$this->eventId || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    // ─── Save Logic ───────────────────────────────────────────────────────────

    /**
     * Save the event to DB. If $asDraft is true, saves step progress only
     * without triggering full validation.
     */
    protected function saveEvent(bool $asDraft = false): Event
    {
        $startDateTime = Carbon::parse("{$this->start_date} {$this->start_time}", $this->timezone);
        $endDateTime   = null;
        if ($this->end_date) {
            $endDateTime = Carbon::parse(
                "{$this->end_date} " . ($this->end_time ?? '23:59'),
                $this->timezone
            );
        }

        $bannerPath = null;
        if ($this->banner_image) {
            // Bug fix #1: slug may be empty in create mode before first save.
            // Fall back to a slugified title so the storage path is never empty.
            $folder = $this->slug ?: \Illuminate\Support\Str::slug($this->title ?: 'event');
            $bannerPath = $this->banner_image->store("events/{$folder}", 'public');
        }

        $data = [
            'title'                          => $this->title,
            'slug'                           => $this->slug ?: Str::slug($this->title),
            'category_id'                    => $this->category_id,
            'event_type'                     => $this->event_type,
            'description'                    => $this->description,
            'content'                        => $this->content,
            'start_date'                     => $startDateTime,
            'end_date'                       => $endDateTime,
            'is_all_day'                     => $this->is_all_day,
            'timezone'                       => $this->timezone,
            // Bug fix #2: location & online meeting fields were missing from save payload
            'location'                       => $this->location,
            'location_address'               => $this->location_address,
            'location_url'                   => $this->location_url,
            'online_meeting_url'             => $this->online_meeting_url,
            'registration_start_date'        => $this->registration_start_date
                ? Carbon::parse($this->registration_start_date) : null,
            'registration_end_date'          => $this->registration_end_date
                ? Carbon::parse($this->registration_end_date) : null,
            'requires_registration'          => $this->requires_registration,
            'registration_requires_approval' => $this->registration_requires_approval,
            'requires_corporate_email'       => $this->requires_corporate_email,
            'sending_email'                  => $this->sending_email,
            'sender_email'                   => $this->sender_email,
            'sender_name'                    => $this->sender_name,
            'cc_to_email'                    => $this->cc_to_email,
            'max_participants'               => $this->max_participants,
            'banner_image'                   => $bannerPath ?? $this->event?->banner_image,
            'success_title'                  => $this->success_title,
            'success_desc'                   => $this->success_desc,
            'success_button'                 => $this->success_button,
            'success_link_type'              => $this->success_link_type,
            'success_link'                   => $this->success_link,
            'show_registered_count'          => $this->show_registered_count,
            'enable_track_session'           => $this->enable_track_session,
            'status'                         => $this->status,
            'wizard_step'                    => $this->currentStep,
        ];

        if ($this->eventId > 0) {
            $this->event->update($data);
            $event = $this->event;
        } else {
            $data['author_id']    = auth()->id();
            $data['published_at'] = $this->status === 'published' ? now() : null;
            $event = Event::create($data);
            $this->eventId = $event->id;
            $this->event   = $event;
        }

        return $event;
    }

    // ─── Data Loading (Edit Mode) ─────────────────────────────────────────────

    protected function loadEventData(): void
    {
        $e = $this->event;

        // Step 1
        $this->title              = $e->title;
        $this->slug               = $e->slug;
        $this->category_id        = $e->category_id;
        $this->event_type         = $e->event_type;
        $this->description        = $e->description;
        $this->content            = $e->content;
        $this->status             = $e->status;
        // Bug fix #2: load location fields in edit mode
        $this->location           = $e->location;
        $this->location_address   = $e->location_address;
        $this->location_url       = $e->location_url;
        $this->online_meeting_url = $e->online_meeting_url;

        // Step 2
        $this->start_date = $e->start_date->format('Y-m-d');
        $this->start_time = $e->start_date->format('H:i');
        if ($e->end_date) {
            $this->end_date = $e->end_date->format('Y-m-d');
            $this->end_time = $e->end_date->format('H:i');
        }
        $this->is_all_day = $e->is_all_day;
        $this->timezone   = $e->timezone ?? 'Asia/Jakarta';
        $this->registration_start_date = $e->registration_start_date?->format('Y-m-d');
        $this->registration_end_date   = $e->registration_end_date?->format('Y-m-d');

        // Step 3
        $this->requires_registration           = $e->requires_registration;
        $this->registration_requires_approval  = $e->registration_requires_approval;
        $this->requires_corporate_email         = $e->requires_corporate_email;
        $this->sending_email                   = $e->sending_email;
        $this->sender_email                   = $e->sender_email;
        $this->sender_name                     = $e->sender_name;
        $this->cc_to_email                     = $e->cc_to_email;
        $this->max_participants                 = $e->max_participants;

        // Step 4
        $this->success_title       = $e->success_title;
        $this->success_desc        = $e->success_desc;
        $this->success_button      = $e->success_button ?? 'Back to Event';
        $this->success_link_type   = $e->success_link_type ?? 'event';
        $this->success_link        = $e->success_link;
        $this->show_registered_count = $e->show_registered_count;
        $this->enable_track_session  = $e->enable_track_session;

        // Wizard position
        $this->currentStep = $e->wizard_step > 0 ? (int) $e->wizard_step : 1;
    }

    // ─── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $categories = EventCategory::orderBy('order')->get();
        $timezones  = timezone_identifiers_list();

        return view('events::livewire.event-wizard', [
            'categories' => $categories,
            'timezones' => $timezones,
        ]);
    }
}
