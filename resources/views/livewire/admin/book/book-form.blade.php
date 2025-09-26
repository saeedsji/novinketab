@section('title', $book?->exists ? 'ویرایش کتاب' : 'افزودن کتاب')

<div>
    {{-- Breadcrumb/Back Link --}}
    <div class="mb-6">
        <a href="{{ route('book.index') }}" wire:navigate
           class="flex items-center text-sm font-medium text-text-muted hover:text-text-main">
            <x-icons.chevron-right class="h-4 w-4 ml-1"/>
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

                    <div class="sm:col-span-12 md:col-span-6">
                        <label for="title" class="form-label">عنوان کتاب</label>
                        <input type="text" wire:model.blur="title" id="title" class="form-input mt-1">
                        @error('title') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="financial_code" class="form-label">کد مالی</label>
                        <input type="text" dir="ltr" wire:model.blur="financial_code" id="financial_code" class="form-input mt-1">
                        @error('financial_code') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-12 md:col-span-3">
                        <label for="category_id" class="form-label">دسته‌بندی</label>
                        <select wire:model.blur="category_id" id="category_id" class="form-input form-select mt-1">
                            <option value="">انتخاب کنید</option>
                            @foreach($categoryTree as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="status" class="form-label">وضعیت</label>
                        <select wire:model.blur="status" id="status" class="form-input form-select mt-1">
                            @foreach($bookStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="gender_suitability" class="form-label">مناسب برای</label>
                        <select wire:model.blur="gender_suitability" id="gender_suitability" class="form-input form-select mt-1">
                            @foreach($genderSuitabilityEnum as $gender)
                                <option value="{{ $gender->value }}">{{ $gender->pName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="publish_date" class="form-label">تاریخ انتشار</label>
                        <input type="date" wire:model.blur="publish_date" id="publish_date" class="form-input mt-1">
                        @error('publish_date') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="taghche_title" class="form-label">عنوان کتاب در طاقچه</label>
                        <input type="text" wire:model.blur="taghche_title" id="taghche_title" class="form-input mt-1">
                        @error('taghche_title') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </fieldset>

                {{-- Section 2: People --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">عوامل</legend>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedAuthors" class="form-label">نویسنده(ها)</label>
                        <select id="selectedAuthors" wire:model.blur="selectedAuthors" multiple class="form-input form-select mt-1 h-48">
                            @foreach($authors as $author) <option value="{{ $author->id }}">{{ $author->name }}</option> @endforeach
                        </select>
                        @error('selectedAuthors') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedPublishers" class="form-label">ناشر(ها)</label>
                        <select id="selectedPublishers" wire:model.blur="selectedPublishers" multiple class="form-input form-select mt-1 h-48">
                            @foreach($publishers as $publisher) <option value="{{ $publisher->id }}">{{ $publisher->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedTranslators" class="form-label">مترجم(ها)</label>
                        <select id="selectedTranslators" wire:model.blur="selectedTranslators" multiple class="form-input form-select mt-1 h-48">
                            @foreach($translators as $translator) <option value="{{ $translator->id }}">{{ $translator->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedNarrators" class="form-label">گوینده(ها)</label>
                        <select id="selectedNarrators" wire:model.blur="selectedNarrators" multiple class="form-input form-select mt-1 h-48">
                            @foreach($narrators as $narrator) <option value="{{ $narrator->id }}">{{ $narrator->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedComposers" class="form-label">آهنگساز(ها)</label>
                        <select id="selectedComposers" wire:model.blur="selectedComposers" multiple class="form-input form-select mt-1 h-48">
                            @foreach($composers as $composer) <option value="{{ $composer->id }}">{{ $composer->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-12 md:col-span-4">
                        <label for="selectedEditors" class="form-label">تدوینگر(ها)</label>
                        <select id="selectedEditors" wire:model.blur="selectedEditors" multiple class="form-input form-select mt-1 h-48">
                            @foreach($editors as $editor) <option value="{{ $editor->id }}">{{ $editor->name }}</option> @endforeach
                        </select>
                    </div>
                </fieldset>

                {{-- Section 3: Financial & Quantitative Info --}}
                <fieldset
                    class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">اطلاعات مالی و مشخصات</legend>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="print_price" class="form-label">قیمت کتاب چاپی</label>
                        <input type="number" dir="ltr"  wire:model.blur="print_price" id="print_price" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="suggested_price" class="form-label">قیمت پیشنهادی</label>
                        <input type="number" dir="ltr"  wire:model.blur="suggested_price" id="suggested_price" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="max_discount" class="form-label">حداکثر تخفیف (%)</label>
                        <input type="number" dir="ltr"  wire:model.blur="max_discount" id="max_discount" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="breakeven_sales_count" class="form-label">نقطه سر به سر (تعداد)</label>
                        <input type="number" dir="ltr"  wire:model.blur="breakeven_sales_count" id="breakeven_sales_count" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="print_pages" class="form-label">تعداد صفحات چاپی</label>
                        <input type="number" dir="ltr"  wire:model.blur="print_pages" id="print_pages" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="track_count" class="form-label">تعداد ترک صوتی</label>
                        <input type="number" dir="ltr" wire:model.blur="track_count" id="track_count" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="duration" class="form-label">مدت زمان (دقیقه)</label>
                        <input type="number" dir="ltr"  wire:model.blur="duration" id="duration" class="form-input mt-1">
                    </div>
                </fieldset>

                {{-- Section 4: Details & Platforms --}}
                <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">جزئیات و پلتفرم‌ها</legend>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label class="form-label">قالب‌های کتاب</label>
                        <div class="mt-2 space-y-2">
                            @foreach($bookFormatsEnum as $format)
                                <div class="flex items-center">
                                    <input id="format-{{$format->value}}" wire:model.blur="formats" value="{{$format->value}}" type="checkbox" class="form-checkbox">
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
                                    <input id="platform-{{$platform->value}}" wire:model.blur="sales_platforms" value="{{$platform->value}}" type="checkbox" class="form-checkbox">
                                    <label for="platform-{{$platform->value}}" class="mr-2 text-sm text-text-main">{{$platform->pName()}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-12 md:col-span-6">
                        <label for="new-tag" class="form-label">تگ‌ها</label>
                        <div class="flex items-center mt-1 gap-2">
                            <input type="text" id="new-tag" class="form-input flex-grow" placeholder="تگ جدید را وارد و Enter بزنید" wire:model.defer="newTag" wire:keydown.enter.prevent="addTag">
                            <button type="button" wire:click.prevent="addTag" class="btn btn-secondary whitespace-nowrap">افزودن تگ</button>
                        </div>
                        @if (!empty($tags))
                            <div class="mt-3 flex flex-wrap gap-2" dir="rtl">
                                @foreach ($tags as $index => $tag)
                                    <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-sm font-medium text-text-main dark:text-gray-300">
                                        {{ $tag }}
                                        <button type="button" wire:click.prevent="removeTag({{ $index }})" class="group relative h-4 w-4 rounded-sm hover:bg-gray-500/20 flex items-center justify-center">
                                            <span class="sr-only">Remove</span>
                                            <x-icons.x class="h-3 w-3 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200"/>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="sm:col-span-12">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea wire:model.blur="description" id="description" rows="4" class="form-input mt-1"></textarea>
                    </div>
                </fieldset>

                {{-- Section 5: External IDs --}}
                <fieldset class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-12 border border-border-color p-4 rounded-lg">
                    <legend class="text-base font-semibold text-text-main px-2">شناسه‌های خارجی</legend>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="fidibo_book_id" class="form-label">شناسه فیدیبو</label>
                        <input type="number" dir="ltr" wire:model.blur="fidibo_book_id" id="fidibo_book_id" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="taghcheh_book_id" class="form-label">شناسه طاقچه</label>
                        <input type="number" dir="ltr" wire:model.blur="taghcheh_book_id" id="taghcheh_book_id" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="navar_book_id" class="form-label">شناسه نوار</label>
                        <input type="number" dir="ltr" wire:model.blur="navar_book_id" id="navar_book_id" class="form-input mt-1">
                    </div>
                    <div class="sm:col-span-6 md:col-span-3">
                        <label for="ketabrah_book_id" class="form-label">شناسه کتابراه</label>
                        <input type="number" dir="ltr" wire:model.blur="ketabrah_book_id" id="ketabrah_book_id" class="form-input mt-1">
                    </div>
                </fieldset>
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-row-reverse gap-2 mt-8 border-t border-border-color pt-5">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <div wire:loading.remove wire:target="save">ذخیره اطاعات کتاب</div>
                    <div wire:loading wire:target="save">در حال ذخیره...</div>
                </button>
            </div>
        </form>
    </div>
</div>
