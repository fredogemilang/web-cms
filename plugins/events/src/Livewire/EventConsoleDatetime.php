<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Plugins\Events\Models\Event;
use Carbon\Carbon;

class EventConsoleDatetime extends Component
{
    public $eventId;
    public $event;

    // Date & Time
    public $start_date;
    public $start_time;
    public $end_date;
    public $end_time;
    public $is_all_day = false;
    public $timezone = 'Asia/Jakarta';

    // Registration Settings
    public $requires_registration = false;
    public $registration_requires_approval = false;
    public $requires_corporate_email = false;
    public $limit_by_quota = false;
    public $max_participants;
    public $registration_start_date;
    public $registration_start_time;
    public $registration_end_date;
    public $registration_end_time;

    // Success Page
    public $success_title;
    public $success_desc;
    public $success_button;
    public $success_link_type = 'event';
    public $success_link;

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->event = Event::findOrFail($eventId);
        $this->loadData();
    }

    protected function loadData()
    {
        $e = $this->event;

        // Date & Time
        if ($e->start_date) {
            $this->start_date = $e->start_date->format('Y-m-d');
            $this->start_time = $e->start_date->format('H:i');
        }
        if ($e->end_date) {
            $this->end_date = $e->end_date->format('Y-m-d');
            $this->end_time = $e->end_date->format('H:i');
        }
        $this->is_all_day = $e->is_all_day;
        $this->timezone = $e->timezone ?? 'Asia/Jakarta';

        // Registration
        $this->requires_registration = $e->requires_registration;
        $this->registration_requires_approval = $e->registration_requires_approval;
        $this->requires_corporate_email = $e->requires_corporate_email;
        $this->limit_by_quota = $e->limit_by_quota ?? false;
        $this->max_participants = $e->max_participants;

        if ($e->registration_start_date) {
            $this->registration_start_date = Carbon::parse($e->registration_start_date)->format('Y-m-d');
            $this->registration_start_time = Carbon::parse($e->registration_start_date)->format('H:i');
        }
        if ($e->registration_end_date) {
            $this->registration_end_date = Carbon::parse($e->registration_end_date)->format('Y-m-d');
            $this->registration_end_time = Carbon::parse($e->registration_end_date)->format('H:i');
        }

        // Success Page
        $this->success_title = $e->success_title;
        $this->success_desc = $e->success_desc;
        $this->success_button = $e->success_button;
        $this->success_link_type = $e->success_link_type ?? 'event';
        $this->success_link = $e->success_link;
    }

    public function updatedRequiresRegistration($value)
    {
        if (!$value) {
            $this->limit_by_quota = false;
            $this->max_participants = null;
        }
    }

    public function save()
    {
        $this->validate([
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_participants' => 'nullable|integer|min:1',
        ]);

        $startDateTime = Carbon::parse($this->start_date . ' ' . ($this->start_time ?? '00:00'), $this->timezone);
        $endDateTime = ($this->end_date && $this->end_time)
            ? Carbon::parse($this->end_date . ' ' . $this->end_time, $this->timezone)
            : null;

        $regStart = ($this->registration_start_date && $this->registration_start_time)
            ? Carbon::parse($this->registration_start_date . ' ' . $this->registration_start_time, $this->timezone)
            : null;
        $regEnd = ($this->registration_end_date && $this->registration_end_time)
            ? Carbon::parse($this->registration_end_date . ' ' . $this->registration_end_time, $this->timezone)
            : null;

        $this->event->update([
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'timezone' => $this->timezone,
            'requires_registration' => $this->requires_registration,
            'registration_requires_approval' => $this->registration_requires_approval,
            'requires_corporate_email' => $this->requires_corporate_email,
            'limit_by_quota' => $this->limit_by_quota,
            'max_participants' => $this->max_participants ?: null,
            'registration_start_date' => $regStart,
            'registration_end_date' => $regEnd,
            'success_title' => $this->success_title,
            'success_desc' => $this->success_desc,
            'success_button' => $this->success_button,
            'success_link_type' => $this->success_link_type,
            'success_link' => $this->success_link_type === 'custom' ? $this->success_link : null,
        ]);

        session()->flash('success', 'Date, registration, and success page settings saved.');
        $this->dispatch('notify', message: 'Settings saved successfully');
    }

    public function render()
    {
        $timezones = timezone_identifiers_list();

        return view('events::livewire.event-console-datetime', [
            'timezones' => $timezones,
        ]);
    }
}
