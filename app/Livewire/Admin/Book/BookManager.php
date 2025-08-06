<?php

namespace App\Livewire\Admin\Book;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
use App\Enums\Book\ListenerTypeEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookPrice;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookManager extends Component
{
    use WithPagination;

    // Book Properties
    public ?Book $editingBook = null;
    public string $financial_code = '';
    public string $title = '';
    public ?int $category_id = null;
    public int $status = 1;
    public ?int $estimated_cost = null;
    public ?int $print_price = null;
    public ?int $suggested_price = null;
    public ?int $estimated_pages = null;
    public ?int $track_count = null;
    public ?int $print_pages = null;
    public ?int $breakeven_sales_count = null;
    public int $listener_type = 1;
    public ?int $author_rate = null;
    public ?int $narrator_rate = null;
    public ?int $editor_composer_rate = null;
    public ?int $translator_rate = null;
    public ?string $fidibo_book_id = null;
    public ?string $taghcheh_book_id = null;
    public ?string $navar_book_id = null;
    public ?string $ketabrah_book_id = null;
    public ?int $max_discount = null;
    public string $description = '';
    public ?string $based_on = null;
    public ?string $publish_date = null;
    public ?string $awards = null;
    public int $gender_suitability = 3;
    public array $sales_platforms = [];
    public array $formats = [];
    public array $tags = [];

    // Relationships
    public array $selectedAuthors = [];
    public array $selectedTranslators = [];
    public array $selectedNarrators = [];
    public array $selectedComposers = [];
    public array $selectedPublishers = [];

    // Modals & Titles
    public bool $showBookModal = false;
    public string $bookModalTitle = '';
    public bool $showPriceModal = false;

    // Price Management
    public ?Book $pricingBook = null;
    public ?int $new_price = null;
    public ?string $effective_date = null;

    // Search, Filter & Sort
    public string $search = '';
    public string $sortCol = 'created_at';
    public bool $sortAsc = false;
    public string $filterStatus = '';
    public string $filterCategory = '';

    protected function bookRules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'financial_code' => 'required|string|unique:books,financial_code,' . $this->editingBook?->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'selectedAuthors' => 'required|array|min:1',
            'estimated_cost' => 'nullable|integer|min:0',
            'print_price' => 'nullable|integer|min:0',
            'suggested_price' => 'nullable|integer|min:0',
            'estimated_pages' => 'nullable|integer|min:0',
            'track_count' => 'nullable|integer|min:0',
            'print_pages' => 'nullable|integer|min:0',
            'breakeven_sales_count' => 'nullable|integer|min:0',
            'author_rate' => 'nullable|integer|min:0|max:100',
            'narrator_rate' => 'nullable|integer|min:0|max:100',
            'editor_composer_rate' => 'nullable|integer|min:0|max:100',
            'translator_rate' => 'nullable|integer|min:0|max:100',
            'max_discount' => 'nullable|integer|min:0|max:100',
            'publish_date' => 'nullable|date',
        ];
    }

    protected function priceRules(): array
    {
        return [
            'new_price' => 'required|integer|min:0',
            'effective_date' => 'required|date',
        ];
    }

    public function mount()
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'filterStatus', 'filterCategory'])) {
            $this->resetPage();
        }
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->resetExcept('sortCol', 'sortAsc', 'search', 'filterStatus', 'filterCategory');
        $this->mount(); // Re-initialize default values
    }

    public function createBook(): void
    {
        $this->resetForm();
        $this->bookModalTitle = 'ایجاد کتاب جدید';
        $this->showBookModal = true;
        $this->dispatch('init-tom-select');
    }

    public function editBook(Book $book): void
    {
        $this->resetForm();
        $this->editingBook = $book->load(
            'authors', 'translators', 'narrators', 'composers', 'publishers'
        );

        $this->fill($book);
        $this->publish_date = $book->publish_date?->format('Y-m-d');

        $this->selectedAuthors = $book->authors->pluck('id')->toArray();
        $this->selectedTranslators = $book->translators->pluck('id')->toArray();
        $this->selectedNarrators = $book->narrators->pluck('id')->toArray();
        $this->selectedComposers = $book->composers->pluck('id')->toArray();
        $this->selectedPublishers = $book->publishers->pluck('id')->toArray();

        $this->bookModalTitle = 'ویرایش کتاب';
        $this->showBookModal = true;
        $this->dispatch('init-tom-select');
    }

    public function saveBook(): void
    {
        $validatedData = $this->validate($this->bookRules());

        $bookData = [
            'financial_code' => $this->financial_code,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'estimated_cost' => $this->estimated_cost,
            'print_price' => $this->print_price,
            'suggested_price' => $this->suggested_price,
            'estimated_pages' => $this->estimated_pages,
            'track_count' => $this->track_count,
            'print_pages' => $this->print_pages,
            'breakeven_sales_count' => $this->breakeven_sales_count,
            'sales_platforms' => $this->sales_platforms,
            'formats' => $this->formats,
            'listener_type' => $this->listener_type,
            'author_rate' => $this->author_rate,
            'narrator_rate' => $this->narrator_rate,
            'editor_composer_rate' => $this->editor_composer_rate,
            'translator_rate' => $this->translator_rate,
            'fidibo_book_id' => $this->fidibo_book_id,
            'taghcheh_book_id' => $this->taghcheh_book_id,
            'navar_book_id' => $this->navar_book_id,
            'ketabrah_book_id' => $this->ketabrah_book_id,
            'max_discount' => $this->max_discount,
            'description' => $this->description,
            'tags' => $this->tags,
            'based_on' => $this->based_on,
            'awards' => $this->awards,
            'publish_date' => $this->publish_date,
            'gender_suitability' => $this->gender_suitability,
        ];

        $book = Book::updateOrCreate(['id' => $this->editingBook?->id], $bookData);

        $book->authors()->sync($this->selectedAuthors);
        $book->translators()->sync($this->selectedTranslators);
        $book->narrators()->sync($this->selectedNarrators);
        $book->composers()->sync($this->selectedComposers);
        $book->publishers()->sync($this->selectedPublishers);

        $message = $this->editingBook ? 'کتاب با موفقیت به‌روزرسانی شد.' : 'کتاب با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->showBookModal = false;
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
        $books = Book::query()
            ->with(['category', 'authors'])
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%')->orWhere('financial_code', $this->search))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        $categoryTree = $this->formatCategoriesForSelect(Category::all());

        return view('livewire.admin.book.book-manager', [
            'books' => $books,
            'categoryTree' => $categoryTree,
            'authors' => Author::all(),
            'translators' => Translator::all(),
            'narrators' => Narrator::all(),
            'composers' => Composer::all(),
            'publishers' => Publisher::all(),
            'bookStatuses' => BookStatusEnum::cases(),
            'salesPlatformsEnum' => SalesPlatformEnum::cases(),
            'bookFormatsEnum' => BookFormatEnum::cases(),
            'listenerTypesEnum' => ListenerTypeEnum::cases(),
            'genderSuitabilityEnum' => GenderSuitabilityEnum::cases(),
        ]);
    }
}
