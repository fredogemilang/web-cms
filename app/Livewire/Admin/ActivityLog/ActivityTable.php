<?php

namespace App\Livewire\Admin\ActivityLog;

use App\Models\Activity;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $userFilter = '';

    public string $actionFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 25;

    public ?int $expandedId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'userFilter' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'userFilter', 'actionFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function render()
    {
        $activities = Activity::query()
            ->with(['user:id,name,email,avatar', 'subject'])
            ->when($this->search, fn ($q) => $q->where(function ($w) {
                $w->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('action', 'like', '%'.$this->search.'%');
            }))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->actionFilter, fn ($q) => $q->where('action', 'like', $this->actionFilter.'%'))
            ->when($this->dateFrom, fn ($q) => $q->where('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('created_at', '<=', $this->dateTo.' 23:59:59'))
            ->recent()
            ->paginate($this->perPage);

        return view('livewire.admin.activity-log.activity-table', [
            'activities' => $activities,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'actionGroups' => $this->actionGroups(),
        ]);
    }

    protected function actionGroups(): array
    {
        // For the filter dropdown — distinct prefixes
        $actions = Activity::query()->distinct()->pluck('action')->all();
        $prefixes = [];
        foreach ($actions as $a) {
            $prefix = explode('.', $a)[0] ?? $a;
            $prefixes[$prefix] = ucfirst($prefix);
        }
        ksort($prefixes);

        return $prefixes;
    }
}
