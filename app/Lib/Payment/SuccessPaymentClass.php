<?php

namespace App\Lib\Payment;


use App\Enums\Payment\PaymentStatus;
use App\Lib\Helper\UserAttributeClass;
use App\Lib\Sms\KavenegarSmsClass;
use App\Lib\Sms\SmsClass;

use App\Lib\User\UserTimeClass;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Payment;


class SuccessPaymentClass
{
    private Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function attemp($bankRef)
    {
        $this->updatePayment($bankRef);
        $this->updateTime();
        $this->updateCoupon();
        $this->alert();

    }

    private function updatePayment($bankRef)
    {
        $this->payment->update([
            'status' => PaymentStatus::success->value,
            'bankRef' => $bankRef,
            'pay_at' => now()
        ]);
    }

    private function updateTime()
    {
        $userTimeClass = new UserTimeClass($this->payment->user_id, $this->payment->id);
        $userTimeClass->update();
    }

    private function updateCoupon()
    {
        if (!empty($this->payment->coupon)) {
            $coupon = Coupon::where('code', $this->payment->coupon)->first();
            CouponUser::create([
                'user_id' => $this->payment->user_id,
                'coupon_id' => $coupon->id,
            ]);
        }

    }

    private function alert()
    {
        $phone = $this->payment->user->phone;
        if (!empty($phone)) {
            $smsClass = new SmsClass(new KavenegarSmsClass());
            $name = UserAttributeClass::nameForSms($this->payment->user_id);
            $title = UserAttributeClass::replaceNameForSms($this->payment->roadmap->title);
            $smsClass->pattern($phone, 'karazma-success-payment', $name, $title);

        }


    }
}
