@section('title', 'مدیریت دسترسی‌ها')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="section-title section-title-primary">مدیریت دسترسی‌ها</h1>
            <p class="pr-5 text-sm text-text-muted">لیست تمام دسترسی‌های سیستم. آنها را مدیریت، ویرایش یا حذف کنید.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex-none">
            <button wire:click="createPermission" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5" />
                <span>افزودن دسترسی</span>
            </button>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Filters Section --}}
    {{-- =================================================================== --}}
    <div class="card mt-8 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="search" class="form-label">جستجو</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="نام دسترسی..." class="form-input mt-1">
            </div>
            <div>
                <label for="filterDateFrom" class="form-label">از تاریخ</label>
                <input type="date" wire:model.live="filterDateFrom" id="filterDateFrom" class="form-input mt-1">
            </div>
            <div>
                <label for="filterDateTo" class="form-label">تا تاریخ</label>
                <input type="date" wire:model.live="filterDateTo" id="filterDateTo" class="form-input mt-1">
            </div>
        </div>
        <div class="mt-4 flex justify-start">
            <button wire:click="clearFilters" class="btn btn-secondary">
                <x-icons.x-circle class="h-4 w-4"/>
                <span>پاک کردن فیلترها</span>
            </button>
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
                    <th scope="col" class="table-header-cell"><x-ui.sortable column="name" :$sortCol :$sortAsc>نام دسترسی</x-ui.sortable></th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell"><x-ui.sortable column="created_at" :$sortCol :$sortAsc>تاریخ ایجاد</x-ui.sortable></th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($permissions as $permission)
                    <tr wire:key="permission-{{ $permission->id }}" class="table-row">
                        <td class="table-cell text-sm text-text-main">{{ $permission->name }}</td>
                        <td class="table-cell-muted hidden sm:table-cell">{{ jdate($permission->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="editPermission({{ $permission->id }})" class="btn-link" title="ویرایش">
                                    <x-icons.edit class="h-5 w-5"/>
                                </button>
                                <button wire:click="deletePermission({{ $permission->id }})" wire:confirm="آیا از حذف دائمی این دسترسی اطمینان دارید؟" class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="3" class="table-cell-muted py-12 text-center">هیچ دسترسی با این مشخصات یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($permissions->hasPages())
            <div class="mt-4">{{ $permissions->links() }}</div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- Permission Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showPermissionModal" maxWidth="md">
        <x-dialog.panel>
            <form wire:submit="savePermission">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $permissionModalTitle }}</h3>
                    <div class="mt-6">
                        <label for="permission-name" class="form-label">نام دسترسی (انگلیسی)</label>
                        <input type="text" wire:model="name" id="permission-name" dir="ltr" placeholder="e.g., manage-users" class="form-input mt-1">
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره</button>
                    <button type="button" wire:click="$set('showPermissionModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>
</div>
