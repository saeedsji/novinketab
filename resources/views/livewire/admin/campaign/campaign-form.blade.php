@section('title', $pageTitle)

{{--
    This is the new full-page form for creating/editing campaigns.
    It features a two-column layout for a better user experience.
--}}
<div dir="rtl">
    <form wire:submit="save">
        {{-- =================================================================== --}}
        {{-- Header Section --}}
        {{-- =================================================================== --}}
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold text-text-main">{{ $pageTitle }}</h1>
                <p class="mt-2 text-sm text-text-muted">اطلاعات کمپین و کتاب‌های شامل آن را مدیریت کنید.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex-none flex items-center gap-x-2">
                <a href="{{ route('campaigns.index') }}" wire:navigate class="btn btn-outline w-full sm:w-auto">
                    <span>انصراف</span>
                </a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <span>ذخیره کمپین</span>
                    <x-icons.spinner wire:loading wire:target="save" class="h-4 w-4"/>
                </button>
            </div>
        </div>

        {{-- =================================================================== --}}
        {{-- Form Layout (Two-Column) --}}
        {{-- =================================================================== --}}
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Form Card (Column 1) --}}
            <div class="lg:col-span-2 card">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">اطلاعات کمپین</h3>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="form-label">نام کمپین</label>
                            <input type="text" wire:model="name" id="name" class="form-input mt-1">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="platform" class="form-label">پلتفرم</label>
                            <select wire:model="platform" id="platform" class="form-input form-select mt-1">
                                <option value="">انتخاب کنید...</option>
                                @foreach($platforms as $platformCase)
                                    <option value="{{ $platformCase->value }}">{{ $platformCase->pName() }}</option>
                                @endforeach
                            </select>
                            @error('platform') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="discount_percent" class="form-label">درصد تخفیف</label>
                            <input type="number" id="discount_percent" class="form-input mt-1 text-left" dir="ltr"
                                   wire:model="discount_percent" min="0" max="100">
                            @error('discount_percent') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block mb-1">تاریخ شروع</label>
                            <x-forms.persian-date-picker
                                name="start_date"
                                wire:model="start_date"
                                :value="$start_date"
                                :options="['time' => false, 'persianDigits' => true]"
                            />
                            @error('start_date') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block mb-1">تاریخ پایان</label>
                            <x-forms.persian-date-picker
                                name="end_date"
                                wire:model="end_date"
                                :value="$end_date"
                                :options="['time' => false, 'persianDigits' => true]"
                            />
                            @error('end_date') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Book Management Card (Column 2) --}}
            <div class="lg:col-span-1 card">
                <div class="p-4 border-b border-border-main">
                    <h3 class="text-lg font-medium text-text-main">کتاب‌های کمپین</h3>

                    {{-- --- REFACTOR: START (Book Search UX) --- --}}
                    <div class="relative mt-2" wire:click.away="resetBookSearch">
                        <label for="bookSearch" class="sr-only">جستجوی کتاب</label>

                        <div class="relative">
                            <input type="text"
                                   wire:model.live.debounce.300ms="bookSearch"
                                   wire:focus="showBookSearchResults = true"
                                   id="bookSearch"
                                   class="form-input"
                                   placeholder="جستجوی کتاب (عنوان یا کد مالی)..."
                                   autocomplete="off">

                            <div wire:loading wire:target="bookSearch" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icons.spinner class="h-4 w-4 text-text-muted"/>
                            </div>
                        </div>

                        @if($showBookSearchResults && strlen($this->bookSearch) >= 2)
                            {{--
                                REFACTOR: Removed 'dark:bg-gray-900' to force light background
                                as requested, ensuring text readability.
                            --}}
                            <div class="absolute z-10 mt-1 w-full rounded-md border border-border-main
                    bg-white shadow-lg max-h-60 overflow-y-auto">

                                @if($bookSearchResults->isNotEmpty())
                                    <ul class="divide-y divide-border-main">
                                        @foreach($bookSearchResults as $book)
                                            <li wire:key="search-book-{{ $book->id }}"
                                                wire:click="addBook({{ $book->id }})"
                                                class="p-3 hover:bg-surface-secondary cursor-pointer">
                                                <p class="font-medium text-text-main text-sm">{{ $book->title }}</p>
                                                <p class="text-xs text-text-muted font-mono">{{ $book->financial_code }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="p-3 text-sm text-text-muted text-center">
                                        کتابی با این مشخصات یافت نشد.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                    {{-- --- REFACTOR: END --- --}}
                </div>

                {{-- Selected Books List --}}
                <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                    @forelse($selectedBookDetails as $id => $details)
                        <div wire:key="selected-book-{{ $id }}"
                             class="flex items-center justify-between p-2 rounded-md bg-surface-secondary">
                            <div>
                                <p class="font-medium text-text-main text-sm">{{ $details['title'] }}</p>
                                <p class="text-xs text-text-muted font-mono">{{ $details['financial_code'] }}</p>
                            </div>
                            <button type="button" wire:click="removeBook({{ $id }})" class="btn-link-danger" title="حذف کتاب">
                                <x-icons.x class="h-4 w-4"/>
                            </button>
                        </div>
                    @empty
                        <p class="text-center text-text-muted text-sm py-4">هیچ کتابی انتخاب نشده است.</p>
                    @endforelse
                </div>
                @error('selectedBooks') <span class="form-error px-4 pb-4">{{ $message }}</span> @enderror
            </div>

        </div>

        {{-- --- REFACTOR: START (Goal #3: Bottom Save Button) --- --}}
        <div class="mt-8 border-t border-border-main pt-5">
            <div class="flex justify-end items-center gap-x-2">

                <button type="submit" class="btn btn-primary">
                    <span>ذخیره اطلاعات کمپین</span>
                    <x-icons.spinner wire:loading wire:target="save" class="h-4 w-4"/>
                </button>
            </div>
        </div>
        {{-- --- REFACTOR: END --- --}}

    </form>
</div>
