<?php

namespace App\Lib\Auth;

use App\Lib\Sms\KavenegarSmsClass;
use App\Lib\Sms\SmsClass;
use App\Mail\VerifyEmail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginAuthClass extends BaseAuthClass
{
    private string $user;  // e.g., phone or email
    private string $field; // 'phone' or 'email'

    public function __construct(string $user)
    {
        $this->user = $user;
    }

    /**
     * Main method:
     * 1. Detect phone/email or throw exception if invalid.
     * 2. Send verification code and return success array.
     * @throws Exception
     */
    public function execute(): array
    {
        $this->detectFieldTypeOrFail();

        // Send code if valid
        return $this->sendVerificationCode();
    }

    /**
     * Determine if $this->user is phone or email, or throw if invalid.
     * @throws Exception
     */
    private function detectFieldTypeOrFail(): void
    {
        if ($this->isPhoneValid($this->user)) {
            $this->field = 'phone';
            return;
        }

        if ($this->isEmailValid($this->user)) {
            $this->field = 'email';
            return;
        }

        // If none of the above conditions are met, it's invalid input
        throw new Exception('ایمیل یا شماره موبایل را به درستی وارد کنید.');
    }

    /**
     * Sends the verification code (SMS or Email) based on $this->field.
     */
    private function sendVerificationCode(): array
    {
        ($this->field === 'phone')
            ? $this->sendSmsCode()
            : $this->sendEmailCode();

        return ['field' => $this->field,];
    }

    private function sendSmsCode(): void
    {
        $code = $this->upsertVerifyCode($this->user);
        $smsClass = new SmsClass(new KavenegarSmsClass());
        $smsClass->pattern($this->user, 'login', $code);
    }

    private function sendEmailCode(): void
    {
        try {
            $code = $this->upsertVerifyCode($this->user);
            Mail::to($this->user)->send(new VerifyEmail($code));
        } catch (\Exception $e) {
            // Log the email failure; handle it as needed
            Log::alert('Email not sent: ' . $e->getMessage());
        }
    }
}
