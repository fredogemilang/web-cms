<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Domicile;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;

class DomicileManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public int $perPage = 15;

    /** id being edited (0 = adding new, null = not editing) */
    public ?int $editingId = null;

    /** form payload used for both add & edit */
    public array $form = [
        'code' => '',
        'name' => '',
        'parent_code' => '',
        'type' => 'regency',
    ];

    protected function rules(): array
    {
        return [
            'form.code' => ['required', 'string', 'max:50'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.parent_code' => ['required_if:form.type,regency', 'nullable', 'string', 'max:50'],
            'form.type' => ['required', 'string', 'in:province,regency'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.code' => 'code',
            'form.name' => 'name',
            'form.parent_code' => 'parent province',
            'form.type' => 'type',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
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
        $row = Domicile::findOrFail($id);
        $this->form = [
            'code' => $row->code,
            'name' => $row->name,
            'parent_code' => $row->parent_code ?? '',
            'type' => $row->type,
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

        // Normalize parent_code: empty strings should be null
        if ($validated['type'] === 'province') {
            $validated['parent_code'] = null;
        } else {
            $validated['parent_code'] = $validated['parent_code'] ?: null;
        }

        // Detect duplicate code
        $dupQuery = Domicile::query()->where('code', $validated['code']);
        if ($this->editingId) {
            $dupQuery->where('id', '!=', $this->editingId);
        }
        if ($dupQuery->exists()) {
            $this->addError('form.code', 'A domicile with this code already exists.');

            return;
        }

        if ($this->editingId) {
            Domicile::find($this->editingId)?->update($validated);
            $msg = 'Domicile updated.';
        } else {
            Domicile::create($validated);
            $msg = 'Domicile created.';
        }

        $this->resetForm();
        $this->editingId = null;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function delete(int $id): void
    {
        $row = Domicile::find($id);
        if ($row) {
            // Also nullify/clean children parent references if it's a province
            if ($row->type === 'province') {
                Domicile::where('parent_code', $row->code)->update(['parent_code' => null]);
            }
            $row->delete();
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Domicile deleted.']);
    }

    public function importFromApi(): void
    {
        try {
            // Disable maximum execution time limit for this request
            @set_time_limit(0);

            // Execute the Artisan command programmatically
            Artisan::call('domicile:import');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Domicile data imported successfully from wilayah.id!',
            ]);
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to import from API: '.$e->getMessage(),
            ]);
        }
    }

    protected function resetForm(): void
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'parent_code' => '',
            'type' => 'regency',
        ];
    }

    public function render()
    {
        $provinces = Domicile::where('type', 'province')->orderBy('name')->get();

        $domiciles = Domicile::query()
            ->when($this->search, function ($q) {
                $q->where(function ($w) {
                    $w->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.settings.domicile-manager', [
            'domiciles' => $domiciles,
            'provinces' => $provinces,
        ]);
    }
}
