<?php

namespace App\Enums\Book;

enum BookFormatEnum: int
{
    case AUDIO = 1;
    case EBOOK = 2;
    case PRINTED = 3;

    public function pName(): string
    {
        return match ($this) {
            self::AUDIO => 'صوتی',
            self::EBOOK => 'الکترونیکی',
            self::PRINTED => 'چاپی',
        };
    }

    public static function fromPersian(string $formatName): ?self
    {
        return match (trim($formatName)) {
            'دیجیتال صوتی' => self::AUDIO,
            'صوتی' => self::AUDIO,
            'الکترونیکی' => self::EBOOK,
            'چاپی' => self::PRINTED,
            default => null,
        };
    }
}
