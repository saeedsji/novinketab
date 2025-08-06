<?php

namespace App\Lib\Payment;

use App\Lib\Consultant\SuccessBookClass;
use App\Lib\Course\CourseSuccessPaymentClass;
use App\Lib\Gateway\ZarinpalClass;
use App\Models\Payment;

class PaymentClass
{
    private Payment $payment;
    private string $callbackUrl;
    private string $merchantId;

    public function __construct(Payment $payment)
    {
        $this->merchantId = "a949d6af-4a97-416e-a374-0b18fcb761de";
        $this->callbackUrl = url('/verify-payment');
        $this->payment = $payment;
    }

    public function attemp()
    {
        return $this->payment->amount > 0
            ? $this->zarinpalRequest()
            : $this->noGateway();
    }

    private function noGateway()
    {
        $data = ['url' => null, 'gateway' => false];

        if (!empty($this->payment->plan_id)) {
            $successPayment = new SuccessPaymentClass($this->payment);
            $successPayment->attemp(null);
            return [
                'success' => true,
                'message' => 'خرید برنامه به صورت رایگان با موفقیت انجام شد',
                'data' => $data,
            ];
        }

        elseif (!empty($this->payment->course_id)) {
            $courseSuccessPayment = new CourseSuccessPaymentClass($this->payment);
            $courseSuccessPayment->attemp(null);
            return [
                'success' => true,
                'message' => 'خرید آموزش به صورت رایگان با موفقیت انجام شد',
                'data' => $data,
            ];
        }
        elseif (!empty($this->payment->consultant_id)) {
            $courseSuccessPayment = new SuccessBookClass($this->payment);
            $courseSuccessPayment->attemp(null);
            return [
                'success' => true,
                'message' => 'رزرو وقت مشاوره به صورت رایگان با موفقیت انجام شد',
                'data' => $data,
            ];
        }
        else {
            return [
                'success' => false,
                'message' => 'پرداخت نامعتبر است و به محصولی متصل نیست!',
                'data' => $data,
            ];
        }
    }

    private function zarinpalRequest()
    {
        $zarinpalClass = new ZarinpalClass();
        $amount = round($this->payment->amount);
        $email = $this->payment->user->email;
        $phone = $this->payment->user->phone;
        $description = 'خرید برنامه یا آموزش از فیتامون';
        $result = $zarinpalClass->request($this->merchantId, $amount, $this->callbackUrl, $description, $email, $phone);
        return isset($result["Status"]) && $result["Status"] == 100
            ? $this->success($result)
            : $this->fail($result);

    }

    private function success($result)
    {
        $this->payment->update(['token' => $result["Authority"]]);
        return [
            'success' => true,
            'message' => 'لینک پرداخت با موفقیت صادر شد',
            'data' => [
                'url' => $result["StartPay"],
                'gateway' => true,
            ],
        ];
    }

    private function fail($result)
    {
        return [
            'success' => false,
            'message' => 'مشکلی در ورود به درگاه پرداخت رخ داد لطفا مجددا تلاش کنید',
            'data' => [
                'status' => $result["Status"],
                'zarinaplMessage' => $result["Message"],
            ]
        ];
    }


}
