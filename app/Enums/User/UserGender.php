<?php

namespace App\Enums\User;

enum UserGender: int {
    case man = 1;
    case woman = 2;
    public function pName(): string {
        return match ($this) {
            UserGender::man => 'مرد',
            UserGender::woman => 'زن',
        };
    }
}
