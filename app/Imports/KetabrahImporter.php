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
use Morilog\Jalali\Jalalian;

class KetabrahImporter implements ToCollection, WithStartRow, WithChunkReading, WithEvents
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
                $platformId = $row[0]; // Payment Id
                $bookKetabrahId = $row[1]; // Book Id
                $saleDateStr = $row[3]; // Payment Date
                $priceToman = (float)$row[4]; // Price (Toman)
                $publisherShareToman = (float)$row[5]; // Publisher Share (Toman)
                $transactionFeeToman = (float)$row[7]; // Transaction Fee (Toman)
                $discountPercent = (int)$row[9]; // Discount (%)

                if (is_null($platformId) || is_null($bookKetabrahId)) {
                    continue;
                }

                $book = Book::where('ketabrah_book_id', $bookKetabrahId)->first();

                if (!$book) {
                    $this->failedRecords++;
                    $this->failedDetails[] = ['row' => $row->toArray(), 'error' => "کتاب با شناسه کتابراه ({$bookKetabrahId}) یافت نشد."];
                    continue;
                }

                // --- Calculations & Conversions ---

                // 1. Convert Toman to Rial
                $amountRial = $priceToman * 10;
                $publisherShareRial = $publisherShareToman * 10;
                $taxRial = $transactionFeeToman * 10; // Transaction Fee is stored as tax

                // 2. Calculate discount amount from percentage
                $discountAmountRial = 0;
                if ($discountPercent > 0) {
                    $discountAmountToman = ($priceToman * $discountPercent) / 100;
                    $discountAmountRial = $discountAmountToman * 10;
                }

                // 3. Calculate platform share
                $platformShareRial = $amountRial - $publisherShareRial;

                // 4. Parse date
                $saleDate = Jalalian::fromFormat('Y-m-d H:i', $saleDateStr)->toCarbon();


                $paymentData = [
                    'import_log_id'=>$this->importLog->id,
                    'book_id' => $book->id,
                    'sale_platform' => SalesPlatformEnum::KETABRAH,
                    'sale_date' => $saleDate,
                    'amount' => (int)$amountRial,
                    'publisher_share' => (int)$publisherShareRial,
                    'platform_share' => (int)$platformShareRial,
                    'discount' => (int)$discountAmountRial,
                    'tax' => (int)$taxRial,
                ];

                $payment = Payment::updateOrCreate(
                    ['platform_id' => $platformId, 'sale_platform' => SalesPlatformEnum::KETABRAH],
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
