<?php

namespace App\Livewire\Admin\Book;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
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
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

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
    public string $sortCol = 'id';
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
    public string $filterListenerType = '';
    public string $filterGender = '';
    public ?string $filterPublishDateFrom = null;
    public ?string $filterPublishDateTo = null;


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
        if (str_starts_with($propertyName, 'filter') || $propertyName === 'search') {
            $this->resetPage();
        }
    }

    /**
     * Reset all filters to their default state.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search', 'filterStatus', 'filterCategory', 'filterAuthor', 'filterTranslator',
            'filterNarrator', 'filterComposer', 'filterPublisher', 'filterFormat',
            'filterPlatform', 'filterListenerType', 'filterGender', 'filterPublishDateFrom',
            'filterPublishDateTo'
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

    public function sortBy($column): void
    {
        if ($this->sortCol === $column) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortCol = $column;
    }

    public function render()
    {
        $query = Book::query()
            ->with(['category', 'authors', 'latestPrice'])
            // --- Search ---
            ->when($this->search, fn($q) => $q->where(fn($sub) => $sub->where('title', 'like', '%' . $this->search . '%')->orWhere('financial_code', $this->search)))
            // --- Direct Field Filters ---
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterListenerType, fn($q) => $q->where('listener_type', $this->filterListenerType))
            ->when($this->filterGender, fn($q) => $q->where('gender_suitability', $this->filterGender))
            // --- JSON Field Filters ---
            ->when($this->filterFormat, fn($q) => $q->whereJsonContains('formats', $this->filterFormat))
            ->when($this->filterPlatform, fn($q) => $q->whereJsonContains('sales_platforms', $this->filterPlatform))
            // --- Date Range Filter ---
            ->when($this->filterPublishDateFrom, fn($q) => $q->where('publish_date', '>=', $this->filterPublishDateFrom))
            ->when($this->filterPublishDateTo, fn($q) => $q->where('publish_date', '<=', $this->filterPublishDateTo))
            // --- Relational Filters ---
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterAuthor, fn($q) => $q->whereHas('authors', fn($sub) => $sub->where('id', $this->filterAuthor)))
            ->when($this->filterTranslator, fn($q) => $q->whereHas('translators', fn($sub) => $sub->where('id', $this->filterTranslator)))
            ->when($this->filterNarrator, fn($q) => $q->whereHas('narrators', fn($sub) => $sub->where('id', $this->filterNarrator)))
            ->when($this->filterComposer, fn($q) => $q->whereHas('composers', fn($sub) => $sub->where('id', $this->filterComposer)))
            ->when($this->filterPublisher, fn($q) => $q->whereHas('publishers', fn($sub) => $sub->where('id', $this->filterPublisher)));

        // Calculate stats based on the filtered query
        $statsQuery = clone $query;
        $stats = [
            'total_books' => $statsQuery->count(),
            'published_books' => (clone $statsQuery)->where('status', BookStatusEnum::PUBLISHED->value)->count(),
            'draft_books' => (clone $statsQuery)->where('status', BookStatusEnum::DRAFT->value)->count(),
            'canceled_books' => (clone $statsQuery)->where('status', BookStatusEnum::CANCELED->value)->count(),
        ];

        // Apply sorting and pagination
        $books = $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')->paginate(10);

        // Data for filter dropdowns
        $filterData = [
            'categories' => $this->formatCategoriesForSelect(Category::all()),
            'authors' => Author::orderBy('name')->get(),
            'translators' => Translator::orderBy('name')->get(),
            'narrators' => Narrator::orderBy('name')->get(),
            'composers' => Composer::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('name')->get(),
            'bookStatuses' => BookStatusEnum::cases(),
            'bookFormats' => BookFormatEnum::cases(),
            'salesPlatforms' => SalesPlatformEnum::cases(),
            'listenerTypes' => ListenerTypeEnum::cases(),
            'genderSuitabilities' => GenderSuitabilityEnum::cases(),
        ];

        return view('livewire.admin.book.book-list', [
            'books' => $books,
            'stats' => $stats,
        ])->with($filterData);
    }
}
