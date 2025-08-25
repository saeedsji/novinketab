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
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;

class NovinKetabImporter implements ToCollection, WithStartRow, WithChunkReading, WithEvents
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
        return 2; // Skip header row
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $normalizedTitle = null;
            try {
                // Accessing columns by their index
                $platformId  = $row[0]; // order_id
                $saleDateStr = $row[2]; // order_date
                $status      = strtolower(trim($row[4])); // status
                $amountToman = (float) $row[5]; // order_total (Toman)
                $bookTitle   = $row[9]; // Product Item 1 Name

                // 1. Import only rows with "completed" status
                if ($status !== 'completed' || is_null($platformId) || is_null($bookTitle)) {
                    continue;
                }

                // 2. Find book by title (robust search)
                $normalizedTitle = trim(preg_replace('/\s+/', ' ', $bookTitle));
                $normalizedTitle = str_replace(['ي', 'ك'], ['ی', 'ک'], $normalizedTitle);

                $book = Book::whereRaw('REPLACE(REPLACE(REPLACE(title, " ", ""), "‌", ""), " ", "") = ?', [
                    str_replace([' ', '‌'], '', $normalizedTitle)
                ])->first();

                if (!$book) {
                    $this->failedRecords++;
                    $this->failedDetails[] = ['identifier' => $normalizedTitle, 'error' => 'کتاب با این عنوان یافت نشد.'];
                    continue;
                }

                // 3. Parse date (Format: 7/22/2025 18:34:14)
                $saleDate = Carbon::createFromFormat('n/j/Y H:i:s', $saleDateStr);

                // 4. Convert Toman to Rial
                $amountRial = $amountToman * 10;

                // Assumption: 70% for publisher, 30% for platform.
                // You can change these values if needed.
                $publisherShareRial = $amountRial * 0.70;
                $platformShareRial = $amountRial * 0.30;

                $paymentData = [
                    'import_log_id'=>$this->importLog->id,
                    'book_id'         => $book->id,
                    'sale_platform'   => SalesPlatformEnum::NOVIN_KETAB,
                    'sale_date'       => $saleDate,
                    'amount'          => (int) $amountRial,
                    'publisher_share' => (int) $publisherShareRial,
                    'platform_share'  => (int) $platformShareRial,
                    'discount'        => 0,
                    'tax'             => 0,
                ];

                $payment = Payment::updateOrCreate(
                    ['platform_id' => $platformId, 'sale_platform' => SalesPlatformEnum::NOVIN_KETAB],
                    $paymentData
                );

                if ($payment->wasRecentlyCreated) {
                    $this->newRecords++;
                } else {
                    $this->updatedRecords++;
                }
            } catch (\Exception $e) {
                $this->failedRecords++;
                $this->failedDetails[] = ['identifier' => $normalizedTitle ?? 'ردیف ناشناس', 'error' => 'خطای سیستمی: ' . $e->getMessage()];
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                $this->importLog->update([
                    'new_records'     => $this->newRecords,
                    'updated_records' => $this->updatedRecords,
                    'failed_records'  => $this->failedRecords,
                    'details'         => $this->failedDetails,
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
