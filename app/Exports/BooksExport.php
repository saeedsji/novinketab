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
            'کد مالی',
            'عنوان',
            'دسته بندی سطح 1',
            'دسته بندی سطح 2',
            'وضعیت',
            'نویسنده',
            'مترجم',
            'گوینده',
            'انتخاب موسیقی',
            'تدوینگران',
            'ناشر',
            'مدت  زمان',
            'تعداد ترک',
            'تاریخ انتشار',
            'محل فروش',
            'قالب',
            'Novin Ketab',
            'Fidibo',
            'Ketabrah',
            'Taghcheh',
            'Navar',
            'ماکزیمم تخفیف اعلامی',
            'تعداد صفحات کتاب چاپی',
            'قیمت کتاب چاپی',
            'قیمت پیشنهادی',
            'توضیحات',
            'نقطه سر به سر تعداد فروش',
            'تگ ها (اقتباس از - جوایز)' ,
            'جنسیت گوینده',
            'آخرین قیمت',

            // --- ستون‌های باقی‌مانده از کد اصلی (که در اکسل نبودند) ---
            'عنوان در طاقچه',
            'امتیاز کتاب',
            'تاریخ ایجاد کتاب در پنل',
            'تاریخ آخرین بروزرسانی',
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
            $book->financial_code, // کد مالی
            $book->title, // عنوان
            $book->category->parent->name ?? '', // دسته بندی سطح 1
            $book->category->name ?? '', // دسته بندی سطح 2
            $book->status?->pName() ?? '', // وضعیت
            $book->authors->pluck('name')->join(', '), // نویسنده
            $book->translators->pluck('name')->join(', '), // مترجم
            $book->narrators->pluck('name')->join(', '), // گوینده
            $book->composers->pluck('name')->join(', '), // آهنگساز
            $book->editors->pluck('name')->join(', '), // تدوینگران
            $book->publishers->pluck('name')->join(', '), // ناشر

            $book->duration, // مدت  زمان
            $book->track_count, // تعداد ترک
            $book->publish_date ? Jalalian::forge($book->publish_date)->format('Y/m/d') : '', // تاریخ انتشار
            $mapEnumArray($book->sales_platforms, SalesPlatformEnum::class), // محل فروش
            $mapEnumArray($book->formats, BookFormatEnum::class), // قالب
            $book->novinketab_book_id, // NovinKetab
            $book->fidibo_book_id, // Fidibo
            $book->ketabrah_book_id, // Ketabrah
            $book->taghcheh_book_id, // Taghcheh
            $book->navar_book_id, // Navar
            $book->max_discount, // ماکزیمم تخفیف اعلامی به فیدیبو
            $book->print_pages, // تعداد صفحات کتاب چاپی
            $book->print_price, // قیمت کتاب چاپی
            $book->suggested_price, // قیمت پیشنهادی
            $book->description ?? '', // توضیحات
            $book->breakeven_sales_count, // نقطه سر به سر تعداد فروش
            !empty($book->tags) ? implode(', ', $book->tags ?? []) : '', // Tags
            $book->gender_suitability?->pName() ?? '', // مناسب کدام جنسیت
            $book->latestPrice->price ?? 0, // قیمت ابتدای 1404 (جایگذاری با آخرین قیمت موجود)

            // --- نگاشت داده‌های باقی‌مانده در انتهای فایل ---
            $book->taghche_title,
            $book->rate?->pName() ?? '', // امتیاز
            $book->created_at ? Jalalian::forge($book->created_at)->format('Y/m/d H:i:s') : '',
            $book->updated_at ? Jalalian::forge($book->updated_at)->format('Y/m/d H:i:s') : '',
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
