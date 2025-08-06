<?php

namespace App\Lib\Auth;

use App\enums\user\UserStatus;
use App\Models\User;
use App\Models\Verify;
use Exception;
use Illuminate\Support\Facades\Hash;

class BaseAuthClass
{
    /**
     * Validates a phone number: should be "0" followed by 10 digits.
     */
    protected function isPhoneValid(string $user): bool
    {
        return (bool)preg_match("/^0\d{10}$/", $user);
    }

    /**
     * Validates an email using PHP's filter_var.
     */
    protected function isEmailValid(string $user): bool
    {
        return (bool)filter_var($user, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Creates or updates a verification code for the given subject (phone/email).
     * Returns the newly generated code.
     */
    protected function upsertVerifyCode(string $subject): int
    {
        $code = rand(1000, 9999);
        Verify::updateOrCreate(
            ['subject' => $subject],
            ['code' => encrypt($code)]
        );

        return $code;
    }

    /**
     * Checks if there's an existing user with the given field/value.
     * Returns true if user does NOT exist => new user.
     */
    protected function isNewUser(string $field, string $value): bool
    {
        $user = User::where($field, $value)->first();
        return empty($user);
    }

    /**
     * Checks if the user is active. Throws an exception if not active.
     * If user does not exist, we do nothing here (treated as "no error").
     * @throws Exception
     */
    protected function checkUserStatusOrFail(string $field, string $value): void
    {
        $user = User::where($field, $value)->first();
        if (!empty($user)) {
            if ($user->status->value != UserStatus::active->value) {
                throw new Exception('حساب کاربری شما غیر فعال است لطفا با پشتیبانی تماس بگیرید');
            }
        }
    }

    /**
     * Checks if the given code matches the stored verification code.
     * Throws an exception if verification fails.
     * @throws Exception
     */
    protected function checkVerifyCodeOrFail(string $subject, string $code): void
    {
        $verify = Verify::where('subject', $subject)->first();
        if (empty($verify) || decrypt($verify->code) != $code) {
            throw new Exception('کد یکبار مصرف صحیح نیست.');
        }
    }

    /**
     * Checks if the user's password is set and correct.
     * Throws an exception if it’s unset or incorrect.
     * @throws Exception
     */
    protected function checkPasswordOrFail(User $user, string $password): void
    {
        if (empty($user->password)) {
            throw new Exception('حساب کاربری فاقد رمز عبور است.');
        }

        if (!Hash::check($password, $user->password)) {
            throw new Exception('اطلاعات ورود صحیح نیست.');
        }
    }

    /**
     * Logs in via the web guard (session-based).
     */
    protected function webLogin(User $user): void
    {
        auth()->loginUsingId($user->id);
    }
}
