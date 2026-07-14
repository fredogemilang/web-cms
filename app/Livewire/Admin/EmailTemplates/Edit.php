<?php

namespace App\Livewire\Admin\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateVersion;
use App\Services\EmailTemplateRenderer;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public ?int $templateId = null;

    public EmailTemplate $template;

    public string $name = '';

    public string $key_name = '';

    public string $subject = '';

    public string $body_html = '';

    public ?string $body_text = null;

    public ?string $description = null;

    public string $testEmail = '';

    public function mount(int $id): void
    {
        $this->templateId = $id;
        $this->template = EmailTemplate::findOrFail($id);
        $this->fill($this->template->only(['name', 'key_name', 'subject', 'body_html', 'body_text', 'description']));
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'key_name' => ['required', 'string', 'max:100', Rule::unique('email_templates', 'key_name')->ignore($this->template->id)],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'body_text' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        // Snapshot before mutation, capped at 5 versions.
        EmailTemplateVersion::create([
            'template_id' => $this->template->id,
            'subject' => $this->template->subject,
            'body_html' => $this->template->body_html,
            'edited_by' => auth()->id(),
            'created_at' => now(),
        ]);

        EmailTemplateVersion::where('template_id', $this->template->id)
            ->orderByDesc('created_at')
            ->skip(5)
            ->pluck('id')
            ->each(fn ($id) => EmailTemplateVersion::find($id)?->delete());

        $this->template->update([
            'name' => $this->name,
            'key_name' => $this->key_name,
            'subject' => $this->subject,
            'body_html' => $this->body_html,
            'body_text' => $this->body_text,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Template saved.');
    }

    public function rollback(int $versionId, EmailTemplateRenderer $renderer): void
    {
        $version = EmailTemplateVersion::where('template_id', $this->template->id)
            ->findOrFail($versionId);

        $this->subject = $version->subject;
        $this->body_html = $version->body_html;
        $this->save();
        session()->flash('success', 'Rolled back to selected version.');
    }

    public function sendTest(EmailTemplateRenderer $renderer): void
    {
        $this->validate(['testEmail' => ['required', 'email']]);
        $dummy = $this->dummyVariables();
        // Persist current form state without bumping version, so what's sent
        // matches what the admin sees on screen.
        $tpl = clone $this->template;
        $tpl->subject = $this->subject;
        $tpl->body_html = $this->body_html;
        $tpl->body_text = $this->body_text;
        $tpl->save();
        $renderer->send($tpl->key_name, $this->testEmail, $dummy);
        session()->flash('success', "Test email sent to {$this->testEmail}.");
    }

    protected function dummyVariables(): array
    {
        $bag = [
            'user' => ['name' => 'Test User', 'email' => $this->testEmail],
            'reset_url' => url('/'),
            'expires_in_minutes' => 60,
            'inviter' => ['name' => auth()->user()?->name ?? 'Admin'],
            'accept_url' => url('/'),
            'form' => ['name' => 'Test Form', 'url' => url('/')],
            'entry' => ['summary' => 'Dummy submission contents.', 'url' => url('/')],
        ];
        foreach ((array) $this->template->variables as $k => $_label) {
            data_fill($bag, $k, '['.$k.']');
        }

        return $bag;
    }

    public function getPreviewProperty(): string
    {
        return app(EmailTemplateRenderer::class)->render($this->body_html, $this->dummyVariables());
    }

    public function render()
    {
        return view('livewire.admin.email-templates.edit', [
            'versions' => $this->template->versions()->limit(5)->get(),
        ]);
    }
}
