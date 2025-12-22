@section('title', 'مدیریت کتاب‌ها')

<div dir="rtl">
    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت کتاب‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، ویرایش و مدیریت اطلاعات کتاب‌ها.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex-none flex items-center gap-x-2">
            <x-forms.excel-export-button name="exportExcel"/>
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
            <dt class="truncate text-sm font-medium text-text-muted">تولید مشارکتی</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main">{{ number_format($stats['shared_books']) }}</dd>
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
                <button type="button" wire:click="resetFilters" class="btn btn-outline text-sm">حذف فیلترها</button>
                <x-icons.chevron-down class="h-5 w-5 transition-transform duration-200 details-open:rotate-180"/>
            </div>
        </summary>
        <div class="border-t border-border-main p-4">
            <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                {{-- Search --}}
                <div class="lg:col-span-2">
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

                <div>
                    <label for="filterFormat" class="form-label">قالب</label>
                    <select wire:model.live="filterFormat" id="filterFormat" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($bookFormats as $format)
                            <option value="{{ $format->value }}">{{ $format->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label for="filterCategory" class="form-label">دسته‌بندی</label>
                    {{-- (جدید) Search input for category --}}
                    <input type="text" wire:model.live.debounce.300ms="categorySearch" placeholder="جستجوی دسته‌بندی..." class="form-input mt-1 text-sm" />
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
                    {{-- (جدید) Search input for author --}}
                    <input type="text" wire:model.live.debounce.300ms="authorSearch" placeholder="جستجوی نویسنده..." class="form-input mt-1 text-sm" />
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
                    {{-- (جدید) Search input for translator --}}
                    <input type="text" wire:model.live.debounce.300ms="translatorSearch" placeholder="جستجوی مترجم..." class="form-input mt-1 text-sm" />
                    <select wire:model.live="filterTranslator" id="filterTranslator"
                            class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($translators as $translator)
                            <option value="{{ $translator->id }}">{{ $translator->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Other filters... --}}
                <div>
                    <label for="filterNarrator" class="form-label">گوینده</label>
                    {{-- (جدید) Search input for narrator --}}
                    <input type="text" wire:model.live.debounce.300ms="narratorSearch" placeholder="جستجوی گوینده..." class="form-input mt-1 text-sm" />
                    <select wire:model.live="filterNarrator" id="filterNarrator" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($narrators as $narrator)
                            <option value="{{ $narrator->id }}">{{ $narrator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterComposer" class="form-label">آهنگساز</label>
                    {{-- (جدید) Search input for composer --}}
                    <input type="text" wire:model.live.debounce.300ms="composerSearch" placeholder="جستجوی آهنگساز..." class="form-input mt-1 text-sm" />
                    <select wire:model.live="filterComposer" id="filterComposer" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($composers as $composer)
                            <option value="{{ $composer->id }}">{{ $composer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterPublisher" class="form-label">ناشر</label>
                    {{-- (جدید) Search input for publisher --}}
                    <input type="text" wire:model.live.debounce.300ms="publisherSearch" placeholder="جستجوی ناشر..." class="form-input mt-1 text-sm" />
                    <select wire:model.live="filterPublisher" id="filterPublisher" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($publishers as $publisher)
                            <option value="{{ $publisher->id }}">
                                {{ $publisher->name }}
                                {{-- Only show share percent if it exists --}}
                                @if(isset($publisher->share_percent))
                                    (٪{{ $publisher->share_percent }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filterPlatform" class="form-label">پلتفرم فروش (کتاب)</label>
                    <select wire:model.live="filterPlatform" id="filterPlatform" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($salesPlatforms as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterGender" class="form-label">جنسیت گوینده</label>
                    <select wire:model.live="filterGender" id="filterGender" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($genderSuitabilities as $gender)
                            <option value="{{ $gender->value }}">{{ $gender->pName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterRate" class="form-label">ریت کلی</label>
                    <select wire:model.live="filterRate" id="filterRate" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($rates as $rate)
                            <option value="{{ $rate->value }}">{{ $rate->pName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1">از تاریخ انتشار </label>
                    <x-forms.persian-date-picker
                        name="filterPublishDateFrom"
                        wire:model.live="filterPublishDateFrom"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>
                <div>
                    <label class="block mb-1">تا تاریخ انتشار </label>
                    <x-forms.persian-date-picker
                        name="filterPublishDateTo"
                        wire:model.live="filterPublishDateTo"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>

                {{-- (جدید) Payment Filters --}}
                <hr class="lg:col-span-4 my-2 border-border-main" />

                {{-- ردیف اول فیلترهای مالی --}}
                <div>
                    <label for="filterMinSalesCount" class="form-label">حداقل تعداد فروش</label>
                    <input type="number" wire:model.live.debounce.300ms="filterMinSalesCount" id="filterMinSalesCount" class="form-input mt-1" placeholder="مثلا: 100">
                </div>

                <div>
                    <label for="filterMinSalesAmount" class="form-label">حداقل مبلغ فروش (ریال)</label>
                    {{-- (اصلاح) خطای تایپی class_ به class تبدیل شد --}}
                    <input type="number" wire:model.live.debounce.300ms="filterMinSalesAmount" id="filterMinSalesAmount" class="form-input mt-1" placeholder="مثلا: 1000000">
                </div>

                <div>
                    <label for="filterPaymentPlatform" class="form-label">پلتفرم پرداخت</label>
                    <select wire:model.live="filterPaymentPlatform" id="filterPaymentPlatform" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($salesPlatforms as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- (جدید) سلول خالی برای تراز کردن گرید 4 ستونی --}}
                <div></div>

                {{-- ردیف دوم فیلترهای مالی و تگ‌ها --}}
                <div>
                    <label class="block mb-1">از تاریخ پرداخت </label>
                    <x-forms.persian-date-picker
                        name="filterPaymentDateFrom"
                        wire:model.live="filterPaymentDateFrom"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>

                <div>
                    <label class="block mb-1">تا تاریخ پرداخت </label>
                    <x-forms.persian-date-picker
                        name="filterPaymentDateTo"
                        wire:model.live="filterPaymentDateTo"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>

                {{-- فیلتر تگ‌ها (اکنون در کنار فیلترهای تاریخ قرار می‌گیرد) --}}
                <div class="lg:col-span-2">
                    <label for="filterTags" class="form-label">تگ‌ها (برای انتخاب چند مورد Ctrl را نگه دارید)</label>
                    {{-- (جدید) Search input for tags --}}
                    <input type="text" wire:model.live.debounce.300ms="tagSearch" placeholder="جستجوی تگ..." class="form-input mt-1 text-sm" />
                    <select wire:model.live="filterTags" id="filterTags" class="form-input form-select mt-1" multiple
                            size="5">
                        @foreach($allTags as $tag)
                            <option value="{{ $tag }}">{{ $tag }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </details>

    {{-- Table --}}
    <div class="mt-8 flex flex-col" dir="rtl">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    {{-- ستون برای دکمه باز/بسته کردن --}}
                    <th class="table-header-cell w-12 text-center"><span class="sr-only">جزئیات</span></th>
                    <th class="table-header-cell">
                        <x-ui.sortable column="title" :$sortCol :$sortAsc>کتاب</x-ui.sortable>
                    </th>
                    <th class="table-header-cell">دسته‌بندی</th>
                    <th class="table-header-cell">نویسنده(ها)</th>
                    <th class="table-header-cell">آخرین قیمت (ریال)</th>

                    {{-- (جدید) Sales Columns --}}
                    <th class="table-header-cell">
                        <x-ui.sortable column="sales_count" :$sortCol :$sortAsc>تعداد فروش</x-ui.sortable>
                    </th>
                    <th class="table-header-cell">
                        <x-ui.sortable column="total_amount" :$sortCol :$sortAsc>مبلغ فروش (ریال)</x-ui.sortable>
                    </th>

                    <th class="table-header-cell">
                        <x-ui.sortable column="status" :$sortCol :$sortAsc>وضعیت</x-ui.sortable>
                    </th>
                    <th class="table-header-cell">
                        <x-ui.sortable column="publish_date" :$sortCol :$sortAsc>تاریخ انتشار</x-ui.sortable>
                    </th>
                    <th class="table-header-cell text-center">عملیات</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse ($books as $book)
                    {{-- ردیف اصلی اطلاعات --}}
                    <tr wire:key="book-main-{{ $book->id }}">
                        <td class="w-16 py-4 pr-4 pl-3 text-center sm:pr-6">
                            <button
                                wire:click="toggleExpand({{ $book->id }})"
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                title="نمایش جزئیات بیشتر">

                                <svg
                                    class="h-5 w-5 transition-transform duration-300 ease-in-out @if($expandedBookId === $book->id) rotate-180 @endif"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>

                            </button>
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-sm">
                            <div class="font-medium text-gray-900" title="{{ $book->title }}">{{ \Illuminate\Support\Str::limit($book->title,40) }}</div>
                            <div class="text-gray-500 font-mono text-xs">{{ $book->financial_code }}</div>
                        </td>
                        <td class=" py-4 px-3 text-sm text-gray-500">{{ $book->category->name ?? '—' }}</td>
                        <td class=" py-4 px-3 text-sm text-gray-500">{{ $book->authors->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500 font-mono">
                            {{ $book->latestPrice ? number_format($book->latestPrice->price) : '-' }}
                        </td>

                        {{-- (جدید) Sales Data Cells --}}
                        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500 font-mono">{{ number_format($book->sales_count) }}</td>
                        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500 font-mono">{{ number_format($book->total_amount) }}</td>

                        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{!! $book->status->badge() !!}</td>
                        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{{ $book->publish_date() }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-4 pr-3 text-center text-sm font-medium sm:pl-6">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="openPriceModal({{ $book->id }})"
                                        class="text-indigo-600 hover:text-indigo-900" title="مدیریت قیمت">
                                    <x-icons.dollar class="h-5 w-5"/>
                                </button>
                                <a href="{{ route('book.edit', ['book' => $book->id]) }}"
                                   class="text-indigo-600 hover:text-indigo-900" title="ویرایش">
                                    <x-icons.edit class="h-5 w-5"/>
                                </a>
                                <button wire:click="deleteBook({{ $book->id }})"
                                        wire:confirm="آیا از حذف دائمی این کتاب اطمینان دارید؟"
                                        class="text-red-600 hover:text-red-900" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- ردیف جزئیات (نسخه بهبود یافته) --}}
                    @if ($expandedBookId === $book->id)
                        <tr wire:key="book-details-{{ $book->id }}" class="bg-white">
                            {{-- (تغییر) Colspan updated --}}
                            <td colspan="10" class="p-4 sm:p-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                                    {{-- بخش اول: اطلاعات مالی و مشخصات اصلی --}}
                                    <div class="lg:col-span-2 space-y-6">
                                        {{-- کارت اطلاعات مالی --}}
                                        <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                            <h3 class="mb-4 text-base font-semibold text-gray-800 border-b pb-2">
                                                اطلاعات مالی
                                            </h3>
                                            <dl class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">قیمت چاپی</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->print_price ? number_format($book->print_price) . ' ریال' : '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">ریت کلی </dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->rate ?? '—'}}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">قیمت پیشنهادی</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->suggested_price ? number_format($book->suggested_price) . ' ریال' : '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">نقطه سر به سر</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->breakeven_sales_count ?? '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">حداکثر تخفیف</dt>
                                                    <dd class="mt-1 text-sm font-semibold text-rose-600">{{ $book->max_discount ? $book->max_discount . '٪' : '—' }}</dd>
                                                </div>
                                            </dl>
                                        </div>

                                        {{-- کارت مشخصات کتاب --}}
                                        <div class="p-4 bg-white border border-gray-200 rounded-lg">
                                            <h3 class="mb-4 text-base font-semibold text-gray-800 border-b pb-2">
                                                مشخصات
                                            </h3>
                                            <dl class="grid grid-cols-1 sm:grid-cols-4 gap-x-6 gap-y-5">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">جنسیت گوینده</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->gender_suitability->pName() ?? '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">تعداد ترک</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->track_count ?? '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">مدت زمان (دقیقه)</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->duration ?? '—' }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">صفحات نسخه چاپی</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $book->print_pages ?? '—' }}</dd>
                                                </div>
                                            </dl>
                                        </div>

                                        {{-- کارت توضیحات و تگ‌ها --}}
                                        <div class="p-4 bg-white border border-gray-200 rounded-lg space-y-4">

                                            {{-- بخش توضیحات --}}
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800 mb-2">توضیحات</h4>
                                                <p class=" text-sm text-gray-700 leading-relaxed">
                                                    {{-- اگر توضیحات وجود نداشت، خط تیره نمایش داده می‌شود --}}
                                                    {{ $book->description ?? '—' }}
                                                </p>
                                            </div>

                                            {{-- بخش تگ‌ها --}}
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800 mb-2">تگ‌ها</h4>
                                                <div class="flex flex-wrap gap-2">
                                                    @forelse (is_array($book->tags) ? $book->tags : [] as $tag)
                                                        <div
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $tag }}
                                                        </div>
                                                    @empty
                                                        {{-- اگر آرایه تگ‌ها خالی بود، این بخش اجرا می‌شود --}}
                                                        <span class="text-sm text-gray-500">—</span>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800 mb-2">عنوان در طاقچه</h4>
                                                <p class=" text-sm text-gray-700 leading-relaxed">
                                                    {{ $book->taghche_title ?? '—' }}
                                                </p>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- ستون دوم: شناسه‌ها و عوامل --}}
                                    <div class="p-4 bg-white border border-gray-200 rounded-lg">

                                        {{-- بخش شناسه‌های پلتفرم  --}}
                                        <div>
                                            <h3 class="mb-3 text-base font-semibold text-gray-800">شناسه‌ها</h3>
                                            @php
                                                $platforms = [
                                                    'نوین کتاب' => 'novinketab_book_id', 'فیدیبو' => 'fidibo_book_id', 'طاقچه' => 'taghcheh_book_id',
                                                    'نوار' => 'navar_book_id', 'کتابراه' => 'ketabrah_book_id',
                                                ];
                                            @endphp
                                            {{-- با space-y-4 فاصله بین هر آیتم را بیشتر می‌کنیم --}}
                                            <dl class="space-y-2">
                                                @foreach ($platforms as $name => $property)
                                                    {{-- دیگر نیازی به div با کلاس flex نیست --}}
                                                    <div>
                                                        <dt class="text-sm font-medium text-gray-500">{{ $name }}</dt>
                                                        <dd class="mt-1 text-sm text-gray-900 font-mono tracking-wider">
                                                            {{ $book->{$property} ?? '—' }}
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>

                                        {{-- جداکننده --}}
                                        <hr class="my-4">

                                        {{-- بخش عوامل --}}
                                        <div>
                                            <h3 class="mb-4 text-base font-semibold text-gray-800">عوامل</h3>
                                            @php
                                                // فیلتر کردن عواملی که خالی نیستند
                                                $contributors = collect([
                                                    'مترجم(ها)' => $book->translators, 'گوینده(ها)' => $book->narrators,
                                                    'آهنگساز(ها)' => $book->composers, 'تدوینگر(ها)' => $book->editors,
                                                    'ناشر(ها)' => $book->publishers,
                                                ])->filter(fn($people) => $people->isNotEmpty());
                                            @endphp

                                            <div class="space-y-3">
                                                @forelse ($contributors as $role => $people)
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-500">{{ $role }}</h4>
                                                        <div class="flex flex-wrap gap-2 mt-1">
                                                            @foreach ($people as $person)
                                                                <div
                                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                                                    {{ $person->name }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-sm text-gray-500">هیچ عاملی ثبت نشده است.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        {{-- (تغییر) Colspan updated --}}
                        <td class="py-12 text-center text-gray-500" colspan="10">هیچ کتابی یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($books->hasPages())
            <div class="mt-4">{{ $books->links() }}</div>
        @endif
    </div>

    {{-- Price Management Modal --}}
    <x-dialog wire:model="showPriceModal" maxWidth="2xl">
        <x-dialog.panel>
            <div class="p-6">
                <h3 class="text-lg font-medium text-text-main">مدیریت قیمت برای: <span
                        class="font-bold">{{ $pricingBook?->title }}</span></h3>
                <form wire:submit="saveNewPrice" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="new_price" class="form-label">قیمت جدید (ریال)</label>
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
                                <th class="table-header-cell">قیمت (ریال)</th>
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
                                <tr>
                                    <td class="table-cell-muted text-center py-4" colspan="3">تاریخچه قیمتی وجود
                                        ندارد.
                                    </td>
                                </tr>
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
