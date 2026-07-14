<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable observer base — concrete observers extend and override
 * `activityName()` + `subjectLabel()`. Hooks 3 events: created, updated, deleted.
 */
abstract class LogsActivity
{
    abstract protected function activityName(): string;

    protected function subjectLabel(Model $model): string
    {
        return $model->title ?? $model->name ?? $model->getTable().' #'.$model->getKey();
    }

    public function created(Model $model): void
    {
        $this->logger()->log(
            "{$this->activityName()}.created",
            $model,
            "Created {$this->activityName()} '{$this->subjectLabel($model)}'",
        );
    }

    /** Fields whose changes alone shouldn't trigger an "updated" entry (noise). */
    protected array $ignoredFields = ['updated_at', 'last_login_at', 'remember_token', 'password_changed_at'];

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $meaningful = array_diff(array_keys($changes), $this->ignoredFields);

        if (empty($meaningful)) {
            return;
        }

        $this->logger()->logModelChanges(
            "{$this->activityName()}.updated",
            $model,
            "Updated {$this->activityName()} '{$this->subjectLabel($model)}'",
        );
    }

    public function deleted(Model $model): void
    {
        $this->logger()->log(
            "{$this->activityName()}.deleted",
            $model,
            "Deleted {$this->activityName()} '{$this->subjectLabel($model)}'",
        );
    }

    public function restored(Model $model): void
    {
        $this->logger()->log(
            "{$this->activityName()}.restored",
            $model,
            "Restored {$this->activityName()} '{$this->subjectLabel($model)}'",
        );
    }

    protected function logger(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}
