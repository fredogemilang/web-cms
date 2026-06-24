<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Models\ContactLevel;
use Plugins\Events\Models\ContactDivision;
use App\Rules\PhoneNumberFormat;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Walk-in Registration — Admin Flow (PRD 02)
 *
 * Allows admin to manually register an attendee on-site.
 * Walk-in registrations are:
 *  - Immediately confirmed (bypass approval workflow)
 *  - Flagged with walk_in = true
 *  - Auto checked-in (check_in = true, check_in_date = now())
 *  - registration_type = 'walk_in'
 */
class WalkInRegistration extends Component
{
    public Event $event;

    // ── Modal state ─────────────────────────────────────────────────────────
    public bool $showModal    = false;
    public bool $showSuccess  = false;
    public ?int $lastRegId    = null;

    // ── Form fields ─────────────────────────────────────────────────────────
    public string $salutation        = '';
    public string $full_name         = '';
    public string $company_name      = '';
    public string $job_title         = '';
    public int    $contact_level_id  = 0;
    public int    $contact_divisi_id = 0;
    public string $contact_divisi_name = '';
    public string $country_code      = '+62';
    public string $mobile_phone      = '';
    public string $email             = '';
    public string $notes             = '';

    protected function getRules(): array
    {
        return [
            'salutation'           => 'nullable|in:Mr,Ms,Mrs',
            'full_name'            => 'required|string|max:255',
            'company_name'         => 'required|string|max:255',
            'job_title'            => 'required|string|max:255',
            'contact_level_id'     => 'required|integer|min:1',
            'contact_divisi_id'    => 'required|integer|min:1',
            'contact_divisi_name'  => 'nullable|string|max:255',
            'country_code'         => 'nullable|string|max:10',
            'mobile_phone'         => ['required', new PhoneNumberFormat()],
            'email'                => 'required|email|max:255',
            'notes'                => 'nullable|string',
        ];
    }

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function updatedContactDivisiId(int $value): void
    {
        if ($value != 5) {
            $this->contact_divisi_name = '';
        }
    }

    public function openModal(): void
    {
        $this->reset([
            'salutation', 'full_name', 'company_name', 'job_title',
            'contact_level_id', 'contact_divisi_id', 'contact_divisi_name',
            'mobile_phone', 'email', 'notes',
        ]);
        $this->country_code = '+62';
        $this->showSuccess  = false;
        $this->lastRegId    = null;
        $this->showModal    = true;
    }

    public function closeModal(): void
    {
        $this->showModal   = false;
        $this->showSuccess = false;
    }

    public function register(): void
    {
        // Conditional divisi_name validation
        if ($this->contact_divisi_id == 5 && empty(trim($this->contact_divisi_name))) {
            $this->addError('contact_divisi_name', 'Please specify your division.');
            return;
        }

        $this->validate();

        // ── Capacity check ────────────────────────────────────────────────
        if ($this->event->max_participants) {
            $count = $this->event->registrations()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($count >= $this->event->max_participants) {
                $this->addError('capacity', 'This event has reached its maximum capacity.');
                return;
            }
        }

        // ── Duplicate check ───────────────────────────────────────────────
        $duplicate = EventRegistration::where('event_id', $this->event->id)
            ->where('email', $this->email)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($duplicate) {
            $this->addError('email', 'This email is already registered for this event.');
            return;
        }

        // ── Create walk-in registration (auto-confirmed + auto-checked-in) ─
        $registration = EventRegistration::create([
            'event_id'            => $this->event->id,
            'uuid'                => Str::uuid(),
            'salutation'          => $this->salutation ?: null,
            'full_name'           => $this->full_name,
            'name'                => $this->full_name,         // legacy compat
            'company_name'        => $this->company_name,
            'company_type'        => EventRegistration::detectCompanyType($this->company_name),
            'job_title'           => $this->job_title,
            'contact_level_id'    => $this->contact_level_id,
            'contact_divisi_id'   => $this->contact_divisi_id,
            'contact_divisi_name' => $this->contact_divisi_id == 5 ? $this->contact_divisi_name : null,
            'country_code'        => $this->country_code,
            'mobile_phone'        => EventRegistration::formatPhoneNumber($this->mobile_phone),
            'email'               => $this->email,
            'notes'               => $this->notes ?: null,
            // Walk-in specifics
            'status'              => 'approved',
            'approved_at'         => now(),
            'walk_in'             => true,
            'check_in'            => true,
            'check_in_date'       => now(),
            'registration_type'   => 'walk_in',
            'referral_source'     => 'Walk-in',
            'ip_address'          => request()->ip(),
            'user_agent'          => request()->userAgent(),
        ]);

        // Update registered count
        $this->event->incrementRegisteredCount();

        $this->lastRegId   = $registration->id;
        $this->showSuccess = true;

        // Dispatch event so the registrations table can refresh
        $this->dispatch('registration-created');
    }

    public function render()
    {
        $contactLevels    = ContactLevel::where('is_active', true)->orderBy('level')->get();
        $contactDivisions = ContactDivision::where('is_active', true)->orderBy('name')->get();
        $countries        = [
            '+62' => 'Indonesia (+62)',
            '+65' => 'Singapore (+65)',
            '+60' => 'Malaysia (+60)',
            '+66' => 'Thailand (+66)',
            '+63' => 'Philippines (+63)',
            '+84' => 'Vietnam (+84)',
        ];

        $lastReg = $this->lastRegId
            ? EventRegistration::find($this->lastRegId)
            : null;

        return view('events::livewire.walk-in-registration', [
            'contactLevels'    => $contactLevels,
            'contactDivisions' => $contactDivisions,
            'countries'        => $countries,
            'lastReg'          => $lastReg,
        ]);
    }
}
