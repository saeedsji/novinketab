<?php

namespace App\Lib\Helper;

use Carbon\Carbon;
use Illuminate\Http\Request;

class BirthdayClass
{
    public static function days()
    {
        return ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12',
            '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25',
            '26', '27', '28', '29', '30', '31'];
    }

    public static function months()
    {
        return [
            ['number' => '01', 'name' => 'فروردین'],
            ['number' => '02', 'name' => 'اردیبهشت'],
            ['number' => '03', 'name' => 'خرداد'],
            ['number' => '04', 'name' => 'تیر'],
            ['number' => '05', 'name' => 'مرداد'],
            ['number' => '06', 'name' => 'شهریور'],
            ['number' => '07', 'name' => 'مهر'],
            ['number' => '08', 'name' => 'آبان'],
            ['number' => '09', 'name' => 'آذر'],
            ['number' => '10', 'name' => 'دی'],
            ['number' => '11', 'name' => 'بهمن'],
            ['number' => '12', 'name' => 'اسفند'],
        ];
    }

    public static function years()
    {
        $year = Carbon::now()->timestamp;
        $jalali_year = JalaliClass::jdate('Y', $year);
        return collect(range(1330, $jalali_year));
    }

    public static function birthdayToGeo($year, $month, $day)
    {
        if (!empty($year) && !empty($month) && !empty($day)) {
            return JalaliClass::jalali_to_gregorian($year, $month, $day, '-');
        }
        else {
            return null;
        }
    }

    public static function jalalianYear($birthday)
    {
        return !empty($birthday) ? JalaliClass::jdate('Y', $birthday) : null;
    }

    public static function jalalianMonth($birthday)
    {
        return !empty($birthday) ? JalaliClass::jdate('m', $birthday) : null;
    }

    public static function jalalianDay($birthday)
    {
        return !empty($birthday) ? JalaliClass::jdate('d', $birthday) : null;
    }

    public static function jalalianBirthday($birthday)
    {
        return !empty($birthday) ? JalaliClass::jdate('Y/m/d', $birthday) : null;
    }
}
