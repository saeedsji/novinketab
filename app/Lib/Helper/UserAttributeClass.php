<?php

namespace App\Lib\Helper;

use App\Models\User;

class UserAttributeClass
{

    public static function replaceNameForSms($name)
    {
        return str_replace(' ', '-', $name);
    }

    public static function nameForSms($user_id)
    {
        $user = User::find($user_id);
        return !empty($user->name) ? str_replace(' ', '-', $user->name) : "کاربر";
    }


    public static function name($user_id)
    {
        $user = User::find($user_id);
        return !empty($user->name) ? $user->name : "کاربر";
    }
}
