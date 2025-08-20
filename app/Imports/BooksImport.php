<?php

namespace App\Imports;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\ListenerTypeEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;

class BooksImport implements ToCollection, WithStartRow
{
    /**
     * Skips the first row (header).
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
            // Skip empty rows or rows without a financial code
            if ($row->filter()->isEmpty() || empty($row[1])) {
                continue;
            }

            Log::info('Processing row for financial code: ' . $row[1]);
            Log::info('category: ' . $row[3]);

            // 1. Handle Categories (Nested)
            $categoryId = $this->getCategoryId($row[3]);


            // 2. Get Enum values for JSON fields
            $salesPlatforms = $this->getEnumValuesFromString($row[23], SalesPlatformEnum::class, '/');
            $formats = $this->getEnumValuesFromString($row[26], BookFormatEnum::class, '/');
            $maxDiscount = !empty($row[38]) ? $row[38] * 100 : null;


            // 3. Create or Update Book with CORRECT column mapping
            $book = Book::updateOrCreate(
                ['financial_code' => $row[1]],
                [
                    'title' => $this->cleanUtf8String($row[2]),
                    'category_id' => $categoryId,
                    'status' => BookStatusEnum::PRODUCED->value,
                    'track_count' => is_numeric($row[13]) ? $row[13] : null,
                    'sales_platforms' => $salesPlatforms,
                    'formats' => $formats,
                    'listener_type' => !empty($row[28]) && $row[28] !== '---' ? ListenerTypeEnum::fromPersian($row[28]) : null,
                    'author_rate' => $this->validateRate($row[29]),
                    'narrator_rate' => $this->validateRate($row[30]),
                    'editor_composer_rate' => $this->validateRate($row[31]),
                    'translator_rate' => $this->validateRate($row[32]),
                    'fidibo_book_id' => $row[34] !== '---' ? $row[34] : null,
                    'ketabrah_book_id' => $row[35] !== '---' ? $row[35] : null,
                    'taghcheh_book_id' => $row[36] !== '---' ? $row[36] : null,
                    'navar_book_id' => $row[37] !== '---' ? $row[37] : null,
                    'max_discount' => $maxDiscount,
                    'description' => !empty($row[43]) ? $row[43] : null,
                ]
            );

            // 4. Handle Many-to-Many Relationships
            $this->attachItems($book, $row[5], Author::class, 'authors');
            $this->attachItems($book, $row[6], Translator::class, 'translators');
            $this->attachItems($book, $row[7], Narrator::class, 'narrators');
            $this->attachItems($book, $row[8], Composer::class, 'composers');
            $this->attachItems($book, $row[9], Publisher::class, 'publishers');
        }
    }

    private function getCategoryId(?string $categoryString): ?int
    {
        if (empty($categoryString) || $categoryString === '---') {
            return null;
        }
        $categories = array_map('trim', explode('،', $categoryString));
        $parentId = null;
        foreach ($categories as $categoryName) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName, 'parent_id' => $parentId]
            );
            $parentId = $category->id;
        }
        return $parentId;
    }

    private function getEnumValuesFromString(?string $enumString, string $enumClass, string $delimiter): ?array
    {
        if (empty($enumString) || $enumString === '---') {
            return null;
        }

        $items = explode($delimiter, $enumString);
        $enumValues = [];
        foreach ($items as $item) {
            $enumCase = $enumClass::fromPersian(trim($item));
            if ($enumCase) {
                $enumValues[] = $enumCase->value;
            }
        }
        return !empty($enumValues) ? $enumValues : null;
    }

    private function attachItems(Book $book, ?string $names, string $modelClass, string $relation): void
    {
        if (empty($names) || $names === '---') {
            $book->$relation()->sync([]); // Detach all if cell is empty
            return;
        }
        $pattern = '/\s*،\s*|\s+و\s+|\s*\/\s*/u';
        // Splits by Persian comma, English comma, and the word 'و'
        $items = preg_split($pattern, $names, -1, PREG_SPLIT_NO_EMPTY);

        $itemIds = [];
        foreach ($items as $itemName) {
            $item = $modelClass::firstOrCreate(['name' => $itemName]);
            $itemIds[] = $item->id;
        }
        // Sync ensures the book only has the relationships from the current row
        $book->$relation()->sync($itemIds);
    }

    private function cleanUtf8String(?string $string): ?string
    {
        if ($string === null) {
            return null;
        }
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    private function validateRate($value): int
    {
        if (!is_numeric($value)) {
            return 0;
        }
        $rate = (int)$value;
        return ($rate >= 1 && $rate <= 5) ? $rate : 0;
    }
}
