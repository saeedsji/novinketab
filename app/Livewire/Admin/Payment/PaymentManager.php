<?php

namespace App\Livewire\Admin\Payment;

use App\Enums\Book\SalesPlatformEnum;
use App\Exports\PaymentsExport;
use App\Models\Author;
use App\Models\Category;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;

class PaymentManager extends Component
{
    use WithPagination;

    // Properties for Payment Management
    public ?Payment $editingPayment = null;
    public ?int $book_id = null;
    public ?int $sale_platform = null;
    public ?string $platform_id = '';
    public ?string $sale_date = '';
    public ?int $amount = 0;
    public ?int $publisher_share = 0;
    public ?int $platform_share = 0;
    public ?int $discount = 0;
    public ?int $tax = 0;

    // Modals & Titles
    public bool $showPaymentModal = false;
    public string $paymentModalTitle = '';

    // Sorting
    public string $sortCol = 'sale_date';
    public bool $sortAsc = false;

    // Filter Properties
    public string $search = '';
    public string $filterPlatform = '';
    public ?string $filterDateFrom = '';
    public ?string $filterDateTo = '';
    public string $filterAuthor = '';
    public string $filterCategory = '';
    public ?int $filterAmountMin = null;
    public ?int $filterAmountMax = null;


    protected function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'sale_platform' => ['required', Rule::enum(SalesPlatformEnum::class)],
            'platform_id' => 'nullable|string|max:255',
            'sale_date' => 'required|date',
            'amount' => 'required|integer|min:0',
            'publisher_share' => 'required|integer|min:0',
            'platform_share' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'tax' => 'nullable|integer|min:0',
        ];
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'filter') || $propertyName === 'search') {
            $this->resetPage();
        }
    }

    public function createPayment(): void
    {
        $this->resetPaymentForm();
        $this->paymentModalTitle = 'ایجاد پرداخت جدید';
        $this->showPaymentModal = true;
    }

    public function editPayment(Payment $payment): void
    {
        $this->resetPaymentForm();
        $this->editingPayment = $payment;
        $this->book_id = $payment->book_id;
        $this->sale_platform = $payment->sale_platform;
        $this->platform_id = $payment->platform_id;
        $this->sale_date = $payment->sale_date->format('Y-m-d\TH:i');
        $this->amount = $payment->amount;
        $this->publisher_share = $payment->publisher_share;
        $this->platform_share = $payment->platform_share;
        $this->discount = $payment->discount;
        $this->tax = $payment->tax;
        $this->paymentModalTitle = 'ویرایش پرداخت';
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $validatedData = $this->validate();
        Payment::updateOrCreate(['id' => $this->editingPayment?->id], $validatedData);
        $message = $this->editingPayment ? 'پرداخت با موفقیت به‌روزرسانی شد.' : 'پرداخت با موفقیت ایجاد شد.';
        $this->dispatch('toast', text: $message, icon: 'success');
        $this->showPaymentModal = false;
    }

    public function deletePayment(Payment $payment): void
    {
        $payment->delete();
        $this->dispatch('toast', text: 'پرداخت با موفقیت حذف شد.', icon: 'success');
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

    public function resetFilters(): void
    {
        $this->reset('search', 'filterPlatform', 'filterDateFrom', 'filterDateTo',
            'filterAuthor', 'filterCategory', 'filterAmountMin', 'filterAmountMax');
        $this->resetPage();
    }

    public function resetPaymentForm(): void
    {
        $this->resetValidation();
        $this->reset('editingPayment', 'book_id', 'sale_platform', 'platform_id', 'sale_date',
            'amount', 'publisher_share', 'platform_share', 'discount', 'tax');
    }

    /**
     * Creates and returns the base query for payments with all filters applied.
     */
    protected function getPaymentsQuery(): Builder
    {
        $filterDateFrom = $this->filterDateFrom
            ? Jalalian::fromFormat('Y/m/d', $this->filterDateFrom)->toCarbon()->format('Y-m-d')
            : null;

        $filterDateTo = $this->filterDateTo
            ? Jalalian::fromFormat('Y/m/d', $this->filterDateTo)->toCarbon()->format('Y-m-d')
            : null;

        return Payment::query()
            ->with('book') // Eager load book to prevent N+1 queries
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('platform_id', 'like', '%' . $search . '%')
                        ->orWhereHas('book', function ($sub) use ($search) {
                            $sub->where('title', 'like', '%' . $search . '%')
                                ->orWhere('financial_code', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($this->filterPlatform, fn($query, $platform) => $query->where('sale_platform', $platform))
            ->when($this->filterDateFrom, fn($query) => $query->where('sale_date', '>=', $filterDateFrom))
            ->when($this->filterDateTo, fn($query) => $query->where('sale_date', '<=', $filterDateTo))
            ->when($this->filterAmountMin, fn($query) => $query->where('amount', '>=', $this->filterAmountMin))
            ->when($this->filterAmountMax, fn($query) => $query->where('amount', '<=', $this->filterAmountMax))
            ->when($this->filterAuthor, fn($query, $authorId) => $query->whereHas('book.authors', fn($q) => $q->where('id', $authorId)))
            ->when($this->filterCategory, fn($query, $categoryId) => $query->whereHas('book.category', fn($q) => $q->where('id', $categoryId)));
    }

    /**
     * Exports the filtered data to an Excel file.
     */
    public function exportExcel()
    {
        $query = $this->getPaymentsQuery()->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');

        return Excel::download(
            new PaymentsExport($query),
            'payments-' . now()->format('Y-m-d-H-i') . '.xlsx'
        );
    }

    public function render()
    {
        $paymentsQuery = $this->getPaymentsQuery();

        // Calculate stats based on the filtered query
        $statsQuery = clone $paymentsQuery;
        $stats = [
            'total_count' => $statsQuery->count(),
            'total_amount' => $statsQuery->sum('amount'),
            'total_publisher_share' => $statsQuery->sum('publisher_share'),
            'average_amount' => $statsQuery->avg('amount'),
        ];

        $payments = $paymentsQuery
            ->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(15);

        return view('livewire.admin.payment.payment-manager', [
            'payments' => $payments,
            'stats' => $stats,
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::whereNull('parent_id')->with('children')->get(),
        ]);
    }
}
