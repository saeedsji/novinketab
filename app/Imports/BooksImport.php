<?php

namespace App\Imports;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookPrice;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Editor;
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

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
            $categoryId = $this->getCategoryId($row[3], $row[4]);


            // 2. Get Enum values for JSON fields
            $salesPlatforms = $this->getEnumValuesFromString($row[20], SalesPlatformEnum::class, '/');
            $formats = $this->getEnumValuesFromString($row[21], BookFormatEnum::class, '/');
            // 3. Create or Update Book with CORRECT column mapping
            $book = Book::updateOrCreate(
                ['financial_code' => $row[1]],
                [
                    'title' => $row[2],
                    'category_id' => $categoryId,
                    'status' => BookStatusEnum::PUBLISHED->value,
                    'duration' => $this->normalizeDuration($row[11]),
                    'track_count' => $row[12] ?? 1,
                    'publish_date' => $this->convertToGregorianDate($row[14]),
                    'sales_platforms' => $salesPlatforms,
                    'formats' => $formats,
                    'fidibo_book_id' => $row[23],
                    'ketabrah_book_id' => $row[24],
                    'taghcheh_book_id' => $row[25],
                    'navar_book_id' => $row[26],
                    'max_discount' => !empty($row[27]) ? $row[27] * 100 : null,
                    'print_pages' => is_numeric($row[28]) ? $row[28] : null,
                    'print_price' => is_numeric($row[29]) ? $row[29] : null,
                    'suggested_price' => is_numeric($row[30]) ? $row[30] : null,
                    'description' => !empty($row[31]) ? $row[31] : null,
                    'tags' => $row[33]
                        ? explode('/', str_replace(' ', '', $row[33]))
                        : null,
                ]
            );


            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1397/01/01')->toCarbon()->toDateString(), $row[15]);
            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1398/10/20')->toCarbon()->toDateString(), $row[16]);
            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1399/10/01')->toCarbon()->toDateString(), $row[17]);
            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1400/11/01')->toCarbon()->toDateString(), $row[18]);
            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1401/04/01')->toCarbon()->toDateString(), $row[19]);
            $this->addBookPrice($book, Jalalian::fromFormat('Y/m/d', '1404/01/01')->toCarbon()->toDateString(), $row[38]);

            // 4. Handle Many-to-Many Relationships
            $this->attachItems($book, $row[6], Author::class, 'authors');
            $this->attachItems($book, $row[7], Translator::class, 'translators');
            $this->attachItems($book, $row[8], Narrator::class, 'narrators');
            $this->attachItems($book, $row[9], Editor::class, 'editors');
            $this->attachItems($book, $row[10], Publisher::class, 'publishers');
            $this->attachItems($book, $row[37], Composer::class, 'composers');

        }
    }

    private function getCategoryId($categoryString, $childCategory): ?int
    {
        if (empty($categoryString) || $categoryString === '---') {
            return null;
        }

        $category = Category::where('name', $childCategory)->first();
        if (!empty($category)) {
            return $category->id;
        }
        else {
            $parentCategory = Category::query()->firstOrCreate(
                ['name' => $categoryString, 'parent_id' => null]
            );
            $childCategory = Category::query()->create(['name' => $childCategory, 'parent_id' => $parentCategory->id]);
            return $childCategory->id;
        }
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

    /**
     * ورودی: مثل "1398" یا "بهمن 1398"
     * خروجی: تاریخ میلادی به فرمت Carbon
     */
    private function convertToGregorianDate($input)
    {
        if (empty($input)) {
            return null;
        }
        if (!preg_match('/\d/', $input)) {
            return null;
        }

        $months = [
            'فروردین' => 1,
            'اردیبهشت' => 2,
            'خرداد' => 3,
            'تیر' => 4,
            'مرداد' => 5,
            'شهریور' => 6,
            'مهر' => 7,
            'آبان' => 8,
            'آذر' => 9,
            'دی' => 10,
            'بهمن' => 11,
            'اسفند' => 12,
        ];

        $input = trim($input);

        // فقط سال
        if (preg_match('/^\d{4}$/', $input)) {
            return Jalalian::fromFormat('Y/m/d', $input . '/01/01')
                ->toCarbon()
                ->format('Y-m-d'); // تاریخ میلادی درست
        }

        // ماه + سال
        foreach ($months as $name => $num) {
            if (mb_strpos($input, $name) !== false) {
                $year = (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                return Jalalian::fromFormat('Y/m/d', $year . '/' . str_pad($num, 2, '0', STR_PAD_LEFT) . '/01')
                    ->toCarbon()
                    ->format('Y-m-d'); // خروجی میلادی
            }
        }

        return null;
    }

    private function addBookPrice(Book $book, $effective_date, $price)
    {
        if (!empty($price) && is_numeric($price)) {
            BookPrice::query()->create([
                'book_id' => $book->id,
                'user_id' => 1,
                'effective_date' => $effective_date,
                'price' => $price,
            ]);
        }
    }

    private function normalizeDuration($value): ?int
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim((string)$value);

        // هندل حالت 10--20
        if (strpos($value, '--') !== false) {
            $parts = explode('--', $value);
            return is_numeric($parts[0]) ? (int)$parts[0] : null;
        }

        // اگر فقط عدد بود
        if (is_numeric($value)) {
            return (int)$value;
        }

        // چیزای عجیب مثل "12:00:00 AM" یا رشته غیرعددی → null
        return null;
    }
}
