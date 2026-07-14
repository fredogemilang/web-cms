<?php

namespace Plugins\Events\Livewire;

use App\Models\Domicile;
use App\Rules\PhoneNumberFormat;
use Illuminate\Support\Str;
use Livewire\Component;
use Plugins\Events\Models\ContactLevel;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventCustomAnswer;
use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Rules\CorporateEmail;

class EventRegistrationForm extends Component
{
    public Event $event;

    // ─── Form Fields ─────────────────────────────────────────────────────────
    public string $salutation = '';

    public string $full_name = '';

    public string $company_name = '';

    public string $job_title = '';

    public int $contact_level_id = 0;

    public int $contact_divisi_id = 0;

    public string $contact_divisi_name = '';

    public string $country_code = '+62';

    public string $mobile_phone = '';

    public string $email = '';

    public string $notes = '';

    public string $referral_code = '';

    public bool $consentCheckbox = true;

    // New custom form fields
    public string $highest_education_level = '';

    public string $industry = '';

    public string $domicile = '';

    public string $linkedin = '';

    public string $domicileSearch = '';

    public ?string $domicile_other = '';

    // ─── Custom Question Answers ─────────────────────────────────────────────
    public array $custom_questions = [];

    protected $validationAttributes = [
        'full_name' => 'Name',
        'email' => 'E-mail',
        'mobile_phone' => 'Phone Number',
        'contact_level_id' => 'Job Level',
        'job_title' => 'Job Title',
        'company_name' => 'Institution/company',
        'industry' => 'Industry',
        'domicile' => 'Domicile',
        'domicile_other' => 'Specify Domicile',
    ];

    public function messages(): array
    {
        $messages = [
            'linkedin.regex' => 'LinkedIn account must be a valid LinkedIn URL.',
        ];

        foreach ($this->event->customQuestions as $question) {
            if ($question->type === 'phone') {
                $messages['custom_questions.'.$question->short_label.'.regex'] = 'Please enter a valid phone number.';
            }
        }

        return $messages;
    }

    public function validationAttributes(): array
    {
        $attributes = $this->validationAttributes;

        foreach ($this->event->customQuestions as $question) {
            $attributes['custom_questions.'.$question->short_label] = $question->question;
        }

        return $attributes;
    }

    public function getRules(): array
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                new CorporateEmail($this->event->id),
            ],
            'mobile_phone' => ['required', new PhoneNumberFormat],
            'highest_education_level' => 'nullable|string',
            'contact_level_id' => 'required|integer|min:1|exists:contact_levels,id',
            'job_title' => 'required|string',
            'company_name' => 'required|string|max:255',
            'industry' => 'required|string',
            'domicile' => 'required|string',
            'domicile_other' => 'required_if:domicile,Other|nullable|string',
            'linkedin' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/.*$/i'],
        ];

        foreach ($this->event->customQuestions as $question) {
            $questionRules = [];
            if ($question->required) {
                if ($question->type === 'multi_select') {
                    $questionRules[] = 'required';
                    $questionRules[] = 'array';
                    $questionRules[] = 'min:1';
                } else {
                    $questionRules[] = 'required';
                }
            } else {
                $questionRules[] = 'nullable';
            }

            switch ($question->type) {
                case 'email':
                    $questionRules[] = 'email';
                    break;
                case 'phone':
                    $questionRules[] = 'regex:/^[0-9\-\+\(\)\s]{6,20}$/';
                    break;
                case 'date':
                    $questionRules[] = 'date';
                    break;
            }

            if (! empty($questionRules)) {
                $rules['custom_questions.'.$question->short_label] = $questionRules;
            }
        }

        return $rules;
    }

    public function getDomicileOptions(): array
    {
        if (strlen($this->domicileSearch) < 2 || $this->domicileSearch === 'Other') {
            return [];
        }

        return Domicile::query()
            ->where('name', 'like', '%'.$this->domicileSearch.'%')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                if ($item->type === 'regency') {
                    $parent = Domicile::where('code', $item->parent_code)->first();

                    return [
                        'value' => $item->name,
                        'label' => $item->name.($parent ? ', '.$parent->name : ''),
                    ];
                }

                return [
                    'value' => $item->name,
                    'label' => $item->name.' (Provinsi)',
                ];
            })
            ->toArray();
    }

    public function selectDomicile(string $value, string $label): void
    {
        $this->domicile = $value;
        $this->domicileSearch = $label;
    }

    public function mount(string $slug): void
    {
        $this->event = Event::where('slug', $slug)->published()->firstOrFail();

        foreach ($this->event->customQuestions as $question) {
            if ($question->type === 'multi_select') {
                $this->custom_questions[$question->short_label] = [];
            } else {
                $this->custom_questions[$question->short_label] = '';
            }
        }
    }

    public function updatedContactDivisiId(int $value): void
    {
        // Clear other-divisi text when switching away from "Other"
        if ($value != 5) {
            $this->contact_divisi_name = '';
        }
    }

    public function register()
    {
        $this->validate();

        // ── Capacity check ──────────────────────────────────────────────────
        if ($this->event->max_participants) {
            $query = $this->event->registrations()->whereIn('status', ['pending', 'approved']);

            // If approval not required, only count approved
            if (! ($this->event->registration_requires_approval ?? false)) {
                $query->where('status', 'approved');
            }

            if ($query->count() >= $this->event->max_participants) {
                $this->addError('capacity', 'This event has reached its maximum capacity and is now full.');

                return;
            }
        }

        // ── Duplicate check ─────────────────────────────────────────────
        $duplicate = EventRegistration::where('event_id', $this->event->id)
            ->where('email', $this->email)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($duplicate) {
            $this->addError('email', 'This email is already registered for this event.');

            return;
        }

        // ── Registration period check ───────────────────────────────────
        if ($this->event->registration_start_date && $this->event->registration_start_date->isFuture()) {
            $this->addError('registration', 'Registration for this event has not yet opened.');

            return;
        }
        $effectiveEnd = $this->event->registration_end_date ?? $this->event->registration_deadline;
        if ($effectiveEnd && $effectiveEnd->isPast()) {
            $this->addError('registration', 'Registration for this event has closed.');

            return;
        }

        // ── Create registration ──────────────────────────────────────────
        $requiresApproval = (bool) ($this->event->registration_requires_approval ?? false);

        $finalDomicile = $this->domicile;
        if ($this->domicile === 'Other' && ! empty($this->domicile_other)) {
            $finalDomicile = $this->domicile_other;
        }

        $registration = EventRegistration::create([
            'event_id' => $this->event->id,
            'uuid' => Str::uuid(),
            'name' => $this->full_name,
            'salutation' => null,
            'full_name' => $this->full_name,
            'company_name' => $this->company_name,
            'company_type' => EventRegistration::detectCompanyType($this->company_name),
            'job_title' => $this->job_title,
            'contact_level_id' => $this->contact_level_id,
            'contact_divisi_id' => null,
            'contact_divisi_name' => null,
            'country_code' => $this->country_code,
            'mobile_phone' => EventRegistration::formatPhoneNumber($this->mobile_phone),
            'email' => $this->email,
            'notes' => null,
            'referral_code' => null,
            'referral_source' => EventRegistration::buildReferralSource(null, request()),
            'status' => $requiresApproval ? 'pending' : 'approved',
            'approved_at' => $requiresApproval ? null : now(),
            'consent_accepted_at' => now(),
            'walk_in' => false,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'custom_fields' => [
                'highest_education_level' => $this->highest_education_level,
                'industry' => $this->industry,
                'domicile' => $finalDomicile,
                'linkedin' => $this->linkedin,
            ],
        ]);

        // Increment count only if auto-approved
        if (! $requiresApproval) {
            $this->event->incrementRegisteredCount();
        }

        // ── Save custom question answers ────────────────────────────────
        $this->saveCustomAnswers($registration);

        $message = $requiresApproval
            ? 'Registration submitted! Your registration is pending admin approval.'
            : 'Registration successful! You will receive a confirmation email shortly.';

        session()->flash('success', $message);

        return redirect()->route('events.register.success', [
            'slug' => $this->event->slug,
            'email' => $this->email,
        ]);
    }

    /**
     * Save custom question answers linked to the registration.
     */
    protected function saveCustomAnswers(EventRegistration $registration): void
    {
        if (empty($this->custom_questions)) {
            return;
        }

        foreach ($this->custom_questions as $shortLabel => $answer) {
            // Skip empty answers
            if ($answer === null || $answer === '' || (is_array($answer) && empty(array_filter($answer)))) {
                continue;
            }

            $question = $this->event->customQuestions()
                ->where('short_label', $shortLabel)
                ->first();

            if (! $question) {
                continue;
            }

            // For multi-select, answer is already an array
            // For others, store as scalar
            $storeValue = is_array($answer) ? $answer : (is_string($answer) ? trim($answer) : $answer);

            EventCustomAnswer::updateOrCreate(
                [
                    'event_registration_id' => $registration->id,
                    'question_id' => $question->id,
                ],
                ['answer' => $storeValue]
            );
        }
    }

    public function render()
    {
        $contactLevels = ContactLevel::where('is_active', true)->orderBy('level')->get();

        $educationLevels = [
            'High School / Equivalent',
            'Associate Degree',
            'Bachelor\'s Degree',
            'Master\'s Degree',
            'Doctorate (Ph.D)',
        ];

        $jobTitles = [
            'C-Level (CEO, CTO, COO, CFO, etc)',
            'VP / Director',
            'General Manager / Senior Manager',
            'Manager',
            'Lead / Supervisor',
            'Senior Engineer / Senior Specialist',
            'Engineer / Developer / Specialist',
            'Junior Engineer / Associate',
            'Consultant / Advisor',
            'Student / Academic',
            'Other',
        ];

        $industries = [
            'Information Technology / Software',
            'Finance / Banking / Fintech',
            'Healthcare / Biotech',
            'Education / Edtech',
            'Telecommunications',
            'Consulting / Professional Services',
            'Retail / E-commerce',
            'Manufacturing / Logistics',
            'Media / Entertainment',
            'Government / Public Sector',
            'Other',
        ];

        $domiciles = [
            'Jabodetabek (Jakarta, Bogor, Depok, Tangerang, Bekasi)',
            'Bandung',
            'Surabaya',
            'Yogyakarta',
            'Semarang',
            'Medan',
            'Makassar',
            'Bali',
            'Other City (Indonesia)',
            'International (Outside Indonesia)',
        ];

        return view('events::livewire.event-registration-form', [
            'contactLevels' => $contactLevels,
            'educationLevels' => $educationLevels,
            'jobTitles' => $jobTitles,
            'industries' => $industries,
            'domiciles' => $domiciles,
        ]);
    }
}
