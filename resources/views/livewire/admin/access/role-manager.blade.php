@section('title', 'مدیریت نقش‌ها')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="section-title section-title-primary">مدیریت نقش‌ها</h1>
            <p class="pr-5 text-sm text-text-muted">لیست تمام نقش‌های سیستم. آنها را مدیریت و دسترسی‌هایشان را تعیین کنید.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex-none">
            <button wire:click="createRole" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن نقش</span>
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
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="نام نقش..." class="form-input mt-1">
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
                    <th scope="col" class="table-header-cell"><x-ui.sortable column="name" :$sortCol :$sortAsc>نام نقش</x-ui.sortable></th>
                    <th scope="col" class="table-header-cell">دسترسی‌ها</th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell"><x-ui.sortable column="created_at" :$sortCol :$sortAsc>تاریخ ایجاد</x-ui.sortable></th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($roles as $role)
                    <tr wire:key="role-{{ $role->id }}" class="table-row">
                        <td class="table-cell font-medium text-text-main">{{ $role->name }}</td>
                        <td class="table-cell">
                            <div class="flex flex-wrap gap-1">
                                @foreach($role->permissions as $permission)
                                    <span class="badge-ring-info">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="table-cell-muted hidden sm:table-cell">{{ jdate($role->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="editRole({{ $role->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                <button wire:click="deleteRole({{ $role->id }})" wire:confirm="آیا از حذف دائمی این نقش اطمینان دارید؟" class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="4" class="table-cell-muted py-12 text-center">هیچ نقشی با این مشخصات یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="mt-4">{{ $roles->links() }}</div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- Role Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showRoleModal" maxWidth="lg">
        <x-dialog.panel>
            <form wire:submit="saveRole">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $roleModalTitle }}</h3>
                    <div class="mt-6 grid grid-cols-1 gap-y-6">
                        <div>
                            <label for="role-name" class="form-label">نام نقش</label>
                            <input type="text" wire:model="name" id="role-name" placeholder="مثال: مدیر" class="form-input mt-1">
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="role-permissions" class="form-label">دسترسی‌ها</label>
                            <select wire:model="selectedPermissions" id="role-permissions" multiple class="form-input form-select mt-1 h-48">
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedPermissions') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره</button>
                    <button type="button" wire:click="$set('showRoleModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>
</div>
