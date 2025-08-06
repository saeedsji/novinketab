<?php

namespace App\Lib\Auth;

use App\Models\User;
use Exception;

class VerifyAuthClass extends BaseAuthClass
{
    private string $user;
    private string $code;
    private string $field;

    public function __construct(string $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * 1. Validate phone/email & code & user status, or throw an exception if invalid.
     * 2. Create/update user if needed.
     * 3. Generate token and return success data.
     * @throws \Exception
     */
    public function execute(): void
    {
        $this->detectFieldTypeOrFail();
        $this->checkVerifyCodeOrFail($this->user, $this->code);
        $this->checkUserStatusOrFail($this->field, $this->user);

        $isNewUser = $this->isNewUser($this->field, $this->user);
        $user = $this->upsertUser();
        $this->webLogin($user);

        // Refresh or clear the verification code
        $this->upsertVerifyCode($this->user);
    }

    /**
     * @throws \Exception
     */
    private function detectFieldTypeOrFail(): void
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

    private function upsertUser(): User
    {
        $existingUser = User::where($this->field, $this->user)->first();
        return $existingUser
            ? $this->updateUser($existingUser)
            : $this->storeUser();
    }

    private function storeUser(): User
    {
        return User::create([
            $this->field => $this->user,
            'ip' => request()->ip(),
        ]);
    }

    private function updateUser(User $user): User
    {
        $user->update([
            'ip' => request()->ip(),
        ]);
        return $user;
    }
}
