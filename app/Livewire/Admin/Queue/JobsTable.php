<?php

namespace App\Livewire\Admin\Queue;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class JobsTable extends Component
{
    use WithPagination;

    public string $tab = 'pending';   // pending | failed

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['pending', 'failed'], true) ? $tab : 'pending';
        $this->resetPage();
    }

    public function retry(string $uuid): void
    {
        $this->checkPermission();
        Artisan::call('queue:retry', ['id' => [$uuid]]);
        session()->flash('success', "Job {$uuid} queued for retry.");
    }

    public function forget(int $id): void
    {
        $this->checkPermission();
        DB::table('failed_jobs')->where('id', $id)->delete();
        session()->flash('success', 'Job removed.');
    }

    public function retryAll(): void
    {
        $this->checkPermission();
        Artisan::call('queue:retry', ['id' => ['all']]);
        session()->flash('success', 'All failed jobs queued for retry.');
    }

    protected function checkPermission(): void
    {
        abort_unless(auth()->user()?->hasPermission('queue.retry'), 403);
    }

    public function render()
    {
        if ($this->tab === 'failed') {
            $rows = DB::table('failed_jobs')->orderByDesc('failed_at')->paginate(15);
        } else {
            $rows = DB::table('jobs')->orderByDesc('id')->paginate(15);
        }

        return view('livewire.admin.queue.jobs-table', ['rows' => $rows]);
    }
}
