<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Services\SettingsRegistry;
use App\Settings\Contracts\SettingsAction;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class SettingsPage extends Component
{
    public string $group;

    /** @var array<string, mixed> Current form values keyed by setting key. */
    public array $values = [];

    /** Receives media-selected events from the media picker modal. */
    #[On('media-selected')]
    public function onMediaSelected(string $mediaPath, ?string $field = null): void
    {
        if ($field && array_key_exists($field, $this->values)) {
            $this->values[$field] = $mediaPath;
        }
    }

    public function mount(string $group = 'general'): void
    {
        $registry = app(SettingsRegistry::class);

        abort_unless($registry->hasGroup($group), 404, "Settings group [{$group}] is not registered.");

        $this->group = $group;

        // Hydrate from defaults, then overlay stored values
        $this->values = array_replace(
            $registry->defaults($group),
            Setting::forGroup($group),
        );
    }

    public function runAction(int $index): void
    {
        $registry = app(SettingsRegistry::class);
        $actions = $registry->group($this->group)['actions'] ?? [];
        $action = $actions[$index] ?? null;

        if (! $action || empty($action['handler'])) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Unknown action.']);

            return;
        }

        $handler = app($action['handler']);
        if (! $handler instanceof SettingsAction) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Action handler is not a SettingsAction.']);

            return;
        }

        $result = $handler->handle($this->values);
        $this->dispatch('notify', $result);
    }

    public function save(): void
    {
        $registry = app(SettingsRegistry::class);

        try {
            $validated = $registry->validate($this->group, $this->values);
        } catch (ValidationException $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please fix the errors below.']);
            throw $e;
        }

        $types = [];
        foreach ($registry->fields($this->group) as $field) {
            $types[$field['key']] = $this->fieldStorageType($field['type'] ?? 'string');
        }

        Setting::setMany($validated, $this->group, $types);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Settings saved.']);
    }

    protected function fieldStorageType(string $uiType): string
    {
        return match ($uiType) {
            'boolean' => 'boolean',
            'number' => 'integer',
            'multiselect', 'tags', 'array' => 'array',
            default => 'string',
        };
    }

    public function render()
    {
        $registry = app(SettingsRegistry::class);

        return view('livewire.admin.settings.settings-page', [
            'currentGroup' => $registry->group($this->group),
            'allGroups' => $registry->groups(),
            'fields' => $registry->fields($this->group),
        ]);
    }
}
