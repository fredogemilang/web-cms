<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLevel extends Model
{
    protected $fillable = ['name', 'level', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
