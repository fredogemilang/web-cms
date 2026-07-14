<?php

namespace Plugins\Events\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Models\TrackingCode;

class EventConsoleReferrals extends Component
{
    public Event $event;

    // Form/Modal state
    public bool $showCreateModal = false;

    public ?int $editingId = null;

    public string $trackingCode = '';

    public string $source = '';

    public string $description = '';

    public bool $isActive = true;

    // Delete Modal state
    public bool $showDeleteModal = false;

    public ?int $deletingId = null;

    protected array $rules = [
        'trackingCode' => 'required|string|max:50|alpha_dash',
        'source' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'isActive' => 'boolean',
    ];

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function getTrackingCodesProperty()
    {
        return TrackingCode::where('event_id', $this->event->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReferralStatsProperty()
    {
        return EventRegistration::where('event_id', $this->event->id)
            ->selectRaw("
                referral_source,
                referral_code,
                count(*) as total_count,
                sum(case when status = 'approved' then 1 else 0 end) as approved_count,
                sum(case when check_in = 1 then 1 else 0 end) as checked_in_count
            ")
            ->groupBy('referral_source', 'referral_code')
            ->orderByDesc('total_count')
            ->get();
    }

    public function getSummaryStatsProperty()
    {
        $referralRegs = EventRegistration::where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->whereNotNull('referral_code')
                    ->orWhere(function ($sq) {
                        $sq->whereNotNull('referral_source')->where('referral_source', '!=', 'Direct');
                    });
            })->get();

        $top = collect($this->referralStats)->reject(fn ($s) => ($s->referral_source ?? 'Direct') === 'Direct')->first();

        return [
            'total' => $referralRegs->count(),
            'approved' => $referralRegs->where('status', 'approved')->count(),
            'checked_in' => $referralRegs->where('check_in', true)->count(),
            'top_campaign' => $top ? $top->referral_source : 'N/A',
            'top_campaign_count' => $top ? $top->total_count : 0,
        ];
    }

    public function openCreateModal(?int $id = null): void
    {
        $this->resetErrorBag();
        if ($id) {
            $record = TrackingCode::findOrFail($id);
            $this->editingId = $record->id;
            $this->trackingCode = $record->tracking_code;
            $this->source = $record->source;
            $this->description = $record->description ?? '';
            $this->isActive = (bool) $record->is_active;
        } else {
            $this->editingId = null;
            // Generate a random referral/tracking code prefix
            $this->trackingCode = 'REF-'.strtoupper(Str::random(6));
            $this->source = '';
            $this->description = '';
            $this->isActive = true;
        }
        $this->showCreateModal = true;
    }

    public function saveTrackingCode(): void
    {
        $this->validate();

        // Custom validation: check uniqueness of code
        $query = TrackingCode::where('tracking_code', $this->trackingCode);
        if ($this->editingId) {
            $query->where('id', '!=', $this->editingId);
        }
        if ($query->exists()) {
            $this->addError('trackingCode', 'This tracking code is already in use.');

            return;
        }

        TrackingCode::updateOrCreate(
            ['id' => $this->editingId],
            [
                'event_id' => $this->event->id,
                'tracking_code' => $this->trackingCode,
                'source' => $this->source,
                'description' => $this->description ?: null,
                'is_active' => $this->isActive,
            ]
        );

        $this->showCreateModal = false;
        $this->dispatch('notify', message: 'Tracking code saved successfully.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteTrackingCode(): void
    {
        if ($this->deletingId) {
            TrackingCode::destroy($this->deletingId);
            $this->deletingId = null;
            $this->showDeleteModal = false;
            $this->dispatch('notify', message: 'Tracking code deleted.');
        }
    }

    public function toggleActive(int $id): void
    {
        $record = TrackingCode::findOrFail($id);
        $record->update(['is_active' => ! $record->is_active]);
        $this->dispatch('notify', message: 'Status updated successfully.');
    }

    public function render()
    {
        return view('events::livewire.event-console-referrals');
    }
}
