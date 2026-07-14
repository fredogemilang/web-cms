<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class CptEntryObserver extends LogsActivity
{
    protected function activityName(): string
    {
        return 'entry';
    }

    protected function subjectLabel(Model $model): string
    {
        $type = $model->postType?->singular_label ?? 'entry';

        return "{$type}: ".($model->title ?? '(untitled)');
    }
}
