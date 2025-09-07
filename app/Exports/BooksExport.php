<?php

namespace App\Exports;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class BooksExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'کد مالی',
            'عنوان کتاب',
            'وضعیت',
            'دسته‌بندی',
            'نویسندگان',
            'مترجمان',
            'گویندگان',
            'ناشران',
            'آخرین قیمت (ریال)',
            'تاریخ انتشار',
            'تگ‌ها',
            'قالب‌ها',
            'پلتفرم‌های فروش',
        ];
    }

    /**
     * @param Book $book
     */
    public function map($book): array
    {
        // Helper to convert array of enums to a comma-separated string of Persian names
        $mapEnumArray = function ($array, $enumClass) {
            if (empty($array)) return '';
            return collect($array)
                ->map(fn($val) => $enumClass::tryFrom($val)?->pName())
                ->filter()
                ->join(', ');
        };

        return [
            $book->id,
            $book->financial_code,
            $book->title,
            $book->status->pName(),
            $book->category->name ?? '',
            $book->authors->pluck('name')->join(', '),
            $book->translators->pluck('name')->join(', '),
            $book->narrators->pluck('name')->join(', '),
            $book->publishers->pluck('name')->join(', '),
            $book->latestPrice->price ?? 0,
            $book->publish_date ? Jalalian::forge($book->publish_date)->format('Y/m/d') : '',
            !empty($book->tags) ? implode(', ', $book->tags ?? []) : '',
            $mapEnumArray($book->formats, BookFormatEnum::class),
            $mapEnumArray($book->sales_platforms, SalesPlatformEnum::class),
        ];
    }
}
