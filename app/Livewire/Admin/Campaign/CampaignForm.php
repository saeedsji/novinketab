<?php

namespace App\Livewire\Admin\Campaign;

use App\Enums\Book\SalesPlatformEnum;
use App\Models\Book;
use App\Models\Campaign;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Morilog\Jalali\Jalalian;


class CampaignForm extends Component
{
    public ?Campaign $campaign = null;

    // Form Properties
    public string $name = '';
    public ?string $start_date = '';
    public ?string $end_date = '';
    public ?int $discount_percent = 0;
    public ?int $platform = null;

    // Book Management
    public array $selectedBooks = []; // Holds Book IDs
    public array $selectedBookDetails = []; // Holds title/code for display
    public string $bookSearch = '';

    // --- REFACTOR: START (UX Improvement) ---
    // Manages the visibility of the search results dropdown
    public bool $showBookSearchResults = false;
    // --- REFACTOR: END ---

    // Page state
    public string $pageTitle = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|string|max:10', // Jalali date YYYY/MM/DD
            'end_date' => 'required|string|max:10', // Jalali date YYYY/MM/DD
            'discount_percent' => 'required|integer|min:0|max:100',
            'platform' => ['required', Rule::enum(SalesPlatformEnum::class)],
            'selectedBooks' => 'nullable|array',
            'selectedBooks.*' => 'exists:books,id',
        ];
    }

    protected $validationAttributes = [
        'name' => 'نام کمپین',
        'start_date' => 'تاریخ شروع',
        'end_date' => 'تاریخ پایان',
        'discount_percent' => 'درصد تخفیf',
        'platform' => 'پلتفرم',
        'selectedBooks' => 'کتاب‌ها',
    ];

    /**
     * Mounts the component, setting state for create or edit.
     */
    public function mount(): void
    {
        if ($this->campaign) {
            $campaign = $this->campaign;
            $this->pageTitle = 'ویرایش کمپین: ' . $campaign->name;

            // Load data
            $this->name = $campaign->name;
            $this->start_date = $campaign->start_date_jalali();
            $this->end_date = $campaign->end_date_jalali();
            $this->discount_percent = $campaign->discount_percent;
            $this->platform = $campaign->platform->value;

            // Load associated books (Eager loading - Instruction #6)
            $books = $campaign->books()->select('books.id', 'title', 'financial_code')->get();
            $this->selectedBooks = $books->pluck('id')->toArray();
            $this->selectedBookDetails = $books->keyBy('id')->map(fn($book) => [
                'title' => $book->title,
                'financial_code' => $book->financial_code,
            ])->toArray();
        } else {
            $this->pageTitle = 'ایجاد کمپین جدید';
            // Set defaults if needed, e.g., $this.start_date = Jalalian::now()->format('Y/m/d');
        }
    }

    /**
     * Real-time validation (Instruction #5)
     */
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['name', 'start_date', 'end_date', 'discount_percent', 'platform'])) {
            $this->validateOnly($propertyName);
        }

        // --- REFACTOR: START (UX Improvement) ---
        // If user is typing in search, ensure dropdown is visible
        if ($propertyName === 'bookSearch' && strlen($this->bookSearch) >= 2) {
            $this->showBookSearchResults = true;
        }
        // --- REFACTOR: END ---
    }

    // --- Book Management Methods ---

    /**
     * Searches for books when user types in the search box.
     */
    public function getBookSearchResultsProperty()
    {
        // --- REFACTOR: START (UX Improvement) ---
        // Only search if the dropdown is supposed to be open and query is long enough
        if (!$this->showBookSearchResults || strlen($this->bookSearch) < 2) {
            return collect();
        }
        // --- REFACTOR: END ---

        // Optimized query (Instruction #10)
        return Book::query()
            ->where(function ($query) {
                $query->where('title', 'like', '%' . $this->bookSearch . '%')
                    ->orWhere('financial_code', 'like', '%' . $this->bookSearch . '%');
            })
            // Only select books that are not already in the list
            ->whereNotIn('id', $this->selectedBooks)
            ->select('id', 'title', 'financial_code')
            ->take(10)
            ->get();
    }

    /**
     * Resets the book search input and hides the dropdown.
     * (UX Improvement)
     */
    public function resetBookSearch(): void
    {
        $this->bookSearch = '';
        $this->showBookSearchResults = false;
    }

    /**
     * Adds a book to the selected list for the campaign.
     */
    public function addBook(int $bookId): void
    {
        if (in_array($bookId, $this->selectedBooks)) {
            $this->resetBookSearch(); // Close dropdown even if book is already added
            return;
        }

        $book = Book::query()->find($bookId, ['id', 'title', 'financial_code']);
        if ($book) {
            $this->selectedBooks[] = $book->id;
            $this->selectedBookDetails[$book->id] = [
                'title' => $book->title,
                'financial_code' => $book->financial_code,
            ];
            $this->resetBookSearch(); // --- REFACTOR (UX)
        }
    }

    /**
     * Removes a book from the selected list.
     */
    public function removeBook(int $bookId): void
    {
        $this->selectedBooks = array_filter($this->selectedBooks, fn($id) => $id !== $bookId);
        unset($this->selectedBookDetails[$bookId]);
    }

    /**
     * Saves the new or edited campaign to the database.
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        // Convert Jalali dates to Carbon/Gregorian for DB
        try {
            $startDateGregorian = $this->start_date
                ? Jalalian::fromFormat('Y/m/d', $this->start_date)->toCarbon()->format('Y-m-d')
                : null;
            $endDateGregorian = $this->end_date
                ? Jalalian::fromFormat('Y/m/d', $this->end_date)->toCarbon()->format('Y-m-d')
                : null;
        } catch (\Exception $e) {
            $this->addError('start_date', 'فرمت تاریخ نامعتبر است.');
            return;
        }

        if (strtotime($startDateGregorian) > strtotime($endDateGregorian)) {
            $this->addError('end_date', 'تاریخ پایان باید بعد از تاریخ شروع باشد.');
            return;
        }

        $dbData = [
            'name' => $validatedData['name'],
            'start_date' => $startDateGregorian,
            'end_date' => $endDateGregorian,
            'discount_percent' => $validatedData['discount_percent'],
            'platform' => $validatedData['platform'],
        ];

        // --- Logic separation (Instruction #3 & #4) ---
        $campaign = Campaign::updateOrCreate(['id' => $this->campaign?->id], $dbData);
        $campaign->books()->sync($validatedData['selectedBooks']);
        // --- End Logic ---

        $message = $this->campaign ? 'کمپین با موفقیت به‌روزرسانی شد.' : 'کمپین با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success'); // Instruction #18

        // Redirect back to the manager page
        $this->redirectRoute('campaigns.index');
    }

    /**
     * Renders the component view.
     */
    public function render()
    {
        return view('livewire.admin.campaign.campaign-form', [
            'platforms' => SalesPlatformEnum::cases(),
            'bookSearchResults' => $this->bookSearchResults,
        ]);
    }
}
