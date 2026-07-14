<?php

namespace Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;

class ContactDivision extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
