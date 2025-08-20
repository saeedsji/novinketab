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

class AnalyticsService
{
    private ?Carbon $startDate;
    private ?Carbon $endDate;

    public function __construct(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
    }

    /**
     * اعمال فیلتر بازه زمانی روی کوئری
     */
    private function applyDateFilter(Builder $query, string $dateColumn = 'sale_date'): Builder
    {
        if ($this->startDate && $this->endDate) {
            return $query->whereBetween($dateColumn, [$this->startDate, $this->endDate]);
        }
        return $query;
    }

    /**
     * واکشی آمار کلی و جامع
     */
    public function getComprehensiveStats(): array
    {
        $paymentsQuery = $this->applyDateFilter(Payment::query());

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
            'total_authors' => Author::count(),
            'best_selling_book_title' => $bestSellingBook->title ?? 'N/A',
            'most_profitable_book_title' => $mostProfitableBook->title ?? 'N/A',
        ];
    }


    /**
     * واکشی آخرین پرداخت‌ها
     */
    public function getRecentPayments(int $limit = 10)
    {
        return $this->applyDateFilter(Payment::query())
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
            ->select('authors.id', 'authors.name', DB::raw('SUM(payments.amount) as total_revenue'))
            // Join tables to connect authors to payments
            ->join('book_author_pivot', 'authors.id', '=', 'book_author_pivot.author_id')
            ->join('books', 'book_author_pivot.book_id', '=', 'books.id')
            ->join('payments', 'books.id', '=', 'payments.book_id')
            // Apply date filter on the correct table
            ->where(function ($query) {
                $this->applyDateFilter($query, 'payments.sale_date');
            })
            ->groupBy('authors.id', 'authors.name')
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
            'topBooksByRevenue' => $this->getTopBooksChartData('revenue'),
            'topBooksBySales' => $this->getTopBooksChartData('sales_count'),
            'salesByCategory' => $this->getSalesByCategoryData(),
            'salesByGender' => $this->getSalesByEnumData('gender_suitability', GenderSuitabilityEnum::class),
        ];
    }

    private function getTopBooks(string $orderBy, int $limit)
    {
        $dateFilterCallback = fn($q) => $this->applyDateFilter($q);

        $query = Book::select('id', 'title');

        if ($orderBy === 'sales_count') {
            $query->withCount(['payments as aggregate' => $dateFilterCallback])->orderByDesc('aggregate');
        }
        else { // revenue
            $query->withSum(['payments as aggregate' => $dateFilterCallback], 'amount')->orderByDesc('aggregate');
        }

        return $query->limit($limit)->get();
    }

    private function getSalesOverTimeData(): array
    {
        $query = $this->applyDateFilter(Payment::query())
            ->select(DB::raw('DATE(sale_date) as date'), DB::raw('SUM(amount) as total'))
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
        $platformMapping = [1 => 'فیدیبو', 2 => 'طاقچه', 3 => 'نوار', 4 => 'کتابراه'];
        $data = $this->applyDateFilter(Payment::query())
            ->select('sale_platform', DB::raw('SUM(amount) as total'))
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
                $this->applyDateFilter($q, 'payments.sale_date');
            })
            ->selectRaw('SUM(payments.amount) as total_revenue')
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
        $data = Book::whereHas('payments', fn($q) => $this->applyDateFilter($q))
            ->withSum(['payments as total_revenue' => fn($q) => $this->applyDateFilter($q)], 'amount')
            ->get()
            ->groupBy($attribute)
            ->map(fn($books) => $books->sum('total_revenue'));

        return [
            'labels' => $data->keys()->map(fn($value) => $enumClass::from($value)->pName()),
            'values' => $data->values(),
        ];
    }
}
