<?php


namespace App\Lib\Analytics;
use App\Enums\Book\GenderSuitabilityEnum;
use App\Enums\Book\ListenerTypeEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Publisher;
use App\Models\Narrator;

class AnalyticsService
{
    private ?Carbon $startDate;
    private ?Carbon $endDate;
    // (جدید)
    private ?int $bookId;
    private ?string $platform;

    public function __construct(?string $startDate, ?string $endDate, ?int $bookId = null, ?string $platform = null)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // (جدید)
        $this->bookId = $bookId;
        $this->platform = $platform;
    }

    // (جدید)
    /**
     * یک کالبک (Closure) برای اعمال تمام فیلترها بر روی کوئری Payment
     * این متد جایگزین applyDateFilter شده و فیلترهای جدید را هم شامل می‌شود
     */
    private function getPaymentFilterCallback(): \Closure
    {
        return function (Builder $query) {
            // فیلتر تاریخ
            if ($this->startDate && $this->endDate) {
                // اطمینان از اینکه نام جدول درست است (مخصوصا در join ها)
                $column = $query->from === 'payments' ? 'payments.sale_date' : 'sale_date';
                $query->whereBetween($column, [$this->startDate, $this->endDate]);
            }

            // فیلتر کتاب
            if ($this->bookId) {
                $column = $query->from === 'payments' ? 'payments.book_id' : 'book_id';
                $query->where($column, $this->bookId);
            }

            // فیلتر پلتفرم
            if ($this->platform) {
                $column = $query->from === 'payments' ? 'payments.sale_platform' : 'sale_platform';
                $query->where($column, $this->platform);
            }
        };
    }

    /**
     * واکشی آمار کلی و جامع
     */
    public function getComprehensiveStats(): array
    {
        $paymentsQuery = Payment::query()->where($this->getPaymentFilterCallback());
        $stats = $paymentsQuery->select(
            DB::raw('SUM(amount) as total_revenue'),
            DB::raw('COUNT(id) as total_sales_count'),
            DB::raw('SUM(discount) as total_discount'),
            DB::raw('SUM(publisher_share) as total_publisher_share')
        )->first();

        // کتاب‌های پرفروش و پرسود در بازه زمانی
        $bestSellingBook = $this->getTopBooks('sales_count', 1)->first();
        $mostProfitableBook = $this->getTopBooks('revenue', 1)->first();

        return [
            'total_revenue' => $stats->total_revenue ?? 0,
            'total_sales_count' => $stats->total_sales_count ?? 0,
            'total_discount' => $stats->total_discount ?? 0,
            'total_publisher_share' => $stats->total_publisher_share ?? 0,
            'total_books' => Book::count(),
            'best_selling_book_title' => $bestSellingBook->title ?? 'N/A',
            'most_profitable_book_title' => $mostProfitableBook->title ?? 'N/A',
        ];
    }


    /**
     * واکشی آخرین پرداخت‌ها
     */
    public function getRecentPayments(int $limit = 10)
    {
        return Payment::query()->where($this->getPaymentFilterCallback())
            ->with('book:id,title')
            ->latest('sale_date')
            ->limit($limit)
            ->get();
    }

    /**
     * واکشی برترین نویسندگان بر اساس درآمد
     */
    public function getTopAuthors(int $limit = 5)
    {
        return Author::query()
            ->select('authors.id', 'authors.name', DB::raw('SUM(payments.publisher_share) as total_revenue'))
            // Join tables to connect authors to payments
            ->join('book_author_pivot', 'authors.id', '=', 'book_author_pivot.author_id')
            ->join('books', 'book_author_pivot.book_id', '=', 'books.id')
            ->join('payments', 'books.id', '=', 'payments.book_id')
            // Apply date filter on the correct table
            ->where(function ($query) {
                $this->getPaymentFilterCallback()($query->from('payments'));
            })
            ->groupBy('authors.id', 'authors.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * واکشی برترین ناشران بر اساس درآمد
     */
    public function getTopPublishers(int $limit = 5)
    {
        $query = Publisher::query()
            ->select(
                'publishers.id',
                'publishers.name',
                DB::raw('SUM(payments.publisher_share) as total_revenue')
            )
            ->join('book_publisher_pivot', 'publishers.id', '=', 'book_publisher_pivot.publisher_id')
            ->join('books', 'book_publisher_pivot.book_id', '=', 'books.id')
            ->join('payments', 'books.id', '=', 'payments.book_id');

        // فیلتر تاریخ
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payments.sale_date', [$this->startDate, $this->endDate]);
        }

        // فیلتر کتاب
        if ($this->bookId) {
            $query->where('payments.book_id', $this->bookId);
        }

        // فیلتر پلتفرم
        if ($this->platform) {
            $query->where('payments.sale_platform', $this->platform);
        }

        return $query
            ->groupBy('publishers.id', 'publishers.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }
    /**
     * واکشی برترین گویندگان بر اساس درآمد
     */
    public function getTopNarrators(int $limit = 5)
    {
        $query = Narrator::query()
            ->select(
                'narrators.id',
                'narrators.name',
                DB::raw('SUM(payments.publisher_share) as total_revenue')
            )
            ->join('book_narrator_pivot', 'narrators.id', '=', 'book_narrator_pivot.narrator_id')
            ->join('books', 'book_narrator_pivot.book_id', '=', 'books.id')
            ->join('payments', 'books.id', '=', 'payments.book_id');

        // فیلتر تاریخ
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('payments.sale_date', [$this->startDate, $this->endDate]);
        }

        // فیلتر کتاب
        if ($this->bookId) {
            $query->where('payments.book_id', $this->bookId);
        }

        // فیلتر پلتفرم
        if ($this->platform) {
            $query->where('payments.sale_platform', $this->platform);
        }

        return $query
            ->groupBy('narrators.id', 'narrators.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * آماده سازی تمام داده‌های نمودارها
     */
    public function getAllChartData(): array
    {
        return [
            'salesOverTime' => $this->getSalesOverTimeData(),
            'salesByPlatform' => $this->getSalesByPlatformData(),
            'topBooksByRevenue' => $this->getTopBooksChartData('revenue',50),
            'topBooksBySales' => $this->getTopBooksChartData('sales_count',50),
            'salesByCategory' => $this->getSalesByCategoryData(10),
            'salesByGender' => $this->getSalesByEnumData('gender_suitability', GenderSuitabilityEnum::class),
        ];
    }

    private function getTopBooks(string $orderBy, int $limit)
    {
        $filterCallback = $this->getPaymentFilterCallback();
        $query = Book::select('id', 'title');

        if ($orderBy === 'sales_count') {
            $query->withCount(['payments as aggregate' => $filterCallback])->orderByDesc('aggregate');
        }
        else { // revenue
            $query->withSum(['payments as aggregate' => $filterCallback], 'publisher_share')->orderByDesc('aggregate');
        }

        return $query->limit($limit)->get();
    }

    private function getSalesOverTimeData(): array
    {
        $query = Payment::query()->where($this->getPaymentFilterCallback())
            ->select(DB::raw('DATE(sale_date) as date'), DB::raw('SUM(publisher_share) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc');

        $data = $query->get();
        return [
            'labels' => $data->pluck('date')->map(fn($date) => \Morilog\Jalali\Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d')),
            'values' => $data->pluck('total'),
        ];
    }

    private function getSalesByPlatformData(): array
    {
        $platformMapping = [
            1 => 'فیدیبو',          // FIDIBO
            2 => 'طاقچه',           // TAGHCHEH
            3 => 'کتابراه',         // KETABRAH
            4 => 'نوار',            // NAVAR
            5 => 'نوین کتاب گویا',  // NOVIN_KETAB
        ];
        $data = Payment::query()->where($this->getPaymentFilterCallback())
            ->select('sale_platform', DB::raw('SUM(publisher_share) as total'))
            ->groupBy('sale_platform')->get();

        return [
            'labels' => $data->pluck('sale_platform')->map(fn($p) => $platformMapping[$p] ?? 'نامشخص'),
            'values' => $data->pluck('total'),
        ];
    }

    private function getTopBooksChartData(string $type, int $limit = 10): array
    {
        $books = $this->getTopBooks($type, $limit);
        return [
            'labels' => $books->pluck('title'),
            'values' => $books->pluck('aggregate'),
        ];
    }

    private function getSalesByCategoryData(int $limit = 7): array
    {
        $data = Category::select('categories.name')
            ->join('books', 'categories.id', '=', 'books.category_id')
            ->join('payments', 'books.id', '=', 'payments.book_id')
            ->where(function ($q) {
                $this->getPaymentFilterCallback()($q->from('payments'));
            })
            ->selectRaw('SUM(payments.publisher_share) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        return [
            'labels' => $data->pluck('name'),
            'values' => $data->pluck('total_revenue'),
        ];
    }

    private function getSalesByEnumData(string $attribute, string $enumClass): array
    {
        $filterCallback = $this->getPaymentFilterCallback();
        $data = Book::whereHas('payments', $filterCallback)
            ->withSum(['payments as total_revenue' => $filterCallback], 'publisher_share')
            ->get()
            ->groupBy($attribute)
            ->map(fn($books) => $books->sum('total_revenue'));

        return [
            'labels' => $data->keys()->map(fn($value) => $enumClass::from($value)->pName()),
            'values' => $data->values(),
        ];
    }
}
