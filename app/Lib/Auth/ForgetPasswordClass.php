<?php

namespace App\Lib\Auth;

use App\Models\User;
use Exception;

class ForgetPasswordClass extends BaseAuthClass
{
    private string $user;
    private string $code;
    private string $newPassword;
    private string $field;
    private User $userModel;

    /**
     * @param string $user        Phone or email
     * @param string $code        One-time code user received
     * @param string $newPassword The new password to set
     */
    public function __construct(
        string $user,
        string $code,
        string $newPassword
    ) {
        $this->user        = $user;
        $this->code        = $code;
        $this->newPassword = $newPassword;
    }

    /**
     * Main method to reset password:
     *  1. Detect field (phone/email) or fail.
     *  2. Load user or fail.
     *  3. Check verification code or fail.
     *  4. Update password.
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->detectFieldOrFail();
        $this->loadUserOrFail();
        $this->checkVerifyCodeOrFail($this->user, $this->code);
        $this->updatePassword($this->newPassword);
    }

    /**
     * Determine if $this->user is phone or email; throws if invalid.
     * @throws Exception
     */
    private function detectFieldOrFail(): void
    {
        if ($this->isPhoneValid($this->user)) {
            $this->field = 'phone';
        } elseif ($this->isEmailValid($this->user)) {
            $this->field = 'email';
        } else {
            throw new Exception('ایمیل یا شماره موبایل را به درستی وارد کنید.');
        }
    }

    /**
     * Load the user from DB or throw an exception if not found.
     * @throws Exception
     */
    private function loadUserOrFail(): void
    {
        $user = User::where($this->field, $this->user)->first();

        if (!$user) {
            throw new Exception('کاربری با این اطلاعات یافت نشد.');
        }

        $this->userModel = $user;
    }

    /**
     * Update the user's password in the database.
     */
    private function updatePassword(string $newPassword): void
    {
        // You can use Hash::make or bcrypt to encrypt password
        $this->userModel->update([
            'password' => bcrypt($newPassword),
        ]);


        // Refresh or clear the verification code
        $this->upsertVerifyCode($this->user);

    }
}
