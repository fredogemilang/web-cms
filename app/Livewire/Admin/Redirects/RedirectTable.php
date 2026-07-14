<?php

namespace App\Livewire\Admin\Redirects;

use App\Models\Redirect;
use Livewire\Component;
use Livewire\WithPagination;

class RedirectTable extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    /** id being edited (0 = adding new, null = not editing) */
    public ?int $editingId = null;

    /** form payload used for both add & edit */
    public array $form = [
        'from_path' => '',
        'to_url' => '',
        'status_code' => 302,
        'is_regex' => false,
        'is_active' => true,
        'notes' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.from_path' => ['required', 'string', 'max:500'],
            'form.to_url' => ['required', 'string', 'max:1000'],
            'form.status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'form.is_regex' => ['boolean'],
            'form.is_active' => ['boolean'],
            'form.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'form.from_path.required' => 'From path is required.',
            'form.to_url.required' => 'Target URL is required.',
            'form.status_code.in' => 'Status must be 301, 302, 307, or 308.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function add(): void
    {
        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $id): void
    {
        $row = Redirect::findOrFail($id);
        $this->form = [
            'from_path' => $row->from_path,
            'to_url' => $row->to_url,
            'status_code' => $row->status_code,
            'is_regex' => $row->is_regex,
            'is_active' => $row->is_active,
            'notes' => $row->notes ?? '',
        ];
        $this->editingId = $id;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $validated = $this->validate()['form'];

        // Normalize from_path: ensure leading slash for non-regex
        if (! $validated['is_regex'] && ! str_starts_with($validated['from_path'], '/')) {
            $validated['from_path'] = '/'.$validated['from_path'];
        }

        // Validate regex compiles before persisting
        if ($validated['is_regex']) {
            $pattern = $this->delimitPatternPreview($validated['from_path']);
            if (@preg_match($pattern, '') === false) {
                $this->addError('form.from_path', 'Invalid regex pattern.');

                return;
            }
        }

        // Detect duplicate exact rule
        $dupQuery = Redirect::query()
            ->where('from_path', $validated['from_path'])
            ->where('is_regex', $validated['is_regex']);
        if ($this->editingId) {
            $dupQuery->where('id', '!=', $this->editingId);
        }
        if ($dupQuery->exists()) {
            $this->addError('form.from_path', 'A redirect rule for this path already exists.');

            return;
        }

        if ($this->editingId) {
            Redirect::find($this->editingId)?->update($validated);
            $msg = 'Redirect updated.';
        } else {
            Redirect::create($validated);
            $msg = 'Redirect created.';
        }

        $this->resetForm();
        $this->editingId = null;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function toggleActive(int $id): void
    {
        $row = Redirect::find($id);
        if (! $row) {
            return;
        }

        $row->update(['is_active' => ! $row->is_active]);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Redirect '.($row->is_active ? 'enabled' : 'disabled').'.',
        ]);
    }

    public function delete(int $id): void
    {
        Redirect::find($id)?->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Redirect deleted.']);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'from_path' => '',
            'to_url' => '',
            'status_code' => 302,
            'is_regex' => false,
            'is_active' => true,
            'notes' => '',
        ];
    }

    protected function delimitPatternPreview(string $pattern): string
    {
        if (preg_match('/^([\/#~|!@%&]).+\1[imsxuADSUXJ]*$/', $pattern)) {
            return $pattern;
        }

        return '#'.str_replace('#', '\\#', $pattern).'#';
    }

    public function render()
    {
        $redirects = Redirect::query()
            ->when($this->search, function ($q) {
                $q->where(function ($w) {
                    $w->where('from_path', 'like', '%'.$this->search.'%')
                        ->orWhere('to_url', 'like', '%'.$this->search.'%')
                        ->orWhere('notes', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.redirects.redirect-table', [
            'redirects' => $redirects,
        ]);
    }
}
