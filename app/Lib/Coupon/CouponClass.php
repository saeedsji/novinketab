<?php

namespace App\Lib\Coupon;


use App\Enums\Coupon\CouponType;
use App\Models\Coupon;
use App\Models\CouponUser;
use Carbon\Carbon;

class CouponClass
{
    private string $code;
    private int $user_id;
    private int $amount;
    private int|null $plan_id;
    private int|null $course_id;

    public function __construct($code, $user_id, $amount, $plan_id = null, $course_id = null)
    {
        $this->code = $code;
        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->plan_id = $plan_id;
        $this->course_id = $course_id;
    }

    public function apply(): array
    {
        $validation = $this->validation();

        if (!$validation['success']) {
            return $validation;
        }

        $discount = $this->calculateDiscount();

        return [
            'success' => true,
            'data' => [
                'amount' => $this->amount,
                'discount' => $discount,
                'finalPrice' => $this->amount - $discount,
            ],
            'message' => 'کد تخفیف با موفقیت اعمال شد',
        ];
    }

    private function validation(): array
    {
        $coupon = $this->getCoupon();

        if (!$coupon) {
            return ['success' => false, 'message' => 'کد تخفیف وجود ندارد و معتبر نیست'];
        }

        if (!$coupon->active) {
            return ['success' => false, 'message' => 'کد تخفیف وارد شده فعال نیست'];
        }
        if ($coupon->limit <= $coupon->useCount()) {
            return ['success' => false, 'message' => 'اعتبار استفاده از این کد تخفیف به پایان رسیده'];
        }

        if ($coupon->userLimit <= $this->getCouponUserCount()) {
            return ['success' => false, 'message' => 'شما از این کد تخفیف قبلا استفاده کردید'];
        }

        if ($coupon->start && $coupon->start > Carbon::now()->toDateString()) {
            return ['success' => false, 'message' => 'زمان استفاده از این کد تخفیف هنوز فرا نرسیده است'];
        }

        if ($coupon->end && $coupon->end < Carbon::now()->toDateString()) {
            return ['success' => false, 'message' => 'زمان استفاده از این کد تخفیف گذشته است'];
        }

        if (!empty($coupon->users)) {
            $users = unserialize($coupon->users);
            if (!in_array($this->user_id, $users)) {
                return ['success' => false, 'message' => 'کد تخفیف برای حساب کاربری شما معتبر نیست'];
            }
        }
        if (!empty($coupon->plans) && !empty($this->plan_id)) {
            $plans = unserialize($coupon->plans);
            if (!in_array($this->plan_id, $plans)) {
                return ['success' => false, 'message' => 'کد تخفیف برای پلن انتخاب شده معتبر نیست'];
            }
        }
        if (!empty($coupon->courses) && !empty($this->course_id)) {
            $courses = unserialize($coupon->courses);
            if (!in_array($this->course_id, $courses)) {
                return ['success' => false, 'message' => 'کد تخفیف برای آموزش انتخاب شده معتبر نیست'];
            }
        }

        if ($this->calculateDiscount() > $this->amount) {
            return ['success' => false, 'message' => ' تخفیف نمیتواند از مبلغ نهایی بیشتر باشد'];
        }

        return ['success' => true, 'message' => 'کد تخفیف معتبر است'];
    }

    private function getCoupon(): ?Coupon
    {
        return Coupon::where('code', $this->code)->first();
    }

    private function getCouponUserCount(): int
    {
        $coupon = $this->getCoupon();
        return CouponUser::where('coupon_id', $coupon->id)->where('user_id', $this->user_id)->count();
    }

    private function calculateDiscount(): int
    {
        $coupon = $this->getCoupon();
        $couponAmount = $coupon->amount;

        return match ($this->getCoupon()->type->value) {
            CouponType::percent->value => ($couponAmount / 100) * $this->amount,
            CouponType::amount->value => $couponAmount,
        };

    }
}
