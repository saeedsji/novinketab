<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportNovinKetabIds extends Command
{
    protected $signature = 'books:sync-novinketab {file : The path to the CSV file}';
    protected $description = 'Import NovinKetab IDs with simple logging for mismatches';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        // خواندن فایل و حذف هدر
        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData);

        // دریافت عنوان‌های دیتابیس
        $books = Book::select('id', 'title')->get();

        $this->info("Start processing " . count($csvData) . " records...");

        $bar = $this->output->createProgressBar(count($csvData));
        $bar->start();

        $updatedCount = 0;
        $notFoundCount = 0;

        foreach ($csvData as $row) {
            if (!isset($row[0], $row[1])) {
                continue;
            }

            $csvId = trim($row[0]);
            $csvTitleRaw = $row[1];
            $csvTitleNormalized = $this->normalizeText($csvTitleRaw);

            $bestMatch = null;
            $highestSimilarity = 0;

            foreach ($books as $book) {
                $dbTitleNormalized = $this->normalizeText($book->title);
                similar_text($csvTitleNormalized, $dbTitleNormalized, $percent);

                if ($percent > $highestSimilarity) {
                    $highestSimilarity = $percent;
                    $bestMatch = $book;
                }
            }

            // شرط تطابق (مثلا 85 درصد)
            if ($highestSimilarity >= 60 && $bestMatch) {
                DB::table('books')
                    ->where('id', $bestMatch->id)
                    ->update(['novinketab_book_id' => $csvId]);

                $updatedCount++;
            } else {
                $notFoundCount++;

                // --- لاگ ساده برای موارد پیدا نشده ---

                // 1. نمایش در ترمینال (با رنگ زرد)
                // نشان می‌دهیم که چه چیزی در اکسل بوده و نزدیک‌ترین چیزی که در دیتابیس پیدا شده چیست
                $closestTitle = $bestMatch ? $bestMatch->title : '---';
                $sim = round($highestSimilarity, 1);

                // پاک کردن خط پروگرس بار موقتا برای نمایش پیام تمیز
                $bar->clear();
                $this->warn("MISMATCH: CSV: [$csvTitleRaw] | Best DB Match: [$closestTitle] ($sim%) | ID: $csvId");
                $bar->display();

                // 2. ذخیره در فایل لاگ لاراول (storage/logs/laravel.log)
                Log::warning("NovinKetab Sync Mismatch", [
                    'csv_id' => $csvId,
                    'csv_title' => $csvTitleRaw,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->table(
            ['Total', 'Updated', 'Not Matched'],
            [[count($csvData), $updatedCount, $notFoundCount]]
        );

        $this->info("Check storage/logs/laravel.log for details.");

        return 0;
    }

    private function normalizeText($text)
    {
        $text = str_replace(['ي', 'ك'], ['ی', 'ک'], $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
