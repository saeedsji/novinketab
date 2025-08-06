@section('title', 'مدیریت کتاب‌ها')

<div dir="rtl">
    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت کتاب‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، ویرایش و مدیریت اطلاعات کتاب‌ها.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex-none">
            <button wire:click="createBook" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن کتاب</span>
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mt-6 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="search" class="form-label">جستجو (عنوان یا کد مالی)</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" class="form-input mt-1">
            </div>
            <div>
                <label for="filterStatus" class="form-label">وضعیت</label>
                <select wire:model.live="filterStatus" id="filterStatus" class="form-input form-select mt-1">
                    <option value="">همه</option>
                    @foreach($bookStatuses as $status)
                        <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterCategory" class="form-label">دسته‌بندی</label>
                <select wire:model.live="filterCategory" id="filterCategory" class="form-input form-select mt-1">
                    <option value="">همه</option>
                    @foreach($categoryTree as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-8 flex flex-col">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    <th class="table-header-cell"><x-ui.sortable column="title" :$sortCol :$sortAsc>کتاب</x-ui.sortable></th>
                    <th class="table-header-cell">دسته‌بندی</th>
                    <th class="table-header-cell">نویسنده(ها)</th>
                    <th class="table-header-cell"><x-ui.sortable column="status" :$sortCol :$sortAsc>وضعیت</x-ui.sortable></th>
                    <th class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($books as $book)
                    <tr wire:key="book-{{ $book->id }}" class="table-row">
                        <td class="table-cell">
                            <div class="font-medium text-text-main">{{ $book->title }}</div>
                            <div class="text-text-muted font-mono">{{ $book->financial_code }}</div>
                        </td>
                        <td class="table-cell-muted">{{ $book->category->name ?? '—' }}</td>
                        <td class="table-cell-muted">{{ $book->authors->pluck('name')->join(', ') }}</td>
                        <td class="table-cell-muted">{!! $book->status->badge() !!}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="openPriceModal({{ $book->id }})" class="btn-link-secondary" title="مدیریت قیمت"><x-icons.check-circle class="h-5 w-5"/></button>
                                <button wire:click="editBook({{ $book->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                <button wire:click="deleteBook({{ $book->id }})" wire:confirm="آیا از حذف دائمی این کتاب اطمینان دارید؟" class="btn-link-danger" title="حذف"><x-icons.trash-2 class="h-5 w-5"/></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="5">هیچ کتابی یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($books->hasPages())
            <div class="mt-4">{{ $books->links() }}</div>
        @endif
    </div>

    {{-- Book Add/Edit Modal --}}
    <x-dialog wire:model="showBookModal" maxWidth="5xl">
        <x-dialog.panel>
            <form wire:submit="saveBook">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $bookModalTitle }}</h3>
                    <div class="mt-6 space-y-8">

                        {{-- Section 1: Basic Info --}}
                        <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                            <legend class="text-base font-semibold text-text-main px-2">اطلاعات پایه</legend>
                            <div class="sm:col-span-6 md:col-span-4">
                                <label for="title" class="form-label">عنوان کتاب</label>
                                <input type="text" wire:model="title" id="title" class="form-input mt-1">
                                @error('title') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-6 md:col-span-2">
                                <label for="financial_code" class="form-label">کد مالی</label>
                                <input type="text" wire:model="financial_code" id="financial_code" class="form-input mt-1">
                                @error('financial_code') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="category_id" class="form-label">دسته‌بندی</label>
                                <select wire:model="category_id" id="category_id" class="form-input form-select mt-1">
                                    <option value="">انتخاب کنید</option>
                                    @foreach($categoryTree as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="status" class="form-label">وضعیت</label>
                                <select wire:model="status" id="status" class="form-input form-select mt-1">
                                    @foreach($bookStatuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>

                        {{-- Section 2: People --}}
                        <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                            <legend class="text-base font-semibold text-text-main px-2">عوامل</legend>
                            <div class="sm:col-span-12 md:col-span-6">
                                <label for="selectedAuthors" class="form-label">نویسنده(ها)</label>
                                <select id="selectedAuthors" wire:model="selectedAuthors" multiple class="form-input form-select mt-1 h-32">
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedAuthors') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-12 md:col-span-6">
                                <label for="selectedPublishers" class="form-label">ناشر(ها)</label>
                                <select id="selectedPublishers" wire:model="selectedPublishers" multiple class="form-input form-select mt-1 h-32">
                                    @foreach($publishers as $publisher)
                                        <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-12 md:col-span-4">
                                <label for="selectedTranslators" class="form-label">مترجم(ها)</label>
                                <select id="selectedTranslators" wire:model="selectedTranslators" multiple class="form-input form-select mt-1 h-32">
                                    @foreach($translators as $translator)
                                        <option value="{{ $translator->id }}">{{ $translator->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-12 md:col-span-4">
                                <label for="selectedNarrators" class="form-label">گوینده(ها)</label>
                                <select id="selectedNarrators" wire:model="selectedNarrators" multiple class="form-input form-select mt-1 h-32">
                                    @foreach($narrators as $narrator)
                                        <option value="{{ $narrator->id }}">{{ $narrator->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-12 md:col-span-4">
                                <label for="selectedComposers" class="form-label">آهنگساز(ها)</label>
                                <select id="selectedComposers" wire:model="selectedComposers" multiple class="form-input form-select mt-1 h-32">
                                    @foreach($composers as $composer)
                                        <option value="{{ $composer->id }}">{{ $composer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>

                        {{-- Section 3: Financial Info --}}
                        <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                            <legend class="text-base font-semibold text-text-main px-2">اطلاعات مالی و فروش</legend>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="estimated_cost" class="form-label">هزینه تخمینی</label>
                                <input type="number" wire:model="estimated_cost" id="estimated_cost" class="form-input mt-1">
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="suggested_price" class="form-label">قیمت پیشنهادی</label>
                                <input type="number" wire:model="suggested_price" id="suggested_price" class="form-input mt-1">
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="max_discount" class="form-label">حداکثر تخفیف (%)</label>
                                <input type="number" wire:model="max_discount" id="max_discount" class="form-input mt-1">
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label for="breakeven_sales_count" class="form-label">نقطه سر به سر (تعداد)</label>
                                <input type="number" wire:model="breakeven_sales_count" id="breakeven_sales_count" class="form-input mt-1">
                            </div>
                        </fieldset>

                        {{-- Section 4: Details --}}
                        <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                            <legend class="text-base font-semibold text-text-main px-2">جزئیات و مشخصات</legend>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label class="form-label">قالب‌های کتاب</label>
                                <div class="mt-2 space-y-2">
                                    @foreach($bookFormatsEnum as $format)
                                        <div class="flex items-center">
                                            <input id="format-{{$format->value}}" wire:model="formats" value="{{$format->value}}" type="checkbox" class="form-checkbox">
                                            <label for="format-{{$format->value}}" class="mr-2 text-sm text-text-main">{{$format->pName()}}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="sm:col-span-6 md:col-span-3">
                                <label class="form-label">پلتفرم‌های فروش</label>
                                <div class="mt-2 space-y-2">
                                    @foreach($salesPlatformsEnum as $platform)
                                        <div class="flex items-center">
                                            <input id="platform-{{$platform->value}}" wire:model="sales_platforms" value="{{$platform->value}}" type="checkbox" class="form-checkbox">
                                            <label for="platform-{{$platform->value}}" class="mr-2 text-sm text-text-main">{{$platform->pName()}}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="sm:col-span-12 md:col-span-6">
                                <label for="tags" class="form-label">تگ‌ها (با Enter جدا کنید)</label>
                                <input type="text" wire:model="tags" id="tags" class="form-input mt-1" placeholder="تگ ۱, تگ ۲, ...">
                            </div>
                            <div class="sm:col-span-12">
                                <label for="description" class="form-label">توضیحات</label>
                                <textarea wire:model="description" id="description" rows="4" class="form-input mt-1"></textarea>
                            </div>
                        </fieldset>

                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره کتاب</button>
                    <button type="button" wire:click="$set('showBookModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>

    {{-- Price Management Modal --}}
    <x-dialog wire:model="showPriceModal" maxWidth="2xl">
        <x-dialog.panel>
            <div class="p-6">
                <h3 class="text-lg font-medium text-text-main">مدیریت قیمت برای: <span class="font-bold">{{ $pricingBook?->title }}</span></h3>
                <form wire:submit="saveNewPrice" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="new_price" class="form-label">قیمت جدید (تومان)</label>
                        <input type="number" wire:model="new_price" id="new_price" class="form-input mt-1">
                        @error('new_price') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="effective_date" class="form-label">تاریخ اعمال</label>
                        <input type="date" wire:model="effective_date" id="effective_date" class="form-input mt-1">
                        @error('effective_date') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:pt-7">
                        <button type="submit" class="btn btn-secondary w-full">افزودن قیمت</button>
                    </div>
                </form>
                <div class="mt-8">
                    <h4 class="text-md font-medium text-text-main">تاریخچه قیمت‌ها</h4>
                    <div class="table-container mt-4">
                        <table class="table-main">
                            <thead class="table-header">
                            <tr>
                                <th class="table-header-cell">قیمت (تومان)</th>
                                <th class="table-header-cell">تاریخ اعمال</th>
                                <th class="table-header-cell">ثبت توسط</th>
                            </tr>
                            </thead>
                            <tbody class="table-body">
                            @forelse($pricingBook?->prices ?? [] as $price)
                                <tr class="table-row">
                                    <td class="table-cell font-mono">{{ number_format($price->price) }}</td>
                                    <td class="table-cell-muted">{{ \Morilog\Jalali\Jalalian::forge($price->effective_date)->format('Y/m/d') }}</td>
                                    <td class="table-cell-muted">{{ $price->user->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td class="table-cell-muted text-center py-4" colspan="3">تاریخچه قیمتی وجود ندارد.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-surface-secondary px-4 py-3 sm:px-6 flex justify-end">
                <button type="button" wire:click="$set('showPriceModal', false)" class="btn btn-outline">بستن</button>
            </div>
        </x-dialog.panel>
    </x-dialog>
</div>
