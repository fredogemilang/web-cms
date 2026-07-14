<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

/**
 * Central registry of Settings groups and their field schemas.
 *
 * Core code and plugins call registerGroup() in a service provider's boot()
 * method to expose a Settings page under /admin/settings/{slug}.
 *
 * Field shape:
 *   [
 *     'key'     => 'site_name',         // unique within group, stored in settings.key
 *     'label'   => 'Site Name',
 *     'type'    => 'text'|'textarea'|'select'|'boolean'|'number'|'email'|'password'|'code',
 *     'default' => 'My CMS',
 *     'rules'   => ['required','string','max:255'],
 *     'options' => ['id' => 'Bahasa Indonesia', 'en' => 'English'], // for select
 *     'help'    => 'Displayed in the browser title bar.',
 *     'order'   => 10,
 *     'section' => 'Identity',          // optional grouping inside the page
 *   ]
 */
class SettingsRegistry
{
    /** @var array<string, array{label:string,icon:string,permission:?string,order:int,fields:array}> */
    protected array $groups = [];

    public function registerGroup(string $slug, array $config): void
    {
        $existing = $this->groups[$slug] ?? null;

        $fields = $config['fields'] ?? [];
        // Sort by 'order' (default 100) so callers can interleave registrations.
        usort($fields, fn ($a, $b) => ($a['order'] ?? 100) <=> ($b['order'] ?? 100));

        $this->groups[$slug] = [
            'slug' => $slug,
            'label' => $config['label'] ?? ucfirst($slug),
            'icon' => $config['icon'] ?? 'settings',
            'permission' => $config['permission'] ?? 'settings.view',
            'order' => $config['order'] ?? 100,
            'description' => $config['description'] ?? null,
            // Optional custom Livewire component (e.g. 'admin.redirects.redirect-table').
            // When set, SettingsPage renders this component instead of the generic field form.
            'component' => $config['component'] ?? null,
            'actions' => $config['actions'] ?? [],
            'fields' => $existing
                ? $this->mergeFields($existing['fields'], $fields)
                : $fields,
        ];
    }

    public function groups(): array
    {
        $sorted = $this->groups;
        uasort($sorted, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $sorted;
    }

    public function hasGroup(string $slug): bool
    {
        return isset($this->groups[$slug]);
    }

    public function group(string $slug): ?array
    {
        return $this->groups[$slug] ?? null;
    }

    public function fields(string $slug): array
    {
        return $this->groups[$slug]['fields'] ?? [];
    }

    public function defaults(string $slug): array
    {
        $out = [];
        foreach ($this->fields($slug) as $field) {
            $out[$field['key']] = $field['default'] ?? null;
        }

        return $out;
    }

    public function rules(string $slug): array
    {
        $out = [];
        foreach ($this->fields($slug) as $field) {
            $out[$field['key']] = $field['rules'] ?? ['nullable'];
        }

        return $out;
    }

    public function labels(string $slug): array
    {
        $out = [];
        foreach ($this->fields($slug) as $field) {
            $out[$field['key']] = $field['label'] ?? $field['key'];
        }

        return $out;
    }

    public function validate(string $slug, array $data): array
    {
        return Validator::make($data, $this->rules($slug), [], $this->labels($slug))->validate();
    }

    protected function mergeFields(array $existing, array $incoming): array
    {
        $byKey = [];
        foreach ($existing as $f) {
            $byKey[$f['key']] = $f;
        }
        foreach ($incoming as $f) {
            $byKey[$f['key']] = $f;
        }
        $merged = array_values($byKey);
        usort($merged, fn ($a, $b) => ($a['order'] ?? 100) <=> ($b['order'] ?? 100));

        return $merged;
    }
}
