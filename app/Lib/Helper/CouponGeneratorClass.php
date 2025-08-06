<?php

namespace App\Lib\Helper;

use App\Enums\Coupon\CouponType;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponGeneratorClass
{
    public function generate($amount, $end, $source, $first, $description = 'ساخت کوپن به صورت اتوماتیک')
    {
        $code = $this->getCode($first);
        return Coupon::create([
            'code' => $code,
            'users' => null,
            'limit' => 30,
            'userLimit' => 1,
            'type' => CouponType::percent->value,
            'source' => $source,
            'amount' => $amount,
            'start' => Carbon::now(),
            'end' => Carbon::now()->addDays($end),
            'description' => $description
        ]);
    }

    private function getCode($first)
    {
        $randStr = $this->generateRandomString(5);
        return $first . $randStr;
    }

    private function generateRandomString($length = 25)
    {
        $characters = '23456789abcdefghkmnpqrstuwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
