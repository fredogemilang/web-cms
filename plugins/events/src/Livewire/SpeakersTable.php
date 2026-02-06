<?php

namespace Plugins\Events\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Plugins\Events\Models\Speaker;

class SpeakersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        try {
            Speaker::find($id)->delete();
            session()->flash('success', 'Speaker deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete speaker.');
        }
    }

    public function toggleStatus($id)
    {
        $speaker = Speaker::find($id);
        if ($speaker) {
            $speaker->is_active = !$speaker->is_active;
            $speaker->save();
        }
    }

    public function render()
    {
        $speakers = Speaker::with('photo')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('company', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('events::livewire.speakers-table', [
            'speakers' => $speakers
        ]);
    }
}
