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
use Maatwebsite\Excel\Concerns\WithStartRow; // Import this
use Maatwebsite\Excel\Events\AfterImport;
use Morilog\Jalali\Jalalian;

class FidiboImporter implements ToCollection, WithStartRow, WithChunkReading, WithEvents
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
     * Start reading the import from the second row to skip the header.
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Accessing columns by their index, not by name
                $bookId         = $row[0]; // ستون اول: شناسه کتاب
                $saleDateStr    = $row[2]; // ستون سوم: تاریخ فروش
                $saleAmount     = $row[5]; // ستون ششم: مبلغ فروش
                $publisherShare = $row[6]; // ستون هفتم: سهم ناشر

                // Skip row if essential data is missing
                if (is_null($bookId) || is_null($saleDateStr)) {
                    continue;
                }

                $book = Book::where('fidibo_book_id', $bookId)->first();

                if (!$book) {
                    $this->failedRecords++;
                    $this->failedDetails[] = ['row' => $row->toArray(), 'error' => "کتاب با شناسه فیدیبو ({$bookId}) یافت نشد."];
                    continue;
                }

                $platformId = $saleDateStr . '-' . $bookId;
                $saleDate = Carbon::parse($saleDateStr)->toDateTimeString();

                $paymentData = [
                    'import_log_id'=>$this->importLog->id,
                    'book_id'         => $book->id,
                    'sale_platform'   => SalesPlatformEnum::FIDIBO,
                    'sale_date'       => $saleDate,
                    'amount'          => (int) $saleAmount,
                    'publisher_share' => (int) $publisherShare,
                    'platform_share'  => (int) ($saleAmount - $publisherShare),
                    'discount'        => 0,
                    'tax'             => 0,
                ];

                $payment = Payment::updateOrCreate(
                    ['platform_id' => $platformId, 'sale_platform' => SalesPlatformEnum::FIDIBO],
                    $paymentData
                );

                if ($payment->wasRecentlyCreated) {
                    $this->newRecords++;
                } else {
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
