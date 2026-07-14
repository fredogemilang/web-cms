<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class FreeEmailDomain extends Model
{
    protected $fillable = ['domain', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
