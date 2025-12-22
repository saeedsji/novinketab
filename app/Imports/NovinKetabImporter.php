<?php

namespace App\Imports;

use App\Enums\Book\SalesPlatformEnum;
use App\Models\Book;
use App\Models\ImportLog;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class NovinKetabImporter implements ToCollection, WithHeadingRow, WithChunkReading, WithEvents
{
    private ImportLog $importLog;
    private int $newRecords = 0;
    private int $updatedRecords = 0;
    private int $failedRecords = 0;
    private array $failedDetails = [];

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // اطمینان از وجود order_id
                // (کتابخانه Excel هدرها را به صورت snake_case تبدیل می‌کند)
                if (!isset($row['order_id'])) {
                    continue;
                }

                $orderId = $row['order_id'];

                // دریافت تاریخ پرداخت یا تاریخ سفارش
                // اولویت با paid_date است
                $dateStr = $row['paid_date'] ?? $row['order_date'] ?? null;

                if (empty($dateStr)) {
                    continue; // اگر تاریخی نیست، احتمالا پرداخت نشده
                }

                try {
                    // پارس کردن تاریخ (فرمت هایی مثل 6/13/2025 15:34)
                    $saleDate = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $this->failedRecords++;
                    $this->failedDetails[] = [
                        'order_id' => $orderId,
                        'error' => "فرمت تاریخ نامعتبر: $dateStr"
                    ];
                    continue;
                }

                // حلقه برای بررسی آیتم‌های مختلف در یک سطر (Product Item 1, 2, 3, ...)
                // فرض می‌کنیم حداکثر 20 آیتم در یک سفارش ممکن است باشد
                for ($i = 1; $i <= 4; $i++) {
                    $idKey = "product_item_{$i}_id";
                    $totalKey = "product_item_{$i}_total";

                    // اگر ستون ID برای این شماره آیتم وجود نداشت، یعنی به انتهای لیست اقلام رسیدیم
                    if (!$row->has($idKey)) {
                        break;
                    }

                    $bookId = $row[$idKey];

                    // اگر ستون هست اما مقدارش خالیه (مثلا آیتم دوم نداره)، برو بعدی یا قطع کن
                    if (empty($bookId)) {
                        continue;
                    }

                    // پیدا کردن کتاب در دیتابیس
                    $book = Book::where('novinketab_book_id', $bookId)->first();

                    if (!$book) {
                        $this->failedRecords++;
                        $this->failedDetails[] = [
                            'order_id' => $orderId,
                            'row_item' => $i,
                            'book_id_csv' => $bookId,
                            'error' => "کتاب با شناسه نوین کتاب ({$bookId}) در سیستم یافت نشد."
                        ];
                        continue;
                    }

                    // محاسبه مبلغ
                    // حذف کاما و کاراکترهای غیر عددی
                    $amountRaw = preg_replace('/[^0-9]/', '', $row[$totalKey] ?? 0);
                    // تبدیل تومان به ریال (ضرب در 10)
                    $amountRials = (int)$amountRaw * 10;

                    // ساخت شناسه یکتا برای این پرداخت
                    // ترکیب: Novin-{شماره سفارش}-{شناسه کتاب}
                    $platformUniqueId = "novin-{$orderId}-{$bookId}";

                    $paymentData = [
                        'import_log_id'   => $this->importLog->id,
                        'book_id'         => $book->id,
                        'sale_platform'   => SalesPlatformEnum::NOVIN_KETAB,
                        'sale_date'       => $saleDate->toDateTimeString(),
                        'amount'          => $amountRials,
                        'publisher_share' => $amountRials, // طبق درخواست: سهم ناشر برابر با کل مبلغ
                        'platform_share'  => 0, // چون همه مبلغ سهم ناشر است
                        'discount'        => 0,
                        'tax'             => 0,
                    ];

                    $payment = Payment::updateOrCreate(
                        [
                            'platform_id'   => $platformUniqueId,
                            'sale_platform' => SalesPlatformEnum::NOVIN_KETAB
                        ],
                        $paymentData
                    );

                    if ($payment->wasRecentlyCreated) {
                        $this->newRecords++;
                    } else {
                        $this->updatedRecords++;
                    }
                }

            } catch (\Exception $e) {
                $this->failedRecords++;
                $this->failedDetails[] = [
                    'row_data' => $row->toArray(),
                    'error' => 'خطای سیستمی: ' . $e->getMessage()
                ];
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->importLog->update([
                    'new_records'     => $this->newRecords,
                    'updated_records' => $this->updatedRecords,
                    'failed_records'  => $this->failedRecords,
                    'details'         => $this->failedDetails, // لاراول آرایه را به JSON تبدیل می‌کند (اگر کست شده باشد)
                    'status'          => 'completed',
                ]);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
