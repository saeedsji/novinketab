<?php

namespace App\Lib\Payment;

use App\Enums\Payment\PaymentMethod;
use App\Enums\Payment\PaymentStatus;
use App\Lib\Coupon\CouponClass;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Plan;

class PaymentStoreClass
{

    private int $user_id;
    private int|null $plan_id;
    private int|null $course_id;
    private string|null $coupon;


    public function __construct($user_id, $plan_id, $course_id, $coupon = null)
    {
        $this->user_id = $user_id;
        $this->plan_id = $plan_id;
        $this->course_id = $course_id;
        $this->coupon = $coupon;
    }

    public function attemp()
    {
        $validation = $this->validation();
        if (!$validation['success'])
            return $validation;

        $payment = $this->store();

        return [
            'success' => true,
            'message' => 'اطلاعات پرداخت با موفقیت ذخیره شد',
            'payment' => $payment
        ];


    }

    private function store()
    {
        $discount = $this->getDiscount();
        $amount = $this->getPrice() - $discount;
        return Payment::create([
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'course_id' => $this->course_id,
            'amount' => $amount,
            'discount' => $discount,
            'status' => PaymentStatus::waiting->value,
            'method' => PaymentMethod::zarinpal->value,
            'coupon' => $this->coupon,
        ]);

    }

    private function validation()
    {

        $checkCoupon = $this->checkCoupon();
        if (!$checkCoupon['success'])
            return $checkCoupon;

        if (!empty($this->plan_id) && !empty($this->course_id)) {
            return ['success' => false, 'message' => 'امکان ارسال پلن و دوره آموزشی به صورت همزمان وجود ندارد!'];
        }

        return ['success' => true, 'message' => 'اعتبار سنجی موفق'];
    }


    private function checkCoupon()
    {
        return empty($this->coupon) ? ['success' => true, 'message' => 'بدون کد تخفیف'] : $this->applyCoupon();
    }


    private function getDiscount()
    {
        return !empty($this->coupon) ? $this->calcDiscount() : 0;
    }

    private function calcDiscount()
    {
        $applyCoupon = $this->applyCoupon();
        return $applyCoupon['data']['discount'];
    }


    private function applyCoupon()
    {
        $couponClass = new CouponClass($this->coupon, $this->user_id, $this->getPrice(), $this->plan_id, $this->course_id);
        return $couponClass->apply();
    }

    private function getPrice()
    {
        if (!empty($this->plan_id)) {
            return Plan::find($this->plan_id)->finalPrice();
        }
        if (!empty($this->course_id)) {
            return Course::find($this->course_id)->finalPrice();
        }
        else {
            abort(403, 'قیمت نامعتبر');
        }
    }


}
