<?php

namespace App\Enums\User;

enum UserStatus: int
{
    case active = 1;
    case deactive = 2;

    public function pName(): string
    {
        return match ($this) {
            UserStatus::active => 'فعال',
            UserStatus::deactive => 'غیرفعال',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            UserStatus::active => '<div class="inline-flex items-center gap-x-1.5 rounded-md bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700">
             <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
             <circle cx="3" cy="3" r="3"/></svg> فعال</div>',

            UserStatus::deactive => '<div class="inline-flex items-center gap-x-1.5 rounded-md bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700">
             <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
             <circle cx="3" cy="3" r="3"/></svg> غیر فعال</div>',
        };
    }
}
