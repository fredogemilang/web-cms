<?php

namespace App\Livewire\Admin\Trash;

use App\Models\CptEntry;
use App\Models\Form;
use App\Models\Media;
use App\Models\Page;
use Livewire\Component;
use Livewire\WithPagination;

class TrashIndex extends Component
{
    use WithPagination;

    public string $resource = 'pages';

    public array $selected = [];

    protected array $resources = [
        'pages' => ['model' => Page::class,      'title_field' => 'title', 'label' => 'Pages'],
        'cpt' => ['model' => CptEntry::class,  'title_field' => 'title', 'label' => 'CPT Entries'],
        'forms' => ['model' => Form::class,      'title_field' => 'name',  'label' => 'Forms'],
        'media' => ['model' => Media::class,     'title_field' => 'filename', 'label' => 'Media'],
    ];

    public function updatedResource(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function restore(int $id): void
    {
        $this->authorizeAccess();
        $model = $this->modelClass();
        $model::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('trash-changed');
        session()->flash('success', 'Item restored.');
    }

    public function forceDelete(int $id): void
    {
        $this->authorizeAccess();
        $model = $this->modelClass();
        $model::onlyTrashed()->findOrFail($id)->forceDelete();
        $this->dispatch('trash-changed');
        session()->flash('success', 'Item permanently deleted.');
    }

    public function bulkRestore(): void
    {
        $this->authorizeAccess();
        $model = $this->modelClass();
        $model::onlyTrashed()->whereIn('id', $this->selected)->restore();
        $this->selected = [];
        session()->flash('success', 'Selected items restored.');
    }

    public function bulkForceDelete(): void
    {
        $this->authorizeAccess();
        $model = $this->modelClass();
        $model::onlyTrashed()->whereIn('id', $this->selected)->forceDelete();
        $this->selected = [];
        session()->flash('success', 'Selected items permanently deleted.');
    }

    protected function modelClass(): string
    {
        return $this->resources[$this->resource]['model'] ?? Page::class;
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasPermission('content.trash.manage') || auth()->user()?->hasRole('super-admin'), 403);
    }

    public function render()
    {
        $model = $this->modelClass();
        $items = $model::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return view('livewire.admin.trash.trash-index', [
            'items' => $items,
            'titleField' => $this->resources[$this->resource]['title_field'],
            'resources' => $this->resources,
        ]);
    }
}
