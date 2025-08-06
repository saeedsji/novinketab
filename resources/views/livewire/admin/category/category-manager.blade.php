@section('title', 'مدیریت دسته‌بندی‌ها')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت دسته‌بندی‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، ویرایش و مدیریت دسته‌بندی‌های کتاب‌ها.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex-none">
            <button wire:click="create" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن دسته‌بندی</span>
            </button>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Search Section --}}
    {{-- =================================================================== --}}
    <div class="card mt-6 p-4">
        <div>
            <label for="search" class="form-label">جستجو</label>
            <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="جستجو در نام یا والد..." class="form-input mt-1">
        </div>
    </div>


    {{-- =================================================================== --}}
    {{-- Table Section --}}
    {{-- =================================================================== --}}
    <div class="mt-8 flex flex-col">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    <th scope="col" class="table-header-cell">
                        <x-ui.sortable column="name" :$sortCol :$sortAsc>نام دسته‌بندی</x-ui.sortable>
                    </th>
                    <th scope="col" class="table-header-cell">والد</th>
                    <th scope="col" class="table-header-cell text-center">
                        <x-ui.sortable column="books_count" :$sortCol :$sortAsc>تعداد کتاب‌ها</x-ui.sortable>
                    </th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell">
                        <x-ui.sortable column="created_at" :$sortCol :$sortAsc>تاریخ ایجاد</x-ui.sortable>
                    </th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($categories as $category)
                    {{-- Parent Row --}}
                    <tr wire:key="category-{{ $category->id }}" class="table-row">
                        <td class="table-cell font-medium text-text-main">
                            <div class="flex items-center cursor-pointer" wire:click="toggleExpand({{ $category->id }})">
                                @if($category->children->isNotEmpty() && !$search)
                                    <button class="ml-2 text-text-muted hover:text-text-main">
                                        @if(in_array($category->id, $expanded))
                                            <x-icons.chevron-down class="h-5 w-5"/>
                                        @else
                                            <x-icons.chevron-left class="h-5 w-5"/>
                                        @endif
                                    </button>
                                @else
                                    <span class="ml-2 w-5"></span> {{-- Placeholder for alignment --}}
                                @endif
                                <span>{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="table-cell-muted">{{ $category->parent->name ?? '—' }}</td>
                        <td class="table-cell-muted text-center">{{ $category->books_count }}</td>
                        <td class="table-cell-muted hidden sm:table-cell">
                            {{ \Morilog\Jalali\Jalalian::forge($category->created_at)->format('Y/m/d') }}
                        </td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="edit({{ $category->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="آیا از حذف دائمی این دسته‌بندی اطمینان دارید؟" class="btn-link-danger" title="حذف"><x-icons.trash-2 class="h-5 w-5"/></button>
                            </div>
                        </td>
                    </tr>

                    {{-- Child Rows (Visible if expanded and not searching) --}}
                    @if(in_array($category->id, $expanded) && !$search)
                        @foreach($category->children as $child)
                            <tr wire:key="child-{{ $child->id }}" class="table-row bg-surface-secondary/50">
                                <td class="table-cell font-medium text-text-main pr-10">
                                    <span class="mr-2 text-text-muted">-</span> {{ $child->name }}
                                </td>
                                <td class="table-cell-muted">{{ $child->parent->name ?? '—' }}</td>
                                <td class="table-cell-muted text-center">{{ $child->books_count }}</td>
                                <td class="table-cell-muted hidden sm:table-cell">
                                    {{ \Morilog\Jalali\Jalalian::forge($child->created_at)->format('Y/m/d') }}
                                </td>
                                <td class="table-cell text-center">
                                    <div class="flex items-center justify-center gap-x-4">
                                        <button wire:click="edit({{ $child->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                        <button wire:click="delete({{ $child->id }})" wire:confirm="آیا از حذف دائمی این دسته‌بندی اطمینان دارید؟" class="btn-link-danger" title="حذف"><x-icons.trash-2 class="h-5 w-5"/></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="5">
                            هیچ دسته‌بندی‌ای یافت نشد.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="mt-4">{{ $categories->links() }}</div>
        @endif
    </div>


    {{-- =================================================================== --}}
    {{-- Category Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showModal" maxWidth="lg">
        <x-dialog.panel>
            <form wire:submit="save">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $modalTitle }}</h3>
                    <div class="mt-6 space-y-6">
                        <div>
                            <label for="name" class="form-label">نام دسته‌بندی</label>
                            <input type="text" wire:model="name" id="name" class="form-input mt-1">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        {{-- ** UPDATED: Hierarchical Parent Dropdown ** --}}
                        <div>
                            <label for="parent_id" class="form-label">دسته‌بندی والد (اختیاری)</label>
                            <select wire:model="parent_id" id="parent_id" class="form-input form-select mt-1">
                                <option value="">بدون والد</option>
                                @foreach($categoryTree as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>

</div>
