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
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

// اضافه کردن این خط

class BookForm extends Component
{
    public ?Book $book = null;

    // Form Properties
    public $financial_code = '';
    public $title = '';
    public $category_id = null;
    public $status = 1;
    public $print_price = null;
    public $suggested_price = null;
    public $track_count = null;
    public $print_pages = null;
    public $breakeven_sales_count = null;
    public $listener_type = 1;
    public $author_rate = null;
    public $narrator_rate = null;
    public $editor_composer_rate = null;
    public $translator_rate = null;
    public $fidibo_book_id = null;
    public $taghcheh_book_id = null;
    public $navar_book_id = null;
    public $ketabrah_book_id = null;
    public $max_discount = null;
    public $description = '';
    public $based_on = null;
    public $publish_date = null;
    public $awards = null;
    public $gender_suitability = 3;
    public $sales_platforms = [];
    public $formats = [];
    public $tags = [];
    public string $newTag = '';

    // Relationships
    public array $selectedAuthors = [];
    public array $selectedTranslators = [];
    public array $selectedNarrators = [];
    public array $selectedComposers = [];
    public array $selectedPublishers = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'financial_code' => 'required|string|unique:books,financial_code,' . $this->book?->id,
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'selectedAuthors' => 'required|array|min:1',
            'print_price' => 'nullable|integer|min:0',
            'suggested_price' => 'nullable|integer|min:0',
            'track_count' => 'nullable|integer|min:0',
            'print_pages' => 'nullable|integer|min:0',
            'breakeven_sales_count' => 'nullable|integer|min:0',
            'author_rate' => 'nullable|integer|min:0|max:100',
            'narrator_rate' => 'nullable|integer|min:0|max:100',
            'editor_composer_rate' => 'nullable|integer|min:0|max:100',
            'translator_rate' => 'nullable|integer|min:0|max:100',
            'max_discount' => 'nullable|integer|min:0|max:100',
            'publish_date' => 'nullable|date',
            'formats' => 'nullable|array',
            'sales_platforms' => 'nullable|array',
            'tags' => 'array|nullable',
        ];
    }

    public function mount()
    {

        if ($this->book && $this->book->exists) {
            $this->book = $this->book->load(
                'authors', 'translators', 'narrators', 'composers', 'publishers'
            );
            $this->fill($this->book);
            $this->publish_date = $this->book->publish_date?->format('Y-m-d');
            $this->selectedAuthors = $this->book->authors->pluck('id')->toArray();
            $this->selectedTranslators = $this->book->translators->pluck('id')->toArray();
            $this->selectedNarrators = $this->book->narrators->pluck('id')->toArray();
            $this->selectedComposers = $this->book->composers->pluck('id')->toArray();
            $this->selectedPublishers = $this->book->publishers->pluck('id')->toArray();
            $this->tags = $this->book->tags ?? [];
        }
    }

    public function save(): void
    {
         $this->validate();

        $bookData = [
            'financial_code' => $this->financial_code,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'print_price' => $this->print_price,
            'suggested_price' => $this->suggested_price,
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

        $book = Book::updateOrCreate(['id' => $this->book?->id], $bookData);

        $book->authors()->sync($this->selectedAuthors);
        $book->translators()->sync($this->selectedTranslators);
        $book->narrators()->sync($this->selectedNarrators);
        $book->composers()->sync($this->selectedComposers);
        $book->publishers()->sync($this->selectedPublishers);

        $message = $this->book ? 'کتاب با موفقیت به‌روزرسانی شد.' : 'کتاب با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success', timeout: 2000);

        // تغییر مسیر با متد Livewire
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
        // فقط در صورتی تگ را اضافه کن که خالی نباشد و تکراری هم نباشد
        if ($tag && !in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        // پس از افزودن، فیلد ورودی را خالی کن
        $this->newTag = '';
    }
    public function removeTag(int $index): void
    {
        if (isset($this->tags[$index])) {
            unset($this->tags[$index]);
            $this->tags = array_values($this->tags); // re-index array
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
            'bookStatuses' => BookStatusEnum::cases(),
            'salesPlatformsEnum' => SalesPlatformEnum::cases(),
            'bookFormatsEnum' => BookFormatEnum::cases(),
            'listenerTypesEnum' => ListenerTypeEnum::cases(),
            'genderSuitabilityEnum' => GenderSuitabilityEnum::cases(),
        ]);
    }
}
