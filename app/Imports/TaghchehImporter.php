<?php

namespace App\Imports;

use App\Enums\Book\SalesPlatformEnum;

// Correct Namespace
use App\Models\Book;
use App\Models\ImportLog;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
                $bookTitle = trim($row[0]);
                $saleDateStr = $row[3];
                $saleTimeStr = $row[4];
                $amountRial = (int)$row[5];
                $platformShareRial = (int)$row[7];
                $publisherShareRial = (int)$row[8];

                if (empty($bookTitle) || empty($saleDateStr) || empty($saleTimeStr)) {
                    continue;
                }

                $book = $this->findBookByTitle($bookTitle);

                if (!$book) {
                    $this->failedRecords++;
                    $this->failedDetails[] = [
                        'identifier' => $bookTitle,
                        'error' => 'کتاب با این عنوان یافت نشد.'
                    ];
                    continue;
                }

                // ادامه کد مثل قبل...
                $fullSaleDateTimeStr = "{$saleDateStr} {$saleTimeStr}";
                $saleDate = Jalalian::fromFormat('Y/m/d H:i', $fullSaleDateTimeStr)->toCarbon();
                $platformId = "{$saleDate->timestamp}-{$book->id}-{$amountRial}";

                $paymentData = [
                    'import_log_id' => $this->importLog->id,
                    'book_id' => $book->id,
                    'sale_platform' => SalesPlatformEnum::TAGHCHEH,
                    'sale_date' => $saleDate,
                    'amount' => $amountRial,
                    'publisher_share' => $publisherShareRial,
                    'platform_share' => $platformShareRial,
                    'discount' => 0,
                    'tax' => 0,
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
                $this->failedDetails[] = [
                    'row' => $row->toArray(),
                    'error' => 'خطای سیستمی: ' . $e->getMessage()
                ];
            }
        }
    }

    private function normalizeTitle(string $title): string
    {
        $title = trim($title);

        // یکسان‌سازی حروف عربی ↔ فارسی
        $title = str_replace(['ي', 'ك'], ['ی', 'ک'], $title);

        // نیم‌فاصله (ZWNJ) و فاصله معمولی رو یکی کن
        $title = str_replace("\u{200C}", ' ', $title); // ZWNJ → Space
        $title = str_replace('‌', ' ', $title);        // نیم‌فاصله متداول
        $title = preg_replace('/\s+/u', ' ', $title);  // چند فاصله پشت هم = یک فاصله

        // حذف پرانتزها برای راحتی مقایسه
        $title = str_replace(['(', ')', '【', '】'], '', $title);

        return trim($title);
    }
    private function findBookByTitle(string $title)
    {
        $normalizedTitle = $this->normalizeTitle($title);

        // --- مرحله 1: جستجو در ستون taghche_title ---
        $book = Book::where('taghche_title', 'like', '%' . $title . '%')->first();
        if ($book) {
            return $book;
        }

        // --- مرحله 2: جستجو در ستون title (exact/like) ---
        $book = Book::where('title', $normalizedTitle)->first();
        if ($book) {
            return $book;
        }

        $book = Book::where('title', 'like', '%' . $normalizedTitle . '%')->first();
        if ($book) {
            return $book;
        }

        // --- مرحله 3: تطبیق substring (اکسل ⊂ دیتابیس یا برعکس) ---
        $book = Book::where(function ($q) use ($normalizedTitle) {
            $q->where('title', 'like', '%' . $normalizedTitle . '%')
                ->orWhereRaw('? like concat("%", title, "%")', [$normalizedTitle]);
        })->first();
        if ($book) {
            return $book;
        }

        // --- مرحله 4: fuzzy matching روی کل کتاب‌ها ---
        $allBooks = Book::pluck('title', 'id');
        $bestMatchId = null;
        $bestScore = 0;

        foreach ($allBooks as $id => $dbTitle) {
            $normalizedDbTitle = $this->normalizeTitle($dbTitle);

            // اگر یکی شامل دیگری بود → سریع match
            if (Str::contains($normalizedTitle, $normalizedDbTitle) ||
                Str::contains($normalizedDbTitle, $normalizedTitle)) {
                return Book::find($id);
            }

            // مقایسه درصدی
            similar_text($normalizedTitle, $normalizedDbTitle, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestMatchId = $id;
            }
        }

        if ($bestScore > 70) {
            return Book::find($bestMatchId);
        }

        return null;
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
