<?php

namespace Plugins\Posts\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'posts_settings';
    
    protected $primaryKey = 'key';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
