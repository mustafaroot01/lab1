<?php

namespace App\Models;

class Setting extends SystemSetting
{
    protected $table = 'system_settings';

    public static function get($key, $default = null)
    {
        return self::getValue($key, $default);
    }

    public static function set($key, $value)
    {
        return self::setValue($key, $value);
    }
}
