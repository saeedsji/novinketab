<?php

namespace App\Enums\User;

enum UserAccess: int
{
    case user = 1;
    case admin = 2;

    public function pName(): string
    {
        return match ($this) {
            UserAccess::user => 'کاربر عادی',
            UserAccess::admin => 'ادمین',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            UserAccess::user => '<div class="inline-flex items-center gap-x-1.5 rounded-md bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700">
             <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
             <circle cx="3" cy="3" r="3"/></svg> کاربر عادی</div>',

            UserAccess::admin => '<div class="inline-flex items-center gap-x-1.5 rounded-md bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700">
             <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
             <circle cx="3" cy="3" r="3"/></svg>ادمین</div>',
        };
    }
}
