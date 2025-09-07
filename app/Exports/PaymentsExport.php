<?php

namespace App\Exports;

use App\Enums\Book\SalesPlatformEnum;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Morilog\Jalali\Jalalian;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        // The query already has all filters applied from the component
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
            'عنوان کتاب',
            'کد مالی کتاب',
            'پلتفرم فروش',
            'شناسه پلتفرم',
            'تاریخ فروش',
            'مبلغ کل (ریال)',
            'سهم ناشر (ریال)',
            'سهم پلتفرم (ریال)',
            'تخفیف (ریال)',
            'مالیات (ریال)',
        ];
    }

    /**
     * @param Payment $payment
     */
    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->book->title ?? '',
            $payment->book->financial_code ?? '',
            SalesPlatformEnum::from($payment->sale_platform)->pName(),
            $payment->platform_id,
            Jalalian::forge($payment->sale_date)->format('Y/m/d H:i'),
            $payment->amount,
            $payment->publisher_share,
            $payment->platform_share,
            $payment->discount,
            $payment->tax,
        ];
    }
}
