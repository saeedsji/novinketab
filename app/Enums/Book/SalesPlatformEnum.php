<?php

namespace App\Enums\Book;

enum SalesPlatformEnum: int
{
    case FIDIBO = 1;
    case TAGHCHEH = 2;
    case KETABRAH = 3;
    case NAVAR = 4;
    case NOVIN_KETAB = 5;

    public function pName(): string
    {
        return match ($this) {
            self::FIDIBO => 'فیدیبو',
            self::TAGHCHEH => 'طاقچه',
            self::KETABRAH => 'کتابراه',
            self::NAVAR => 'نوار',
            self::NOVIN_KETAB => 'نوین کتاب گویا',
        };
    }

    public static function fromPersian(string $platformName): ?self
    {
        return match (trim($platformName)) {
            'فیدیبو' => self::FIDIBO,
            'طاقچه' => self::TAGHCHEH,
            'کتابراه' => self::KETABRAH,
            'نوار' => self::NAVAR,
            'نوین کتاب' => self::NOVIN_KETAB, // Adjusted for CSV
            'نوین کتاب گویا' => self::NOVIN_KETAB,
            default => null,
        };
    }
}
