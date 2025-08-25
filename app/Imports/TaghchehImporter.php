<?php

namespace App\Imports;

use App\Enums\Book\SalesPlatformEnum;

// Correct Namespace
use App\Models\Book;
use App\Models\ImportLog;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;
use Morilog\Jalali\Jalalian;

class TaghchehImporter implements ToCollection, WithStartRow, WithChunkReading, WithEvents
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

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Accessing columns by their index
                $bookTitle = trim($row[0]); // عنوان
                $saleDateStr = $row[3]; // تاریخ
                $saleTimeStr = $row[4]; // ساعت
                $amountRial = (int)$row[5]; // مبلغ (ریال)
                $platformShareRial = (int)$row[7]; // سهم درگاه (ریال)
                $publisherShareRial = (int)$row[8]; // سهم ناشر (ریال)

                if (empty($bookTitle) || empty($saleDateStr) || empty($saleTimeStr)) {
                    continue;
                }

                // Remove extra whitespace from start/end and between words
                $normalizedTitle = trim(preg_replace('/\s+/', ' ', $bookTitle));

                // Standardize Arabic 'ي' and 'ك' to Persian 'ی' and 'ک'
                $normalizedTitle = str_replace(['ي', 'ك'], ['ی', 'ک'], $normalizedTitle);

                // --- START OF CORRECTION ---
                // 2. Find the book by comparing the normalized titles
                // This query ignores all spaces and half-spaces during comparison
                $book = Book::whereRaw('REPLACE(REPLACE(REPLACE(title, " ", ""), "‌", ""), " ", "") = ?', [
                    str_replace([' ', '‌'], '', $normalizedTitle)
                ])->first();
                // --- END OF CORRECTION ---


                if (!$book) {
                    $this->failedRecords++;
                    // Save only the essential identifier and the error message
                    $this->failedDetails[] = [
                        'identifier' => $normalizedTitle,
                        'error' => 'کتاب با این عنوان یافت نشد.'
                    ];
                    continue;
                }

                // 1. Combine date and time
                $fullSaleDateTimeStr = "{$saleDateStr} {$saleTimeStr}";

                // 2. Convert combined Jalali date-time to Gregorian Carbon object
                $saleDate = Jalalian::fromFormat('Y/m/d H:i', $fullSaleDateTimeStr)->toCarbon();

                // 3. Create a unique platform_id
                $platformId = "{$saleDate->timestamp}-{$book->id}-{$amountRial}";

                $paymentData = [
                    'import_log_id' => $this->importLog->id,
                    'book_id' => $book->id,
                    'sale_platform' => SalesPlatformEnum::TAGHCHEH,
                    'sale_date' => $saleDate,
                    'amount' => $amountRial,
                    'publisher_share' => $publisherShareRial,
                    'platform_share' => $platformShareRial,
                    'discount' => 0, // As requested
                    'tax' => 0, // No tax column in the file
                ];

                $payment = Payment::updateOrCreate(
                    ['platform_id' => $platformId, 'sale_platform' => SalesPlatformEnum::TAGHCHEH],
                    $paymentData
                );

                if ($payment->wasRecentlyCreated) {
                    $this->newRecords++;
                }
                else {
                    $this->updatedRecords++;
                }
            } catch (\Exception $e) {
                $this->failedRecords++;
                $this->failedDetails[] = ['row' => $row->toArray(), 'error' => 'خطای سیستمی: ' . $e->getMessage()];
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->importLog->update([
                    'new_records' => $this->newRecords,
                    'updated_records' => $this->updatedRecords,
                    'failed_records' => $this->failedRecords,
                    'details' => $this->failedDetails,
                    'status' => 'completed',
                ]);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
