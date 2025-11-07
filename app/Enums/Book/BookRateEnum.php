<?php

namespace App\Enums\Book;

enum BookRateEnum: int
{
    case ONE = 1;
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;

    public function pName(): string
    {
        return match ($this) {
            self::ONE => '۱',
            self::TWO => '۲',
            self::THREE => '۳',
            self::FOUR => '۴',
            self::FIVE => '۵'
        };
    }
}
