<?php

namespace App\Enums\Book;

enum BookStatusEnum: int
{
    case DRAFT = 1;
    case PRODUCED = 2;
    case PUBLISHED = 3;
    case CANCELED = 4;

    public function pName(): string
    {
        return match ($this) {
            self::DRAFT => 'پیش‌نویس',
            self::PRODUCED => 'تولید شده',
            self::PUBLISHED => 'منتشر شده',
            self::CANCELED => 'لغو شده',
        };
    }

    public static function fromPersian(string $status): self
    {
        return match (trim($status)) {
            'تولید شده' => self::PRODUCED,
            'منتشر شده' => self::PUBLISHED,
            'لغو شده' => self::CANCELED,
            'در حال تولید' => self::DRAFT, // Assuming 'in production' maps to draft
            default => self::DRAFT,
        };
    }

    /**
     * Get the corresponding badge HTML for the status.
     */
    public function badge(): string
    {
        return match ($this) {
            self::DRAFT, self::PRODUCED => '<span class="badge-ring-info">' . $this->pName() . '</span>',
            self::PUBLISHED => '<span class="badge-ring-success">' . $this->pName() . '</span>',
            self::CANCELED => '<span class="badge-ring-danger">' . $this->pName() . '</span>',
        };
    }
}
