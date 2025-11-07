<?php

namespace App\Livewire\Admin\Book;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookRateEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Exports\BooksExport;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- اضافه شد
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;

class BookList extends Component
{
    use WithPagination;

    // Price Management
    public ?Book $pricingBook = null;
    public ?int $new_price = null;
    public ?string $effective_date = null;
    public bool $showPriceModal = false;

    // Search, Filter & Sort
    public string $search = '';
    public string $sortCol = 'publish_date';
    public bool $sortAsc = false;

    // --- Filter Properties ---
    public string $filterStatus = '';
    public string $filterCategory = '';
    public string $filterAuthor = '';
    public string $filterTranslator = '';
    public string $filterNarrator = '';
    public string $filterComposer = '';
    public string $filterPublisher = '';
    public string $filterFormat = '';
    public string $filterPlatform = '';
    public string $filterGender = '';
    public string $filterRate = '';
    public ?string $filterPublishDateFrom = null;
    public ?string $filterPublishDateTo = null;
    public array $filterTags = [];

    // --- Search properties for filters (جدید) ---
    public string $categorySearch = '';
    public string $authorSearch = '';
    public string $translatorSearch = '';
    public string $narratorSearch = '';
    public string $composerSearch = '';
    public string $publisherSearch = '';
    public string $tagSearch = '';

    // --- Payment Filter Properties (جدید) ---
    public ?int $filterMinSalesCount = null;
    public ?int $filterMinSalesAmount = null;
    public string $filterPaymentPlatform = '';
    public ?string $filterPaymentDateFrom = null;
    public ?string $filterPaymentDateTo = null;


    /**
     * @var int|null شناسه کتابی که جزئیات آن نمایش داده می‌شود
     */
    public ?int $expandedBookId = null;


    /**
     * متد برای باز یا بسته کردن بخش جزئیات
     */
    public function toggleExpand($bookId): void
    {
        // اگر روی ردیفی که باز است دوباره کلیک شود، آن را می‌بندد
        // در غیر این صورت، ردیف جدید را باز می‌کند
        $this->expandedBookId = $this->expandedBookId === $bookId ? null : $bookId;
    }

    protected function priceRules(): array
    {
        return [
            'new_price' => 'required|integer|min:0',
            'effective_date' => 'required|date',
        ];
    }

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    public function updated($propertyName): void
    {
        // Reset pagination on any filter change
        if (str_starts_with($propertyName, 'filter') || $propertyName === 'search' || str_starts_with($propertyName, 'Search')) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search', 'filterStatus', 'filterCategory', 'filterAuthor', 'filterTranslator',
            'filterNarrator', 'filterComposer', 'filterPublisher', 'filterFormat',
            'filterPlatform', 'filterGender','filterRate', 'filterPublishDateFrom',
            'filterPublishDateTo', 'filterTags',
            // Reset new search properties (جدید)
            'categorySearch', 'authorSearch', 'translatorSearch', 'narratorSearch',
            'composerSearch', 'publisherSearch', 'tagSearch',
            // Reset new payment filters (جدید)
            'filterMinSalesCount', 'filterMinSalesAmount', 'filterPaymentPlatform',
            'filterPaymentDateFrom', 'filterPaymentDateTo'
        ]);
        $this->resetPage();
    }


    public function deleteBook(Book $book): void
    {
        $book->delete();
        $this->dispatch('toast', text: 'کتاب با موفقیت حذف شد.', icon: 'success');
    }

    public function openPriceModal(Book $book): void
    {
        $this->pricingBook = $book->load('prices.user');
        $this->showPriceModal = true;
    }

    public function saveNewPrice(): void
    {
        $this->validate($this->priceRules());

        $this->pricingBook->prices()->create([
            'price' => $this->new_price,
            'effective_date' => $this->effective_date,
            'user_id' => Auth::id(),
        ]);

        $this->dispatch('toast', text: 'قیمت جدید با موفقیت ثبت شد.', icon: 'success');
        $this->pricingBook->load('prices.user');
        $this->reset('new_price');
    }

    private function formatCategoriesForSelect(Collection $categories, $parentId = null, $prefix = ''): array
    {
        $result = [];
        foreach ($categories->where('parent_id', $parentId) as $category) {
            $result[] = (object)['id' => $category->id, 'name' => $prefix . ' ' . $category->name];
            $result = array_merge($result, $this->formatCategoriesForSelect($categories, $category->id, $prefix . '—'));
        }
        return $result;
    }

    public function sortBy(string $column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = false;
        }
        $this->sortCol = $column;
    }

    protected function getBooksQuery(): Builder
    {
        $filterPublishDateFrom = $this->filterPublishDateFrom
            ? Jalalian::fromFormat('Y/m/d', $this->filterPublishDateFrom)->toCarbon()->format('Y-m-d')
            : null;

        $filterPublishDateTo = $this->filterPublishDateTo
            ? Jalalian::fromFormat('Y/m/d', $this->filterPublishDateTo)->toCarbon()->format('Y-m-d')
            : null;

        // (جدید) Convert payment filter dates
        $paymentDateFrom = $this->filterPaymentDateFrom
            ? Jalalian::fromFormat('Y/m/d', $this->filterPaymentDateFrom)->toCarbon()->format('Y-m-d')
            : null;

        $paymentDateTo = $this->filterPaymentDateTo
            ? Jalalian::fromFormat('Y/m/d', $this->filterPaymentDateTo)->toCarbon()->format('Y-m-d')
            : null;

        return Book::query()
            ->when($this->search, fn($q) => $q->where(fn($sub) => $sub->where('title', 'like', '%' . $this->search . '%')->orWhere('financial_code', 'like', '%' . $this->search . '%')))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterGender, fn($q) => $q->where('gender_suitability', $this->filterGender))
            ->when($this->filterRate, fn($q) => $q->where('rate', $this->filterRate))
            ->when($this->filterFormat, fn($q) => $q->whereJsonContains('formats', $this->filterFormat))
            ->when($this->filterPlatform, fn($q) => $q->whereJsonContains('sales_platforms', $this->filterPlatform))
            ->when($filterPublishDateFrom, fn($q) => $q->where('publish_date', '>=', $filterPublishDateFrom))
            ->when($filterPublishDateTo, fn($q) => $q->where('publish_date', '<=', $filterPublishDateTo))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterAuthor, fn($q) => $q->whereHas('authors', fn($sub) => $sub->where('id', $this->filterAuthor)))
            ->when($this->filterTranslator, fn($q) => $q->whereHas('translators', fn($sub) => $sub->where('id', $this->filterTranslator)))
            ->when($this->filterNarrator, fn($q) => $q->whereHas('narrators', fn($sub) => $sub->where('id', $this->filterNarrator)))
            ->when($this->filterComposer, fn($q) => $q->whereHas('composers', fn($sub) => $sub->where('id', $this->filterComposer)))
            ->when($this->filterPublisher, fn($q) => $q->whereHas('publishers', fn($sub) => $sub->where('id', $this->filterPublisher)))
            ->when($this->filterTags, function ($q) {
                // This ensures that books must have ALL selected tags
                foreach ($this->filterTags as $tag) {
                    if (!empty($tag)) {
                        $q->whereJsonContains('tags', $tag);
                    }
                }
            })
            // --- (جدید) Payment Filters ---
            ->when($this->filterMinSalesCount, fn($q) => $q->has('payments', '>=', $this->filterMinSalesCount))
            ->when($this->filterMinSalesAmount, fn($q) => $q->whereHas('payments', function ($query) {
                $query->select(DB::raw('sum(amount)'))
                    ->groupBy('book_id')
                    ->havingRaw('sum(amount) >= ?', [$this->filterMinSalesAmount]);
            }))
            ->when($this->filterPaymentPlatform, fn($q) => $q->whereHas('payments', fn($sub) => $sub->where('sale_platform', $this->filterPaymentPlatform)))
            ->when($paymentDateFrom, fn($q) => $q->whereHas('payments', fn($sub) => $sub->where('sale_date', '>=', $paymentDateFrom)))
            ->when($paymentDateTo, fn($q) => $q->whereHas('payments', fn($sub) => $sub->where('sale_date', '<=', $paymentDateTo)));
    }

    public function exportExcel()
    {
        $query = $this->getBooksQuery()
            // Eager load all necessary relationships for the export
            ->with([
                'category', 'authors', 'translators', 'narrators',
                'publishers', 'latestPrice'
            ])
            // (جدید) Add sales data to export
            ->withCount('payments as sales_count')
            ->withSum('payments as total_amount', 'amount')
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');

        return Excel::download(
            new BooksExport($query),
            'books-' . now()->format('Y-m-d-H-i') . '.xlsx'
        );
    }

    public function render()
    {
        $query = $this->getBooksQuery()->with([
            'category', 'authors', 'latestPrice', 'translators',
            'narrators', 'composers', 'editors', 'publishers'
        ]);

        // (جدید) Add Sales aggregates
        $query->withCount('payments as sales_count')
            ->withSum('payments as total_amount', 'amount');

        $statsQuery = clone $query;
        $stats = [
            'total_books' => $statsQuery->count(),
            'published_books' => (clone $statsQuery)->where('status', BookStatusEnum::PUBLISHED->value)->count(),
            'canceled_books' => (clone $statsQuery)->where('status', BookStatusEnum::CANCELED->value)->count(),
            'shared_books' => (clone $statsQuery)->where('status', BookStatusEnum::SHARED->value)->count(),
        ];

        // (تغییر) Sorting logic now works for sales_count and total_amount
        $books = $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')->paginate(10);

        // (جدید) Apply search to tags
        $allTagsQuery = Book::query()
            ->whereNotNull('tags')
            ->pluck('tags') // بهینه‌سازی: دریافت مستقیم ستون تگ‌ها
            ->flatten()
            ->filter()      // حذف تگ‌های خالی یا null
            ->unique();

        if ($this->tagSearch) {
            $allTagsQuery = $allTagsQuery->filter(fn($tag) => mb_strpos((string)$tag, $this->tagSearch, 0, 'UTF-8') !== false);
        }

        $allTags = $allTagsQuery
            ->sortBy(null, SORT_LOCALE_STRING) // مرتب‌سازی دقیق بر اساس حروف الفبای فارسی
            ->values()
            ->all();

        $filterData = [
            // (جدید) Apply search to filter data
            'categories' => $this->formatCategoriesForSelect(Category::when($this->categorySearch, fn($q) => $q->where('name', 'like', '%' . $this->categorySearch . '%'))->get()),
            'authors' => Author::when($this->authorSearch, fn($q) => $q->where('name', 'like', '%' . $this->authorSearch . '%'))->orderBy('name')->take(50)->get(),
            'translators' => Translator::when($this->translatorSearch, fn($q) => $q->where('name', 'like', '%' . $this->translatorSearch . '%'))->orderBy('name')->take(50)->get(),
            'narrators' => Narrator::when($this->narratorSearch, fn($q) => $q->where('name', 'like', '%' . $this->narratorSearch . '%'))->orderBy('name')->take(50)->get(),
            'composers' => Composer::when($this->composerSearch, fn($q) => $q->where('name', 'like', '%' . $this->composerSearch . '%'))->orderBy('name')->take(50)->get(),
            'publishers' => Publisher::when($this->publisherSearch, fn($q) => $q->where('name', 'like', '%' . $this->publisherSearch . '%'))->orderBy('name')->take(50)->get(),
            'bookStatuses' => BookStatusEnum::cases(),
            'bookFormats' => BookFormatEnum::cases(),
            'salesPlatforms' => SalesPlatformEnum::cases(),
            'genderSuitabilities' => GenderSuitabilityEnum::cases(),
            'rates' => BookRateEnum::cases(),
            'allTags' => $allTags,
        ];

        return view('livewire.admin.book.book-list', [
            'books' => $books,
            'stats' => $stats,
        ])->with($filterData);
    }
}
