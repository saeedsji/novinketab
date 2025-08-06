<?php

namespace App\Lib\Auth;

use App\Models\User;
use Exception;

class PasswordAuthClass extends BaseAuthClass
{
    private string $user;
    private string $password;
    private string $field;
    private User $userModel;

    public function __construct(
        string $user,
        string $password,
    )
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Main login method:
     * 1. Validate phone/email, user existence, password, user status.
     * 2. Authenticate (web or api).
     * @throws Exception
     */
    public function login()
    {
        $this->validateOrFail();
        $this->authenticate();
    }

    /**
     * Validate all steps or throw an exception if any step fails.
     * @throws Exception
     */
    private function validateOrFail(): void
    {
        $this->detectFieldOrFail();
        $this->loadUserOrFail();
        $this->checkPasswordOrFail($this->userModel, $this->password);
        $this->checkUserStatusOrFail('id', $this->userModel->id);
    }

    /**
     * Determine whether $this->user is phone or email; throw if invalid.
     * @throws Exception
     */
    private function detectFieldOrFail(): void
    {
        if ($this->isPhoneValid($this->user)) {
            $this->field = 'phone';
        }
        elseif ($this->isEmailValid($this->user)) {
            $this->field = 'email';
        }
        else {
            throw new Exception('ایمیل یا شماره موبایل را به درستی وارد کنید.');
        }
    }

    /**
     * Load the user from the database or throw if not found.
     * @throws Exception
     */
    private function loadUserOrFail(): void
    {
        $user = User::where($this->field, $this->user)->first();
        if (!$user) {
            throw new Exception('کاربری با این ایمیل یا شماره موبایل وجود ندارد. لطفا ابتدا ثبت‌نام کنید.');
        }
        $this->userModel = $user;
    }

    private function authenticate()
    {
        $this->webLogin($this->userModel);
    }
}
