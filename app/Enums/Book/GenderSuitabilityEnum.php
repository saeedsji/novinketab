<?php

namespace App\Enums\Book;

enum GenderSuitabilityEnum: int
{
    case MAN = 1;
    case WOMAN = 2;
    case BOTH = 3;

    public function pName(): string
    {
        return match ($this) {
            self::MAN => 'مرد',
            self::WOMAN => 'زن',
            self::BOTH => 'هردو',
        };
    }
    public static function fromPersian(string $gender): self
    {
        return match (trim($gender)) {
            'مرد' => self::MAN,
            'زن' => self::WOMAN,
            default => self::BOTH,
        };
    }
}
