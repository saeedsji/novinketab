<?php

namespace App\Lib\Payment;

use App\Lib\Consultant\SuccessBookClass;
use App\Lib\Course\CourseSuccessPaymentClass;
use App\Lib\Gateway\ZarinpalClass;
use App\Models\Payment;

class VerifyPaymentClass
{
    private string $merchantId;

    public function __construct()
    {
        $this->merchantId = "a949d6af-4a97-416e-a374-0b18fcb761de";
    }

    public function verify(Payment $payment)
    {
        $zarinpalClass = new ZarinpalClass();
        $result = $zarinpalClass->verify($this->merchantId, $payment->amount);

        if (isset($result["Status"]) && $result["Status"] == 100) {

            if (!empty($payment->plan_id)) {
                $successPayment = new SuccessPaymentClass($payment);
                $successPayment->attemp($result["RefID"]);
            }

            if (!empty($payment->course_id)) {
                $courseSuccessPayment = new CourseSuccessPaymentClass($payment);
                $courseSuccessPayment->attemp($result["RefID"]);
            }
            if (!empty($payment->consultant_id)) {
                $sucessBook = new SuccessBookClass($payment);
                $sucessBook->attemp($result["RefID"]);
            }

            return [
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد',
                'status' => $result["Status"],
                'refnum' => $result["RefID"],
            ];

        }
        else if (isset($result["Status"]) && $result["Status"] == 101) {
            return [
                'success' => true,
                'message' => 'پرداخت قبلا تایید شده است',
                'status' => $result["Status"],
                'refnum' => $result["RefID"],
            ];
        }
        else {
            $failPayment = new FailPaymentClass($payment);
            $failPayment->attemp($result["Status"]);
            return [
                'success' => false,
                'message' => 'پرداخت ناموفق',
                'status' => $result["Status"],
                'refnum' => '-',
            ];
        }
    }
}
