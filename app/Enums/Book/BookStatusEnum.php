<?php

namespace App\Enums\Book;

enum BookStatusEnum: int
{
    case PRODUCED = 1;
    case PUBLISHED = 2;
    case CANCELED = 3;
    case SHARED = 4;
    case CUSTOM = 5;
    case OUTSOURCE = 6;

    public function pName(): string
    {
        return match ($this) {
            self::PRODUCED => 'تولید شده',
            self::PUBLISHED => 'منتشر شده',
            self::CANCELED => 'لغو شده',
            self::SHARED => 'تولید مشارکتی',
            self::CUSTOM => 'تولید سفارشی',
            self::OUTSOURCE => 'واگذار شده',
        };
    }

    /**
     * Get the corresponding badge HTML for the status.
     */
    public function badge(): string
    {
        return match ($this) {
            self::PRODUCED => '<span class="badge-ring-info">' . $this->pName() . '</span>',
            self::PUBLISHED => '<span class="badge-ring-success">' . $this->pName() . '</span>',
            self::CANCELED => '<span class="badge-ring-danger">' . $this->pName() . '</span>',
            self::SHARED => '<span class="badge-ring-info">' . $this->pName() . '</span>',
            self::CUSTOM => '<span class="badge-ring-info">' . $this->pName() . '</span>',
            self::OUTSOURCE => '<span class="badge-ring-info">' . $this->pName() . '</span>',
        };
    }
}
