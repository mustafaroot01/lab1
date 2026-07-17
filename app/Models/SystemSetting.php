<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue($key, $default = null)
    {
        return Cache::rememberForever("sys_setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue($key, $value)
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value]
        );

        Cache::forget("sys_setting_{$key}");

        return self::getValue($key);
    }

    public static function getBoolean($key, $default = true)
    {
        $val = self::getValue($key, $default ? 'true' : 'false');
        return in_array(strtolower((string) $val), ['1', 'true', 'yes', 'on']);
    }
}
