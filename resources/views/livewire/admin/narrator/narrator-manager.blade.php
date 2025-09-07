@section('title', 'مدیریت گویندگان')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت گویندگان</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، ویرایش و مدیریت اطلاعات گویندگان.</p>
        </div>
        <div class="mt-4 sm:mt-0  flex-none flex items-center gap-x-2">
            <x-forms.excel-export-button name="exportExcel"/>
            <button wire:click="create" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن گوینده</span>
            </button>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Search and Filter Section --}}
    {{-- =================================================================== --}}
    <div class="card mt-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label for="search" class="form-label">جستجو در نام و توضیحات</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="جستجو..."
                       class="form-input mt-1">
            </div>
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
                        <x-ui.sortable column="name" :$sortCol :$sortAsc>نام گوینده</x-ui.sortable>
                    </th>
                    <th scope="col" class="table-header-cell">
                        <x-ui.sortable column="books_count" :$sortCol :$sortAsc>تعداد کتاب‌ها</x-ui.sortable>
                    </th>
                    <th scope="col" class="table-header-cell">توضیحات</th>
                    <th scope="col" class="table-header-cell">
                        <x-ui.sortable column="created_at" :$sortCol :$sortAsc>تاریخ ثبت</x-ui.sortable>
                    </th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($narrators as $narrator)
                    <tr wire:key="narrator-{{ $narrator->id }}" class="table-row">
                        <td class="table-cell font-medium text-text-main">{{ $narrator->name }}</td>
                        <td class="table-cell-muted">{{ $narrator->books_count }}</td>
                        <td class="table-cell-muted" title="{{$narrator->description}}">{{ \Illuminate\Support\Str::limit($narrator->description, 60) }}</td>
                        <td class="table-cell-muted">
                            {{ \Morilog\Jalali\Jalalian::forge($narrator->created_at)->format('Y/m/d') }}
                        </td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="edit({{ $narrator->id }})" class="btn-link" title="ویرایش">
                                    <x-icons.edit class="h-5 w-5"/>
                                </button>
                                <button wire:click="delete({{ $narrator->id }})"
                                        wire:confirm="آیا از حذف دائمی این گوینده اطمینان دارید؟"
                                        class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="5">
                            هیچ گوینده‌ای یافت نشد.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($narrators->hasPages())
            <div class="mt-4">{{ $narrators->links() }}</div>
        @endif
    </div>


    {{-- =================================================================== --}}
    {{-- Narrator Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showModal" maxWidth="md">
        <x-dialog.panel>
            <form wire:submit="save">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $modalTitle }}</h3>
                    <div class="mt-6 space-y-6">
                        <div>
                            <label for="name" class="form-label">نام گوینده</label>
                            <input type="text" wire:model="name" id="name" class="form-input mt-1">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="description" class="form-label">توضیحات (اختیاری)</label>
                            <textarea wire:model="description" id="description" rows="4"
                                      class="form-input mt-1"></textarea>
                            @error('description') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره</button>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="btn btn-outline w-full sm:w-auto">انصراف
                    </button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>

</div>
