<?php

namespace App\Lib\Payment;

use App\Enums\Payment\PaymentStatus;
use App\Models\Payment;

class FailPaymentClass
{
    private Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function attemp($status)
    {
        $this->updateState($status);
    }

    private function updateState($status)
    {
        $state = $this->getState($status);
        $this->payment->update(['state' => $state]);
    }

    private function getState($status)
    {
        return $status == -51 ? PaymentStatus::cancel->value : PaymentStatus::fail->value;
    }
}
