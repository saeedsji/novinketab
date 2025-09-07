<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class NarratorsExport implements FromQuery, WithHeadings, WithMapping
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
            'نام گوینده',
            'توضیحات',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    public function map($narrator): array
    {
        return [
            $narrator->name,
            $narrator->description,
            $narrator->books_count,
            Jalalian::forge($narrator->created_at)->format('Y/m/d H:i'),
        ];
    }
}
