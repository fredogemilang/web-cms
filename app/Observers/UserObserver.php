<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class UserObserver extends LogsActivity
{
    protected function activityName(): string
    {
        return 'user';
    }

    protected function subjectLabel(Model $model): string
    {
        return $model->name.' <'.$model->email.'>';
    }
}
