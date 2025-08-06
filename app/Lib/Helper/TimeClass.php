<?php

namespace App\Lib\Helper;

class TimeClass
{
    public static function formatMinute($minutes)
    {
        if ($minutes < 60) {
            return NumberCLass::enToFa($minutes) . ' دقیقه';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 0) {
            return NumberCLass::enToFa($hours) . ' ساعت و ' . NumberCLass::enToFa($remainingMinutes) . ' دقیقه';
        }

        return NumberCLass::enToFa($hours) . ' ساعت';
    }
}
