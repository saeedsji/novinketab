@section('title', 'مدیریت نشست‌ها')

<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="section-title section-title-primary">مدیریت نشست‌ها</h1>
            <p class="pr-5 text-sm text-text-muted">تمام نشست‌های فعال کاربران را مشاهده و مدیریت کنید.</p>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Data Analysis Dashboard --}}
    {{-- =================================================================== --}}
    <div class="mt-8">
        <h3 class="text-base font-semibold leading-6 text-text-main">آمار کلی نشست‌ها</h3>
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Stat Card: Total Sessions --}}
            <div class="card flex flex-row items-center gap-x-4 overflow-hidden p-4">
                <div class="icon-card-primary">
                    <x-icons.users class="h-6 w-6" />
                </div>
                <div>
                    <p class="form-label">مجموع نشست‌ها</p>
                    <p class="text-2xl font-semibold text-text-main">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
            {{-- Stat Card: Admin Sessions --}}
            <div class="card flex flex-row items-center gap-x-4 overflow-hidden p-4">
                <div class="icon-card-success">
                    <x-icons.shield class="h-6 w-6" />
                </div>
                <div>
                    <p class="form-label">نشست‌های ادمین</p>
                    <p class="text-2xl font-semibold text-text-main">{{ number_format($stats['admins']) }}</p>
                </div>
            </div>
            {{-- Stat Card: Desktop vs Mobile --}}
            <div class="card flex flex-row items-center gap-x-4 overflow-hidden p-4">
                <div class="icon-card-info">
                    <x-icons.monitor class="h-6 w-6" />
                </div>
                <div>
                    <p class="form-label">دسکتاپ / موبایل</p>
                    <p class="text-2xl font-semibold text-text-main">
                        {{ number_format($stats['platforms']['Desktop']) }} / {{ number_format($stats['platforms']['Mobile']) }}
                    </p>
                </div>
            </div>
            {{-- Stat Card: Top Browser --}}
            <div class="card flex flex-row items-center gap-x-4 overflow-hidden p-4">
                <div class="icon-card-warning">
                    <x-icons.globe class="h-6 w-6" />
                </div>
                <div>
                    <p class="form-label">مرورگر برتر</p>
                    <p class="text-2xl font-semibold text-text-main">{{ $stats['top_browser'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Filters Section --}}
    {{-- =================================================================== --}}
    <div class="card mt-8 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="search" class="form-label">جستجو</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="نام کاربر، ایمیل، IP یا دستگاه..." class="form-input mt-1">
            </div>
            <div class="flex items-end">
                <div class="relative flex items-start gap-x-3">
                    <div class="flex h-6 items-center">
                        <input id="filterOnlyAdmins" wire:model.live="filterOnlyAdmins" type="checkbox" class="form-checkbox">
                    </div>
                    <div class="text-sm leading-6">
                        <label for="filterOnlyAdmins" class="form-label">فقط نمایش نشست‌های ادمین</label>
                    </div>
                </div>
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
                    <th scope="col" class="table-header-cell">کاربر</th>
                    <th scope="col" class="table-header-cell">آدرس IP</th>
                    <th scope="col" class="table-header-cell hidden sm:table-cell">دستگاه</th>
                    <th scope="col" class="table-header-cell">
                        <x-ui.sortable column="last_activity" :$sortCol :$sortAsc>آخرین فعالیت</x-ui.sortable>
                    </th>
                    <th scope="col" class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                <tbody class="table-body">
                @forelse ($sessions as $session)
                    <tr wire:key="session-{{ $session->id }}" class="table-row">
                        <td class="table-cell">
                            @if($session->user)
                                <div class="font-medium text-text-main">{{ $session->user->name }}</div>
                                <div class="text-text-muted">{{ $session->user->email }}</div>
                            @else
                                <span class="badge-ring-danger">کاربر حذف شده</span>
                            @endif
                        </td>
                        <td class="table-cell-muted">{{ $session->ip_address }}</td>
                        <td class="table-cell-muted hidden sm:table-cell">{{ $session->user_agent() }}</td>
                        <td class="table-cell-muted">{{ $session->last_activity() }}</td>
                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center">
                                <button wire:click="deleteSession({{ $session->id }})" wire:confirm="آیا از حذف این نشست اطمینان دارید؟ این عمل کاربر را از سیستم خارج می‌کند." class="btn-link-danger" title="حذف نشست">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td class="table-cell-muted py-12 text-center" colspan="5">
                            هیچ نشستی با این مشخصات یافت نشد.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
            <div class="mt-4">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
