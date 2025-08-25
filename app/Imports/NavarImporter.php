<?php

namespace App\Imports;

use App\Enums\Book\SalesPlatformEnum;
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

class NavarImporter implements ToCollection, WithStartRow, WithChunkReading, WithEvents
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
                $bookNavarId = $row[3];
                $saleDateStr = $row[4];
                $saleAmount = $row[6];
                $publisherShare = $row[7];
                $discountAmount = $row[8];
                $taxAmount = $row[9];
                $platformShare = $row[11];

                if (is_null($bookNavarId) || is_null($saleDateStr)) {
                    continue;
                }

                $book = Book::where('navar_book_id', $bookNavarId)->first();

                if (!$book) {
                    $this->failedRecords++;
                    $this->failedDetails[] = ['row' => $row->toArray(), 'error' => "کتاب با شناسه نوار ({$bookNavarId}) یافت نشد."];
                    continue;
                }

                $platformId = "{$saleDateStr}-{$bookNavarId}-{$saleAmount}-{$publisherShare}";
                $saleDate = Jalalian::fromFormat('Y/m/d', $saleDateStr)->toCarbon();


                $paymentData = [
                    'import_log_id'=>$this->importLog->id,
                    'book_id' => $book->id,
                    'sale_platform' => SalesPlatformEnum::NAVAR,
                    'sale_date' => $saleDate,
                    'amount' => (int)$saleAmount,
                    'publisher_share' => (int)$publisherShare,
                    'platform_share' => (int)$platformShare,
                    'discount' => (int)$discountAmount,
                    'tax' => (int)$taxAmount,
                ];

                $payment = Payment::updateOrCreate(
                    ['platform_id' => $platformId, 'sale_platform' => SalesPlatformEnum::NAVAR],
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
