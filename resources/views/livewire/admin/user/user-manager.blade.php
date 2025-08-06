@section('title', 'کاربران')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            {{-- Using standard text utilities for the main page title and description --}}
            <h1 class="text-xl font-semibold text-text-main">مدیریت کاربران</h1>
            <p class="mt-2 text-sm text-text-muted">لیست تمام کاربران سیستم. آنها را مدیریت، ویرایش یا حذف کنید.</p>
        </div>
        <div class="mt-4 flex items-center gap-x-2 sm:mt-0 sm:flex-none">
            {{-- Using the predefined button components --}}
            <button wire:click="export" type="button" class="btn btn-outline">
                <x-icons.download class="h-4 w-4"/>
                <span>خروجی اکسل</span>
                <x-icons.spinner wire:loading wire:target="export" class="h-4 w-4"/>
            </button>
            <button wire:click="createUser" type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن کاربر</span>
            </button>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Filters Section --}}
    {{-- =================================================================== --}}
    <div class="card mt-6 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {{-- Using form-label and form-input components --}}
            <div>
                <label for="search" class="form-label">جستجو</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="نام، ایمیل یا موبایل..." class="form-input mt-1">
            </div>
            <div>
                <label for="filterStatus" class="form-label">وضعیت کاربر</label>
                {{-- Added form-input to select for consistent styling --}}
                <select wire:model.live="filterStatus" id="filterStatus" class="form-input form-select mt-1">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach(\App\Enums\User\UserStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterType" class="form-label">نوع کاربر</label>
                <select wire:model.live="filterType" id="filterType" class="form-input form-select mt-1">
                    <option value="">همه انواع</option>
                    @foreach(\App\Enums\User\UserType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->pName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterAccess" class="form-label">سطح دسترسی</label>
                <select wire:model.live="filterAccess" id="filterAccess" class="form-input form-select mt-1">
                    <option value="">همه سطوح</option>
                    @foreach(\App\Enums\User\UserAccess::cases() as $access)
                        <option value="{{ $access->value }}">{{ $access->pName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterRole" class="form-label">نقش</label>
                <select wire:model.live="filterRole" id="filterRole" class="form-input form-select mt-1">
                    <option value="">همه نقش‌ها</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
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
        {{-- Replaced .table-wrapper with .table-container component --}}
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    {{-- Added .table-header-cell component --}}
                    <th scope="col" class="table-header-cell"><x-ui.sortable column="name" :$sortCol :$sortAsc>کاربر</x-ui.sortable></th>
                    <th scope="col" class="table-header-cell"><x-ui.sortable column="status" :$sortCol :$sortAsc>وضعیت</x-ui.sortable></th>
                    <th scope="col" class="table-header-cell"><x-ui.sortable column="access" :$sortCol :$sortAsc>سطح دسترسی</x-ui.sortable></th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell">نقش‌ها</th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell"><x-ui.sortable column="created_at" :$sortCol :$sortAsc>تاریخ ثبت‌نام</x-ui.sortable></th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="table-row">
                        {{-- Added .table-cell component and used utilities for content --}}
                        <td class="table-cell">
                            <div class="font-medium text-text-main">{{ $user->name }}</div>
                            <div class="text-text-muted">{{ $user->email }} | {{ $user->phone }}</div>
                        </td>
                        <td class="table-cell">{!! $user->status->badge() !!}</td>
                        <td class="table-cell-muted">{{ $user->access->pName() }}</td>
                        <td class="table-cell hidden sm:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    {{-- Using a defined badge component --}}
                                    <span class="badge-ring-info">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="table-cell-muted hidden sm:table-cell">{{ $user->created_at() }}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                {{-- Replaced text color utilities with .btn-link component classes --}}
                                <button wire:click="editUser({{ $user->id }})" class="btn-link" title="ویرایش"><x-icons.edit class="h-5 w-5"/></button>
                                <button wire:click="changePassword({{ $user->id }})" class="btn-link-secondary" title="تغییر رمز عبور"><x-icons.lock class="h-5 w-5"/></button>
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="آیا از حذف دائمی این کاربر اطمینان دارید؟" class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="6">هیچ کاربری با این مشخصات یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- User Add/Edit Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showUserModal" maxWidth="lg">
        <x-dialog.panel>
            <form wire:submit="saveUser">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">{{ $userModalTitle }}</h3>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                        <div class="sm:col-span-2">
                            <label for="user-name" class="form-label">نام و نام خانوادگی</label>
                            <input type="text" wire:model="name" id="user-name" class="form-input mt-1">
                            {{-- Using the .form-error component --}}
                            @error('name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="user-email" class="form-label">ایمیل</label>
                            <input type="email" wire:model="email" id="user-email" class="form-input mt-1">
                            @error('email') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="user-phone" class="form-label">موبایل</label>
                            <input type="text" wire:model="phone" id="user-phone" class="form-input mt-1">
                            @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="user-status" class="form-label">وضعیت</label>
                            <select wire:model="status" id="user-status" class="form-input form-select mt-1">
                                @foreach(\App\Enums\User\UserStatus::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->pName() }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="user-type" class="form-label">نوع کاربر</label>
                            <select wire:model="type" id="user-type" class="form-input form-select mt-1">
                                @foreach(\App\Enums\User\UserType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->pName() }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        @if (!$editingUser)
                            <div>
                                <label for="password" class="form-label">رمز عبور</label>
                                <input type="password" wire:model="password" id="password" dir="ltr" class="form-input mt-1">
                                @error('password') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                                <input type="password" wire:model="password_confirmation" dir="ltr"  id="password_confirmation" class="form-input mt-1">
                            </div>
                        @endif
                        <div>
                            <label for="user-access" class="form-label">سطح دسترسی</label>
                            <select wire:model="access" id="user-access" class="form-input form-select mt-1">
                                @foreach(\App\Enums\User\UserAccess::cases() as $access)
                                    <option value="{{ $access->value }}">{{ $access->pName() }}</option>
                                @endforeach
                            </select>
                            @error('access') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="user-roles" class="form-label">نقش‌ها</label>
                            <select wire:model="selectedRoles" id="user-roles" multiple class="form-input form-select mt-1">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedRoles') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                {{-- Modal footer buttons --}}
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">ذخیره کاربر</button>
                    <button type="button" wire:click="$set('showUserModal', false)" class="btn btn-outline w-full sm:w-auto">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>

    {{-- =================================================================== --}}
    {{-- Password Change Modal --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showPasswordModal" maxWidth="md">
        <x-dialog.panel>
            <form wire:submit="updatePassword">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-text-main">تغییر رمز عبور برای: <span class="font-bold">{{ $editingUser?->name }}</span></h3>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label for="password" class="form-label">رمز عبور جدید</label>
                            <input type="password" wire:model="password" id="password" class="form-input mt-1">
                            @error('password') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="form-label">تکرار رمز عبور جدید</label>
                            <input type="password" wire:model="password_confirmation" id="password_confirmation" class="form-input mt-1">
                        </div>
                    </div>
                </div>
                <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                    <button type="submit" class="btn btn-primary">به‌روزرسانی رمزعبور</button>
                    <button type="button" wire:click="$set('showPasswordModal', false)" class="btn btn-outline">انصراف</button>
                </div>
            </form>
        </x-dialog.panel>
    </x-dialog>
</div>
