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
        // پیشنهاد: اینجا تمام روابط لازم رو eager load کن تا N+1 نداشته باشیم
        return $this->query->with([
            'category',
            'authors',
            'translators',
            'narrators',
            'composers',
            'editors',
            'publishers',
            'latestPrice',
            'payments',
        ]);
    }

    public function headings(): array
    {
        return [
            // اطلاعات پایه
            'ID',
            'کد مالی',
            'عنوان کتاب',
            'عنوان در طاقچه',
            'وضعیت',
            'دسته‌بندی',
            'مناسب برای (جنسیت)',

            // قیمت‌ها و پارامترهای مالی کتاب
            'قیمت چاپی (ریال)',
            'قیمت پیشنهادی (ریال)',
            'حداکثر تخفیف مجاز (%)',

            // اطلاعات عددی کتاب
            'تعداد ترک صوتی',
            'مدت زمان (دقیقه)',
            'تعداد صفحات چاپی',
            'تعداد فروش برای سر به سر',

            // شناسه‌های پلتفرم‌ها
            'شناسه فیدیبو',
            'شناسه طاقچه',
            'شناسه نوار',
            'شناسه کتابراه',

            // عوامل تولید
            'نویسندگان',
            'مترجمان',
            'گویندگان',
            'آهنگسازان',
            'تدوینگران',
            'ناشران',

            // توضیحات و متادیتا
            'توضیحات',
            'تگ‌ها',
            'قالب‌ها',
            'پلتفرم‌های فروش',
            'تاریخ انتشار',
            'امتیاز کتاب',

            // قیمت و زمان‌های سیستمی
            'آخرین قیمت (ریال)',
            'تاریخ ایجاد رکورد',
            'تاریخ آخرین بروزرسانی',

            // خلاصه پرداخت‌ها / فروش‌ها
            'تعداد تراکنش‌های فروش',
            'مجموع مبلغ فروش (ریال)',
            'مجموع سهم ناشر (ریال)',
            'مجموع سهم پلتفرم فروش (ریال)',
            'مجموع تخفیف (ریال)',
            'مجموع مالیات (ریال)',
            'تاریخ اولین فروش',
            'تاریخ آخرین فروش',
        ];
    }

    /**
     * @param Book $book
     */
    public function map($book): array
    {
        // Helper: تبدیل آرایه enumها به رشته‌ی کاما جدا با نام فارسی
        $mapEnumArray = function ($array, $enumClass) {
            if (empty($array)) {
                return '';
            }

            return collect($array)
                ->map(function ($val) use ($enumClass) {
                    $enum = $enumClass::tryFrom($val);

                    return $enum?->pName() ?? $val;
                })
                ->filter()
                ->join(', ');
        };

        // پرداخت‌ها
        $payments = $book->payments ?? collect();

        $paymentsCount          = $payments->count();
        $paymentsTotalAmount    = $payments->sum('amount');
        $paymentsPublisherShare = $payments->sum('publisher_share');
        $paymentsPlatformShare  = $payments->sum('platform_share');
        $paymentsTotalDiscount  = $payments->sum('discount');
        $paymentsTotalTax       = $payments->sum('tax');

        // اولین و آخرین فروش (بر اساس sale_date)
        $paymentsWithDate = $payments->filter(fn ($p) => !empty($p->sale_date));

        $firstSale = $paymentsWithDate->sortBy('sale_date')->first();
        $lastSale  = $paymentsWithDate->sortByDesc('sale_date')->first();

        $firstSaleDateJalali = $firstSale
            ? Jalalian::forge($firstSale->sale_date)->format('Y/m/d H:i')
            : '';

        $lastSaleDateJalali = $lastSale
            ? Jalalian::forge($lastSale->sale_date)->format('Y/m/d H:i')
            : '';

        return [
            // اطلاعات پایه
            $book->id,
            $book->financial_code,
            $book->title,
            $book->taghche_title,
            $book->status?->pName() ?? '',
            $book->category->name ?? '',
            $book->gender_suitability?->pName() ?? '',

            // قیمت‌ها و پارامترهای مالی کتاب
            $book->print_price,
            $book->suggested_price,
            $book->max_discount,

            // اطلاعات عددی کتاب
            $book->track_count,
            $book->duration,
            $book->print_pages,
            $book->breakeven_sales_count,

            // شناسه‌های پلتفرم‌ها
            $book->fidibo_book_id,
            $book->taghcheh_book_id,
            $book->navar_book_id,
            $book->ketabrah_book_id,

            // عوامل تولید
            $book->authors->pluck('name')->join(', '),
            $book->translators->pluck('name')->join(', '),
            $book->narrators->pluck('name')->join(', '),
            $book->composers->pluck('name')->join(', '),
            $book->editors->pluck('name')->join(', '),
            $book->publishers->pluck('name')->join(', '),

            // توضیحات و متادیتا
            $book->description ?? '',
            !empty($book->tags) ? implode(', ', $book->tags ?? []) : '',
            $mapEnumArray($book->formats, BookFormatEnum::class),
            $mapEnumArray($book->sales_platforms, SalesPlatformEnum::class),
            $book->publish_date
                ? Jalalian::forge($book->publish_date)->format('Y/m/d')
                : '',
            $book->rate?->pName() ?? '',

            // قیمت و زمان‌های سیستمی
            $book->latestPrice->price ?? 0,
            $book->created_at
                ? Jalalian::forge($book->created_at)->format('Y/m/d H:i:s')
                : '',
            $book->updated_at
                ? Jalalian::forge($book->updated_at)->format('Y/m/d H:i:s')
                : '',

            // خلاصه پرداخت‌ها / فروش‌ها
            $paymentsCount,
            $paymentsTotalAmount,
            $paymentsPublisherShare,
            $paymentsPlatformShare,
            $paymentsTotalDiscount,
            $paymentsTotalTax,
            $firstSaleDateJalali,
            $lastSaleDateJalali,
        ];
    }
}
