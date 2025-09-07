<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class TranslatorsExport implements FromQuery, WithHeadings, WithMapping
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
            'نام مترجم',
            'توضیحات',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    public function map($translator): array
    {
        return [
            $translator->name,
            $translator->description,
            $translator->books_count,
            Jalalian::forge($translator->created_at)->format('Y/m/d H:i'),
        ];
    }
}
