<?php

namespace App\Enums\Book;

enum ListenerTypeEnum: int
{
    case SUPERFICIAL = 1;
    case DEEP = 2;
    case SPECIALIZED = 3;

    public function pName(): string
    {
        return match ($this) {
            self::SUPERFICIAL => 'سطحی',
            self::DEEP => 'عمیق',
            self::SPECIALIZED => 'تخصصی',
        };
    }

    public static function fromPersian(string $type): self
    {
        return match (trim($type)) {
            'عمیق' => self::DEEP,
            'تخصصی' => self::SPECIALIZED,
            default => self::SUPERFICIAL,
        };
    }
}
