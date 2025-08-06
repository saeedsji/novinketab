<?php

namespace App\Lib\User;

use App\Enums\Plan\PlanType;
use App\Enums\Vip\VipAction;
use App\Enums\Vip\VipType;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vip;
use Carbon\Carbon;

class UserTimeClass
{
    private $user_id, $payment_id;

    public function __construct($user_id, $payment_id)
    {
        $this->user_id = $user_id;
        $this->payment_id = $payment_id;
    }

    public function update()
    {
        $user = User::find($this->user_id);
        $payment = Payment::find($this->payment_id);

        if ($payment->plan->type->value == PlanType::sport_and_diet->value) {
            $this->updateSportTime($user, $payment);
            $this->updateDietTime($user, $payment);
        }
        if ($payment->plan->type->value == PlanType::sport->value) {
            $this->updateSportTime($user, $payment);
        }
        if ($payment->plan->type->value == PlanType::diet->value) {
            $this->updateDietTime($user, $payment);
        }
    }


    private function updateSportTime(User $user, Payment $payment)
    {
        $sportTime = $user->sport_time > Carbon::now() ? Carbon::parse($user->sport_time) : Carbon::parse($payment->created_at);
        $newTime = $sportTime->addMonths($payment->plan->month);
        $this->vipLog(VipType::sport_time->value, $user->sport_time, $newTime);
        $user->update([
            'sport_time' => $newTime
        ]);

    }

    private function updateDietTime(User $user, Payment $payment)
    {
        $dietTime = $user->diet_time > Carbon::now() ? Carbon::parse($user->diet_time) : Carbon::parse($payment->created_at);
        $newTime = $dietTime->addMonths($payment->plan->month);
        $this->vipLog(VipType::diet_time->value, $user->diet_time, $newTime);
        $user->update([
            'diet_time' => $newTime
        ]);
    }

    private function vipLog($type, $from, $to)
    {
        Vip::create([
            'creator_id' => $this->user_id,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'action' => VipAction::success_payment,
            'type' => $type,
            'from' => $from,
            'to' => $to
        ]);
    }
}

