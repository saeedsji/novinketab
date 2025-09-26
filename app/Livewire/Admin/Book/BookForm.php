<?php

namespace App\Livewire\Admin\Book;

use App\Enums\Book\BookFormatEnum;
use App\Enums\Book\BookStatusEnum;
use App\Enums\Book\GenderSuitabilityEnum;
use App\Enums\Book\SalesPlatformEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Composer;
use App\Models\Editor; // اضافه شد
use App\Models\Narrator;
use App\Models\Publisher;
use App\Models\Translator;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class BookForm extends Component
{
    public ?Book $book = null;

    // Form Properties
    public $financial_code = '';
    public $title = '';
    public $taghche_title = '';
    public $category_id = null;
    public $status = 1; // مقدار پیش‌فرض از Enum
    public $gender_suitability = 3; // مقدار پیش‌فرض از Enum
    public $print_price = null;
    public $suggested_price = null;
    public $track_count = null;
    public $duration = null; // اضافه شد
    public $print_pages = null;
    public $breakeven_sales_count = null;
    public $sales_platforms = [];
    public $formats = [];
    public $fidibo_book_id = null;
    public $taghcheh_book_id = null;
    public $navar_book_id = null;
    public $ketabrah_book_id = null;
    public $max_discount = null;
    public $description = '';
    public $tags = [];
    public $publish_date = null;
    public string $newTag = '';

    // Relationships
    public array $selectedAuthors = [];
    public array $selectedTranslators = [];
    public array $selectedNarrators = [];
    public array $selectedComposers = [];
    public array $selectedPublishers = [];
    public array $selectedEditors = []; // اضافه شد

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'financial_code' => 'required|string|unique:books,financial_code,' . $this->book?->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'gender_suitability' => 'required',
            'selectedAuthors' => 'required|array|min:1',

            // Nullable Fields
            'print_price' => 'nullable|integer|min:0',
            'suggested_price' => 'nullable|integer|min:0',
            'track_count' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0', // اضافه شد
            'print_pages' => 'nullable|integer|min:0',
            'breakeven_sales_count' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0|max:100',
            'publish_date' => 'nullable|date',
            'formats' => 'nullable|array',
            'sales_platforms' => 'nullable|array',
            'tags' => 'nullable|array',
            'fidibo_book_id' => 'nullable|string|max:255',
            'taghcheh_book_id' => 'nullable|string|max:255',
            'navar_book_id' => 'nullable|string|max:255',
            'ketabrah_book_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'taghche_title' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        if ($this->book && $this->book->exists) {
            // Eager load all relationships
            $this->book = $this->book->load(
                'authors', 'translators', 'narrators', 'composers', 'publishers', 'editors' // 'editors' اضافه شد
            );

            // Fill form properties from the book model
            $this->fill($this->book);

            // Format date for the input field
            $this->publish_date = $this->book->publish_date?->format('Y-m-d');

            // Populate relationship arrays
            $this->selectedAuthors = $this->book->authors->pluck('id')->toArray();
            $this->selectedTranslators = $this->book->translators->pluck('id')->toArray();
            $this->selectedNarrators = $this->book->narrators->pluck('id')->toArray();
            $this->selectedComposers = $this->book->composers->pluck('id')->toArray();
            $this->selectedPublishers = $this->book->publishers->pluck('id')->toArray();
            $this->selectedEditors = $this->book->editors->pluck('id')->toArray(); // اضافه شد

            // Ensure JSON fields are arrays
            $this->tags = $this->book->tags ?? [];
            $this->sales_platforms = $this->book->sales_platforms ?? [];
            $this->formats = $this->book->formats ?? [];
        }
    }

    public function save(): void
    {
        $this->validate();

        $bookData = [
            'financial_code' => $this->financial_code,
            'title' => $this->title,
            'taghche_title' => $this->taghche_title,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'gender_suitability' => $this->gender_suitability,
            'print_price' => $this->print_price,
            'suggested_price' => $this->suggested_price,
            'track_count' => $this->track_count,
            'duration' => $this->duration, // اضافه شد
            'print_pages' => $this->print_pages,
            'breakeven_sales_count' => $this->breakeven_sales_count,
            'sales_platforms' => $this->sales_platforms,
            'formats' => $this->formats,
            'fidibo_book_id' => $this->fidibo_book_id,
            'taghcheh_book_id' => $this->taghcheh_book_id,
            'navar_book_id' => $this->navar_book_id,
            'ketabrah_book_id' => $this->ketabrah_book_id,
            'max_discount' => $this->max_discount,
            'description' => $this->description,
            'tags' => $this->tags,
            'publish_date' => $this->publish_date,
        ];

        $book = Book::updateOrCreate(['id' => $this->book?->id], $bookData);

        // Sync all relationships
        $book->authors()->sync($this->selectedAuthors);
        $book->translators()->sync($this->selectedTranslators);
        $book->narrators()->sync($this->selectedNarrators);
        $book->composers()->sync($this->selectedComposers);
        $book->publishers()->sync($this->selectedPublishers);
        $book->editors()->sync($this->selectedEditors); // اضافه شد

        $message = $this->book ? 'کتاب با موفقیت به‌روزرسانی شد.' : 'کتاب با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');

        $this->redirect(route('book.index'), navigate: true);
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

    public function addTag(): void
    {
        $tag = trim($this->newTag);
        if ($tag && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        $this->newTag = '';
    }

    public function removeTag(int $index): void
    {
        if (isset($this->tags[$index])) {
            unset($this->tags[$index]);
            $this->tags = array_values($this->tags);
        }
    }

    public function render()
    {
        return view('livewire.admin.book.book-form', [
            'categoryTree' => $this->formatCategoriesForSelect(Category::all()),
            'authors' => Author::query()->orderBy('name')->get(),
            'translators' => Translator::query()->orderBy('name')->get(),
            'narrators' => Narrator::query()->orderBy('name')->get(),
            'composers' => Composer::query()->orderBy('name')->get(),
            'publishers' => Publisher::query()->orderBy('name')->get(),
            'editors' => Editor::query()->orderBy('name')->get(), // اضافه شد
            'bookStatuses' => BookStatusEnum::cases(),
            'genderSuitabilityEnum' => GenderSuitabilityEnum::cases(),
            'salesPlatformsEnum' => SalesPlatformEnum::cases(),
            'bookFormatsEnum' => BookFormatEnum::cases(),
        ]);
    }
}
