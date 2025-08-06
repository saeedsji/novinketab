<?php

namespace App\Lib\Helper;

use App\Models\Setting;

class SettingClass
{
    public static function get($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return !empty($setting->value) ? $setting->value : $default;
    }

    public static function set($key, $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function bulkSet(array $data): void
    {
        $settingsToUpdate = [];

        foreach ($data as $key => $value) {
            $settingsToUpdate[] = ['key' => $key, 'value' => $value];
        }
        Setting::upsert($settingsToUpdate, 'key');
    }
}
