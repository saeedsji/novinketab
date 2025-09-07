<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class ComposersExport implements FromQuery, WithHeadings, WithMapping
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
            'نام آهنگساز',
            'توضیحات',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    public function map($composer): array
    {
        return [
            $composer->name,
            $composer->description,
            $composer->books_count,
            Jalalian::forge($composer->created_at)->format('Y/m/d H:i'),
        ];
    }
}
