@section('title', 'مدیریت پرداخت‌ها')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت پرداخت‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">لیست تمام پرداخت‌های ثبت شده در سیستم.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex-none flex items-center gap-x-2">
            <x-forms.excel-export-button name="exportExcel"/>
            <button wire:click="createPayment" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن پرداخت</span>
            </button>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Stats Section --}}
    {{-- =================================================================== --}}
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">تعداد کل فروش</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">{{ number_format($stats['total_count']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">مجموع مبلغ فروش (ریال)</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">{{ number_format($stats['total_amount']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">مجموع سهم ناشر (ریال)</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">{{ number_format($stats['total_publisher_share']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">میانگین مبلغ فروش (ریال)</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">{{ number_format((int)$stats['average_amount']) }}</dd>
        </div>
    </dl>

    {{-- =================================================================== --}}
    {{-- Filters Section --}}
    {{-- =================================================================== --}}
    <details class="card mt-6 overflow-hidden" open>
        <summary class="p-4 cursor-pointer flex justify-between items-center">
            <h3 class="text-lg font-medium text-text-main">فیلترهای پیشرفته</h3>
            <div class="flex items-center gap-x-4">
                <button type="button" wire:click="resetFilters" class="btn-link-secondary text-sm">حذف فیلترها</button>
                <x-icons.chevron-down class="h-5 w-5 transition-transform duration-200 details-open:rotate-180"/>
            </div>
        </summary>
        <div class="border-t border-border-main p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <div>
                    <label for="search" class="form-label">جستجو (عنوان/کد کتاب، شناسه پلتفرم)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search" class="form-input mt-1">
                </div>
                <div>
                    <label for="filterPlatform" class="form-label">پلتفرم فروش</label>
                    <select wire:model.live="filterPlatform" id="filterPlatform" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach(\App\Enums\Book\SalesPlatformEnum::cases() as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterAuthor" class="form-label">نویسنده</label>
                    <select wire:model.live="filterAuthor" id="filterAuthor" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterCategory" class="form-label">دسته‌بندی کتاب</label>
                    <select wire:model.live="filterCategory" id="filterCategory" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                        <label class="block mb-1">از تاریخ فروش </label>
                        <x-forms.persian-date-picker
                            name="filterDateFrom"
                            wire:model.live="filterDateFrom"
                            :value="null"
                            :options="['time' => false, 'persianDigits' => true]"
                        />
                    </div>
                <div>
                    <label class="block mb-1">تا تاریخ فروش </label>
                    <x-forms.persian-date-picker
                        name="filterDateTo"
                        wire:model.live="filterDateTo"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>
                <div>
                    <label for="filterAmountMin" class="form-label">حداقل مبلغ (ریال)</label>
                    <input type="number" wire:model.live.debounce.500ms="filterAmountMin" id="filterAmountMin" class="form-input mt-1" placeholder="مثلا 100000">
                </div>
                <div>
                    <label for="filterAmountMax" class="form-label">حداکثر مبلغ (ریال)</label>
                    <input type="number" wire:model.live.debounce.500ms="filterAmountMax" id="filterAmountMax" class="form-input mt-1" placeholder="مثلا 500000">
                </div>
            </div>
        </div>
    </details>

    {{-- =================================================================== --}}
    {{-- Table Section --}}
    {{-- =================================================================== --}}
    <div class="mt-8 flex flex-col">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    <th class="table-header-cell"><x-ui.sortable column="book_id" :$sortCol :$sortAsc>کتاب</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="sale_platform" :$sortCol :$sortAsc>پلتفرم</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="sale_date" :$sortCol :$sortAsc>تاریخ فروش</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="amount" :$sortCol :$sortAsc>مبلغ</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="publisher_share" :$sortCol :$sortAsc>سهم ناشر</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="platform_share" :$sortCol :$sortAsc>سهم پلتفرم</x-ui.sortable></th>
                    <th class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($payments as $payment)
                    <tr wire:key="payment-{{ $payment->id }}" class="table-row">
                        <td class="table-cell">
                            <div class="font-medium text-text-main">{{ $payment->book->title ?? '-' }}</div>
                            <div class="text-text-muted text-sm font-mono">{{ $payment->book->financial_code ?? '-' }}</div>
                        </td>
                        <td class="table-cell-muted">
                            <div>{{ \App\Enums\Book\SalesPlatformEnum::from($payment->sale_platform)->pName() }}</div>
                            <div class="text-xs font-mono">شناسه: {{ $payment->platform_id }}</div>
                        </td>
                        <td class="table-cell-muted">{{ \Morilog\Jalali\Jalalian::forge($payment->sale_date)->format('Y/m/d H:i') }}</td>
                        <td class="table-cell font-mono">{{ number_format($payment->amount) }} <span class="text-xs">ریال</span></td>
                        <td class="table-cell-muted font-mono">{{ number_format($payment->publisher_share) }} <span class="text-xs">ریال</span></td>
                        <td class="table-cell-muted font-mono">{{ number_format($payment->platform_share) }} <span class="text-xs">ریال</span></td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="editPayment({{ $payment->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="آیا از حذف دائمی این آیتم اطمینان دارید؟" class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="7">هیچ پرداختی با این مشخصات یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="mt-4">{{ $payments->links() }}</div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- Payment Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showPaymentModal" maxWidth="3xl">
        <x-dialog.panel>
            <form wire:submit="savePayment">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $paymentModalTitle }}</h3>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 md:grid-cols-3 sm:gap-x-4">
                        <div class="sm:col-span-3">
                            <label for="book_id" class="form-label">کتاب</label>
                            <div class="mt-1">
                                {{-- Assuming livewire:shared.book-selector is a component you have for selecting books --}}
                                @livewire('shared.book-selector', ['selectedBookId' => $book_id], key('book-selector-'.$book_id))
                            </div>
                            @error('book_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="sale_platform" class="form-label">پلتفرم فروش</label>
                            <select wire:model="sale_platform" id="sale_platform" class="form-input form-select mt-1">
                                <option value="">انتخاب کنید...</option>
                                @foreach(\App\Enums\Book\SalesPlatformEnum::cases() as $platform)
                                    <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                                @endforeach
                            </select>
                            @error('sale_platform') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="platform_id" class="form-label">شناسه پلتفرم</label>
                            <input type="text" wire:model="platform_id" id="platform_id" class="form-input mt-1">
                            @error('platform_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="sale_date" class="form-label">تاریخ و زمان فروش</label>
                            <input type="datetime-local" wire:model="sale_date" id="sale_date" class="form-input mt-1">
                            @error('sale_date') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="amount" class="form-label">مبلغ کل (ریال)</label>
                            <input type="number" id="amount" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="amount">
                            @error('amount') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="publisher_share" class="form-label">سهم ناشر (ریال)</label>
                            <input type="number" id="publisher_share" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="publisher_share">
                            @error('publisher_share') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="platform_share" class="form-label">سهم پلتفرم (ریال)</label>
                            <input type="number" id="platform_share" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="platform_share">
                            @error('platform_share') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="discount" class="form-label">تخفیف (ریال)</label>
                            <input type="number" id="discount" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="discount">
                            @error('discount') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="tax" class="form-label">مالیات (ریال)</label>
                            <input type="number" id="tax" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="tax">
                            @error('tax') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>
                {{-- Modal footer buttons --}}
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <span>ذخیره پرداخت</span>
                        <x-icons.spinner wire:loading wire:target="savePayment" class="h-4 w-4"/>
                    </button>
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>
</div>
