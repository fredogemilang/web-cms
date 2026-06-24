<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\ApprovalType;

class EventConsoleEmails extends Component
{
    use WithFileUploads;

    public $eventId;
    public $event;

    // Sender Config
    public $sending_email = true;
    public $sender_name;
    public $sender_email;
    public $cc_to_email;

    // Direct Template Fields
    public $pending_subject;
    public $pending_body;
    public $approved_subject;
    public $approved_body;
    public $rejected_subject;
    public $rejected_body;

    // Banner Config
    public $email_banner;
    public $current_banner;

    // Active Email Tab (for UI only)
    public $activeEmailTab = 'pending';

    // Custom Approval / Rejection Types properties
    public $primaryTemplateIds = [];
    public $customTemplates = [];

    public $showTypeModal = false;
    public $typeId = null;
    public $typeName = '';
    public $typeCat = 'approved';
    public $typeSubject = '';
    public $typeBody = '';

    #[On('media-selected')]
    public function onMediaSelected($field, $mediaId, $mediaPath, $mediaUrl)
    {
        if ($field === 'email_banner') {
            $this->current_banner = $mediaUrl;
        }
    }

    #[On('media-removed')]
    public function onMediaRemoved($field)
    {
        if ($field === 'email_banner') {
            $this->current_banner = null;
        }
    }

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->event = Event::findOrFail($eventId);
        $this->loadData();
    }

    protected function loadData()
    {
        $this->sending_email = (bool) $this->event->sending_email;
        $this->sender_name = $this->event->sender_name;
        $this->sender_email = $this->event->sender_email;
        $this->cc_to_email = $this->event->cc_to_email;

        $this->loadTemplates();
    }

    protected function loadTemplates()
    {
        $categories = ['pending', 'approved', 'rejected'];
        foreach ($categories as $cat) {
            $record = ApprovalType::firstOrCreate(
                ['event_id' => $this->eventId, 'cat' => $cat],
                [
                    'type_name' => $cat === 'approved' ? 'Regular' : ($cat === 'rejected' ? 'Not Eligible' : 'Pending Approval'),
                    'email_subject' => "Registration " . ucfirst($cat) . ": {{event_title}}",
                    'email_body' => $this->buildDefaultBody($cat),
                ]
            );

            $this->{$cat . '_subject'} = $record->email_subject;
            $this->{$cat . '_body'} = $record->email_body;
            $this->primaryTemplateIds[$cat] = $record->id;

            if ($record->email_banner) {
                $this->current_banner = $record->email_banner;
            }
        }

        $this->customTemplates = ApprovalType::where('event_id', $this->eventId)
            ->whereNotIn('id', array_values($this->primaryTemplateIds))
            ->get();
    }

    protected function buildDefaultBody(string $category): string
    {
        $messages = [
            'pending'  => "Your registration for {{event_title}} is pending approval. We will notify you once confirmed.",
            'approved' => "Your registration for {{event_title}} has been approved. See you there!",
            'rejected' => "Unfortunately, your registration for {{event_title}} has been declined.",
        ];
        $message = $messages[$category] ?? "You have registered for {{event_title}}.";
        return "<p>Dear {{name}},</p><p>{$message}</p><p>If you have any questions, please contact the event organizer.</p>";
    }

    public function removeBanner()
    {
        $this->email_banner = null;
        $this->current_banner = null;

        foreach (['pending', 'approved', 'rejected'] as $cat) {
            ApprovalType::where('event_id', $this->eventId)
                ->where('cat', $cat)
                ->update(['email_banner' => null]);
        }

        $this->dispatch('notify', message: 'Email banner removed.');
    }

    public function save()
    {
        $this->validate([
            'sender_name' => 'required_if:sending_email,true|nullable|string|max:255',
            'sender_email' => 'required_if:sending_email,true|nullable|email|max:255',
            'pending_subject' => 'required|string|max:255',
            'pending_body' => 'required|string',
            'approved_subject' => 'required|string|max:255',
            'approved_body' => 'required|string',
            'rejected_subject' => 'required|string|max:255',
            'rejected_body' => 'required|string',
        ]);

        $this->event->update([
            'sending_email' => $this->sending_email,
            'sender_name' => $this->sender_name,
            'sender_email' => $this->sender_email,
            'cc_to_email' => $this->cc_to_email,
        ]);

        $bannerPath = $this->current_banner;

        foreach (['pending', 'approved', 'rejected'] as $cat) {
            ApprovalType::updateOrCreate(
                ['event_id' => $this->eventId, 'cat' => $cat],
                [
                    'type_name' => $cat === 'approved' ? 'Regular' : ($cat === 'rejected' ? 'Not Eligible' : 'Pending Approval'),
                    'email_subject' => $this->{$cat . '_subject'},
                    'email_body' => $this->{$cat . '_body'},
                    'email_banner' => $bannerPath,
                ]
            );
        }

        session()->flash('success', 'Email settings and templates saved successfully.');
        $this->dispatch('notify', message: 'Settings saved successfully');
    }

    public function openTypeModal(?int $id = null): void
    {
        $this->resetErrorBag();
        if ($id) {
            $record = ApprovalType::findOrFail($id);
            $this->typeId = $record->id;
            $this->typeName = $record->type_name;
            $this->typeCat = $record->cat;
            $this->typeSubject = $record->email_subject;
            $this->typeBody = $record->email_body;
        } else {
            $this->typeId = null;
            $this->typeName = '';
            $this->typeCat = 'approved';
            $this->typeSubject = '';
            $this->typeBody = '';
        }
        $this->showTypeModal = true;
    }

    public function saveCustomType(): void
    {
        $this->validate([
            'typeName' => 'required|string|max:100',
            'typeCat' => 'required|in:approved,rejected',
            'typeSubject' => 'required|string|max:255',
            'typeBody' => 'required|string',
        ]);

        ApprovalType::updateOrCreate(
            ['id' => $this->typeId],
            [
                'event_id' => $this->eventId,
                'cat' => $this->typeCat,
                'type_name' => $this->typeName,
                'email_subject' => $this->typeSubject,
                'email_body' => $this->typeBody,
                'email_banner' => $this->current_banner,
            ]
        );

        $this->showTypeModal = false;
        $this->loadTemplates();
        $this->dispatch('notify', message: 'Custom template saved successfully.');
    }

    public function deleteCustomType(int $id): void
    {
        $record = ApprovalType::where('event_id', $this->eventId)
            ->where('id', $id)
            ->first();

        if ($record && !in_array($id, array_values($this->primaryTemplateIds))) {
            $record->delete();
            $this->loadTemplates();
            $this->dispatch('notify', message: 'Custom template deleted successfully.');
        }
    }

    public function render()
    {
        return view('events::livewire.event-console-emails');
    }
}
