<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class AuthorsExport implements FromQuery, WithHeadings, WithMapping
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
            'نام نویسنده',
            'توضیحات',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    public function map($author): array
    {
        return [
            $author->name,
            $author->description,
            $author->books_count,
            Jalalian::forge($author->created_at)->format('Y/m/d H:i'),
        ];
    }
}
