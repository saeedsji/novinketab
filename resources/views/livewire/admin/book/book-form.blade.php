@section('title', $book?->exists ? 'ویرایش کتاب' : 'افزودن کتاب')

<div>
    {{-- Breadcrumb/Back Link --}}
    <div class="mb-6">
        <a href="{{ route('book.index') }}"
           class="flex items-center text-sm font-medium text-text-muted hover:text-text-main">
            <x-icons.chevron-right class="h-4 w-4 mr-1"/>
            <span>بازگشت به لیست کتاب‌ها</span>
        </a>
    </div>

    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">{{ $book?->exists ? 'ویرایش کتاب' : 'افزودن کتاب جدید' }}</h1>
            <p class="mt-2 text-sm text-text-muted">لطفا اطلاعات کتاب را به دقت وارد کنید.</p>
        </div>
    </div>


    {{-- Form --}}
    <div class="card mt-6 p-6">
        <form wire:submit="save">
            <div class="space-y-8">

                {{-- Section 1: Basic Info --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">اطلاعات پایه</legend>
                    <div class="sm:col-span-6 md:col-span-4">
                        <label for="title" class="form-label">عنوان کتاب</label>
                        <input type="text" wire:model.live="title" id="title" class="form-input mt-1">
                        @error('title') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-2">
                        <label for="financial_code" class="form-label">کد مالی</label>
                        <input type="text" wire:model.live="financial_code" id="financial_code" class="form-input mt-1">
                        @error('financial_code') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="category_id" class="form-label">دسته‌بندی</label>
                        <select wire:model.live="category_id" id="category_id" class="form-input form-select mt-1">
                            <option value="">انتخاب کنید</option>
                            @foreach($categoryTree as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="status" class="form-label">وضعیت</label>
                        <select wire:model.live="status" id="status" class="form-input form-select mt-1">
                            @foreach($bookStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                {{-- Section 2: People --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">عوامل <span class="font-normal">(برای انتخاب چند مورد ctrl را نگه دارید)</span></legend>
                    <div class="sm:col-span-12 md:col-span-6">
                        <label for="selectedAuthors" class="form-label">نویسنده(ها)</label>
                        <select id="selectedAuthors" wire:model.live="selectedAuthors" multiple
                                class="form-input form-select mt-1 h-64">
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedAuthors') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-12 md:col-span-6">
                        <label for="selectedPublishers" class="form-label">ناشر(ها)</label>
                        <select id="selectedPublishers" wire:model.live="selectedPublishers" multiple
                                class="form-input form-select mt-1 h-64">
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
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedTranslators" class="form-label">مترجم(ها)</label>
                        <select id="selectedTranslators" wire:model.live="selectedTranslators" multiple
                                class="form-input form-select mt-1 h-64">
                            @foreach($translators as $translator)
                                <option value="{{ $translator->id }}">{{ $translator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedNarrators" class="form-label">گوینده(ها)</label>
                        <select id="selectedNarrators" wire:model.live="selectedNarrators" multiple
                                class="form-input form-select mt-1 h-64">
                            @foreach($narrators as $narrator)
                                <option value="{{ $narrator->id }}">{{ $narrator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedComposers" class="form-label">آهنگساز(ها)</label>
                        <select id="selectedComposers" wire:model.live="selectedComposers" multiple
                                class="form-input form-select mt-1 h-64">
                            @foreach($composers as $composer)
                                <option value="{{ $composer->id }}">{{ $composer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                {{-- Section 3: Financial Info --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">اطلاعات مالی و فروش</legend>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="suggested_price" class="form-label">قیمت پیشنهادی</label>
                        <input type="number" wire:model.live="suggested_price" id="suggested_price"
                               class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="max_discount" class="form-label">حداکثر تخفیف (%)</label>
                        <input type="number" wire:model.live="max_discount" id="max_discount" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="breakeven_sales_count" class="form-label">نقطه سر به سر (تعداد)</label>
                        <input type="number" wire:model.live="breakeven_sales_count" id="breakeven_sales_count"
                               class="form-input mt-1">
                    </div>
                </fieldset>

                {{-- Section 4: Details --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">جزئیات و مشخصات</legend>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label class="form-label">قالب‌های کتاب</label>
                        <div class="mt-2 space-y-2">
                            @foreach($bookFormatsEnum as $format)
                                <div class="flex items-center">
                                    <input id="format-{{$format->value}}" wire:model.live="formats"
                                           value="{{$format->value}}" type="checkbox" class="form-checkbox">
                                    <label for="format-{{$format->value}}"
                                           class="mr-2 text-sm text-text-main">{{$format->pName()}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label class="form-label">پلتفرم‌های فروش</label>
                        <div class="mt-2 space-y-2">
                            @foreach($salesPlatformsEnum as $platform)
                                <div class="flex items-center">
                                    <input id="platform-{{$platform->value}}" wire:model.live="sales_platforms"
                                           value="{{$platform->value}}" type="checkbox" class="form-checkbox">
                                    <label for="platform-{{$platform->value}}"
                                           class="mr-2 text-sm text-text-main">{{$platform->pName()}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Section 4: Details / Tags --}}
                    <div class="sm:col-span-12 md:col-span-6">
                        <label for="new-tag" class="form-label">تگ‌ها</label>

                        {{-- Input for adding new tags --}}
                        <div class="flex items-center mt-1 gap-2">
                            <input type="text"
                                   id="new-tag"
                                   class="form-input flex-grow"
                                   placeholder="تگ جدید را وارد و Enter بزنید"
                                   wire:model.defer="newTag"
                                   wire:keydown.enter.prevent="addTag">
                            <button type="button" wire:click.prevent="addTag"
                                    class="btn btn-secondary whitespace-nowrap">افزودن تگ
                            </button>
                        </div>

                        {{-- Display existing tags --}}
                        @if (!empty($tags))
                            <div class="mt-3 flex flex-wrap gap-2" dir="rtl">
                                @foreach ($tags as $index => $tag)
                                    <div
                                        class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-sm font-medium text-text-main dark:text-gray-300">
                                        {{ $tag }}
                                        <button type="button"
                                                wire:click.prevent="removeTag({{ $index }})"
                                                class="group relative h-4 w-4 rounded-sm hover:bg-gray-500/20 flex items-center justify-center">
                                            <span class="sr-only">Remove</span>
                                            <x-icons.x
                                                class="h-3 w-3 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200"/>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="sm:col-span-12">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea wire:model.live="description" id="description" rows="4"
                                  class="form-input mt-1"></textarea>
                    </div>
                </fieldset>

            </div>
            <div class="flex flex-row-reverse gap-2 mt-8">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره کتاب</button>
            </div>
        </form>
    </div>
</div>
