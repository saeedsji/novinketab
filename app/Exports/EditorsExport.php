<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EditorsExport implements FromQuery, WithHeadings
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->query->select('name', 'description', 'created_at');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'نام ویراستار',
            'توضیحات',
            'تاریخ ثبت',
        ];
    }
}
