<?php

namespace App\Enums\User;

enum UserType: int
{
    case normal = 1;

    public function pName(): string
    {
        return match ($this) {
            UserType::normal => 'کاربر عادی',
        };
    }
}
