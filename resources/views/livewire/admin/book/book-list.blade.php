@section('title', 'مدیریت کتاب‌ها')

<div dir="rtl">
    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت کتاب‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، ویرایش و مدیریت اطلاعات کتاب‌ها.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex-none">
            <a href="{{ route('book.create') }}" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن کتاب</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">کل کتاب‌ها (فیلتر شده)</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main">{{ number_format($stats['total_books']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">منتشر شده</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main">{{ number_format($stats['published_books']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">پیش‌نویس</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main">{{ number_format($stats['draft_books']) }}</dd>
        </div>
        <div class="card p-4">
            <dt class="truncate text-sm font-medium text-text-muted">لغو شده</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main">{{ number_format($stats['canceled_books']) }}</dd>
        </div>
    </dl>

    {{-- Filters --}}
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
                {{-- Search --}}
                <div>
                    <label for="search" class="form-label">جستجو (عنوان یا کد مالی)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search" class="form-input mt-1">
                </div>

                {{-- Status --}}
                <div>
                    <label for="filterStatus" class="form-label">وضعیت</label>
                    <select wire:model.live="filterStatus" id="filterStatus" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($bookStatuses as $status)
                            <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label for="filterCategory" class="form-label">دسته‌بندی</label>
                    <select wire:model.live="filterCategory" id="filterCategory" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Author --}}
                <div>
                    <label for="filterAuthor" class="form-label">نویسنده</label>
                    <select wire:model.live="filterAuthor" id="filterAuthor" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Translator --}}
                <div>
                    <label for="filterTranslator" class="form-label">مترجم</label>
                    <select wire:model.live="filterTranslator" id="filterTranslator" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($translators as $translator)
                            <option value="{{ $translator->id }}">{{ $translator->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Narrator --}}
                <div>
                    <label for="filterNarrator" class="form-label">گوینده</label>
                    <select wire:model.live="filterNarrator" id="filterNarrator" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($narrators as $narrator)
                            <option value="{{ $narrator->id }}">{{ $narrator->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Composer --}}
                <div>
                    <label for="filterComposer" class="form-label">آهنگساز</label>
                    <select wire:model.live="filterComposer" id="filterComposer" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($composers as $composer)
                            <option value="{{ $composer->id }}">{{ $composer->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Publisher --}}
                <div>
                    <label for="filterPublisher" class="form-label">ناشر</label>
                    <select wire:model.live="filterPublisher" id="filterPublisher" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($publishers as $publisher)
                            <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Format --}}
                <div>
                    <label for="filterFormat" class="form-label">قالب</label>
                    <select wire:model.live="filterFormat" id="filterFormat" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($bookFormats as $format)
                            <option value="{{ $format->value }}">{{ $format->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Platform --}}
                <div>
                    <label for="filterPlatform" class="form-label">پلتفرم فروش</label>
                    <select wire:model.live="filterPlatform" id="filterPlatform" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($salesPlatforms as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Listener Type --}}
                <div>
                    <label for="filterListenerType" class="form-label">نوع مخاطب</label>
                    <select wire:model.live="filterListenerType" id="filterListenerType" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($listenerTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender --}}
                <div>
                    <label for="filterGender" class="form-label">مناسب برای جنسیت</label>
                    <select wire:model.live="filterGender" id="filterGender" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($genderSuitabilities as $gender)
                            <option value="{{ $gender->value }}">{{ $gender->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Publish Date From --}}
                <div>
                    <label for="filterPublishDateFrom" class="form-label">تاریخ انتشار از</label>
                    <input type="date" wire:model.live="filterPublishDateFrom" id="filterPublishDateFrom" class="form-input mt-1">
                </div>

                {{-- Publish Date To --}}
                <div>
                    <label for="filterPublishDateTo" class="form-label">تاریخ انتشار تا</label>
                    <input type="date" wire:model.live="filterPublishDateTo" id="filterPublishDateTo" class="form-input mt-1">
                </div>

            </div>
        </div>
    </details>

    {{-- Table --}}
    <div class="mt-8 flex flex-col">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    <th class="table-header-cell">
                        <x-ui.sortable column="title" :$sortCol :$sortAsc>کتاب</x-ui.sortable>
                    </th>
                    <th class="table-header-cell">دسته‌بندی</th>
                    <th class="table-header-cell">نویسنده(ها)</th>
                    <th class="table-header-cell">آخرین قیمت (ریال)</th>
                    <th class="table-header-cell">
                        <x-ui.sortable column="status" :$sortCol :$sortAsc>وضعیت</x-ui.sortable>
                    </th>
                    <th class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($books as $book)
                    <tr wire:key="book-{{ $book->id }}" class="table-row">
                        <td class="table-cell">
                            <div class="font-medium text-text-main">{{ $book->title }}</div>
                            <div class="text-text-muted font-mono text-xs">{{ $book->financial_code }}</div>
                        </td>
                        <td class="table-cell-muted">{{ $book->category->name ?? '—' }}</td>
                        <td class="table-cell-muted text-xs">{{ $book->authors->pluck('name')->join(', ') }}</td>
                        <td class="table-cell-muted font-mono">
                            @if($book->latestPrice)
                                {{ number_format($book->latestPrice->price)}}
                            @else
                                -
                            @endif
                        </td>
                        <td class="table-cell-muted">{!! $book->status->badge() !!}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="openPriceModal({{ $book->id }})" class="btn-link-secondary" title="مدیریت قیمت">
                                    <x-icons.check-circle class="h-5 w-5"/>
                                </button>
                                <a href="{{ route('book.edit', ['book' => $book->id]) }}" class="btn-link" title="ویرایش">
                                    <x-icons.edit class="h-5 w-5"/>
                                </a>
                                <button wire:click="deleteBook({{ $book->id }})"
                                        wire:confirm="آیا از حذف دائمی این کتاب اطمینان دارید؟" class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="6">هیچ کتابی یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($books->hasPages())
            <div class="mt-4">{{ $books->links() }}</div>
        @endif
    </div>

    {{-- Price Management Modal (Unchanged) --}}
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
