<?php

namespace App\Exports;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Builder; // Import Builder
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class PublishersExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    /**
     * The constructor now accepts a pre-built query builder instance.
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        // Simply return the query that was passed into the constructor.
        return $this->query;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'نام ناشر',
            'درصد سهم ناشر',
            'تعداد کتاب‌ها',
            'تاریخ ثبت',
        ];
    }

    /**
     * @param Publisher $publisher
     * @return array
     */
    public function map($publisher): array
    {
        return [
            $publisher->name,
            $publisher->share_percent . '%',
            $publisher->books_count,
            Jalalian::forge($publisher->created_at)->format('Y/m/d H:i'),
        ];
    }
}
