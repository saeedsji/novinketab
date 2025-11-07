@section('title', 'مدیریت کمپین‌ها')

{{--
    This view is built based on the styles from the PaymentManager example provided.
    It adheres to RTL, responsiveness, and component usage instructions.
--}}
<div dir="rtl">
    {{-- =================================================================== --}}
    {{-- Header Section --}}
    {{-- =================================================================== --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-text-main">مدیریت کمپین‌ها</h1>
            <p class="mt-2 text-sm text-text-muted">ایجاد، آنالیز و مانیتورینگ کمپین‌های فروش.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex-none flex items-center gap-x-2">
            {{--
                This button now navigates to the new full-page component
                We assume a route named 'admin.campaigns.create' exists
                'wire:navigate' provides SPA-like navigation (Instruction #8)
            --}}
            <a href="{{ route('campaigns.create') }}" wire:navigate type="button" class="btn btn-primary">
                <x-icons.plus class="h-5 w-5"/>
                <span>افزودن کمپین</span>
            </a>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- Filters Section --}}
    {{-- =================================================================== --}}
    <details class="card mt-6 overflow-hidden" open>
        <summary class="p-4 cursor-pointer flex justify-between items-center">
            <div class="flex items-center gap-x-2">
                <h3 class="text-lg font-medium text-text-main">فیلترها</h3>
                {{-- UX Improvement: Add loading indicator for filters/sorting --}}
                <div wire:loading wire:target="search, filterPlatform, filterDateFrom, filterDateTo, resetFilters, sortBy">
                    <x-icons.spinner class="h-5 w-5 text-text-muted"/>
                </div>
            </div>

            <div class="flex items-center gap-x-4">
                <button type="button" wire:click="resetFilters" class="btn-link-secondary text-sm">حذف فیلترها</button>
                <x-icons.chevron-down class="h-5 w-5 transition-transform duration-200 details-open:rotate-180"/>
            </div>
        </summary>
        <div class="border-t border-border-main p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div>
                    <label for="search" class="form-label">جستجو (نام کمپین)</label>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search" class="form-input mt-1">
                </div>
                <div>
                    <label for="filterPlatform" class="form-label">پلتفرم</label>
                    <select wire:model.live="filterPlatform" id="filterPlatform" class="form-input form-select mt-1">
                        <option value="">همه</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1">از تاریخ شروع</label>
                    {{-- Instruction for Persian Date Picker --}}
                    <x-forms.persian-date-picker
                        name="filterDateFrom"
                        wire:model.live="filterDateFrom"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>
                <div>
                    <label class="block mb-1">تا تاریخ پایان</label>
                    <x-forms.persian-date-picker
                        name="filterDateTo"
                        wire:model.live="filterDateTo"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>
            </div>
        </div>
    </details>

    {{-- =================================================================== --}}
    {{-- Table Section (Responsive - Instruction #15) --}}
    {{-- =================================================================== --}}
    <div class="mt-8 flex flex-col">
        <div class="table-container">
            <table class="table-main">
                <thead class="table-header">
                <tr>
                    <th class="table-header-cell"><x-ui.sortable column="name" :$sortCol :$sortAsc>نام کمپین</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="platform" :$sortCol :$sortAsc>پلتفرم</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="start_date" :$sortCol :$sortAsc>تاریخ شروع</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="end_date" :$sortCol :$sortAsc>تاریخ پایان</x-ui.sortable></th>
                    <th class="table-header-cell">تخفیف</th>
                    <th class="table-header-cell">تعداد کتاب</th>
                    {{-- REFACTOR: START --}}
                    <th class="table-header-cell"><x-ui.sortable column="total_sales_count" :$sortCol :$sortAsc>تعداد فروش</x-ui.sortable></th>
                    <th class="table-header-cell"><x-ui.sortable column="total_sales_amount" :$sortCol :$sortAsc>مبلغ فروش (ریال)</x-ui.sortable></th>
                    {{-- REFACTOR: END --}}
                    <th class="relative py-3.5 pl-4 pr-3 sm:pr-6"><span class="sr-only">عملیات</span></th>
                </tr>
                </thead>
                {{-- UX Improvement: Add loading state for table body (Instruction #1 & #8) --}}
                <tbody wire:loading.class.delay="opacity-50" class="table-body">
                @forelse ($campaigns as $campaign)
                    <tr wire:key="campaign-{{ $campaign->id }}" class="table-row">
                        <td class="table-cell font-medium text-text-main">{{ $campaign->name }}</td>
                        <td class="table-cell-muted">{{ $campaign->platform->pName() }}</td>
                        <td class="table-cell-muted">{{ $campaign->start_date_jalali() }}</td>
                        <td class="table-cell-muted">{{ $campaign->end_date_jalali() }}</td>
                        <td class="table-cell-muted">{{ $campaign->discount_percent }}٪</td>
                        <td class="table-cell-muted">{{ $campaign->books_count }}</td>

                        {{-- REFACTOR: START --}}
                        <td class="table-cell-muted font-mono" dir="ltr">
                            {{ number_format($campaign->total_sales_count) }}
                        </td>
                        <td class="table-cell-muted font-mono" dir="ltr">
                            {{ number_format($campaign->total_sales_amount) }}
                        </td>
                        {{-- REFACTOR: END --}}

                        <td class="table-cell text-center">
                            <div class="flex items-center justify-center gap-x-4">
                                <button wire:click="showCampaignStats({{ $campaign->id }})" class="btn-link" title="آنالیز و مانیتورینگ">
                                    <x-icons.activity class="h-5 w-5"/>
                                </button>

                                <a href="{{ route('campaigns.edit', $campaign) }}" wire:navigate class="btn-link" title="ویرایش">
                                    <x-icons.edit class="h-5 w-5"/>
                                </a>
                                {{-- Instruction #17 for delete confirm --}}
                                <button wire:click="deleteCampaign({{ $campaign->id }})"
                                        wire:confirm="آیا از حذف دائمی این آیتم اطمینان دارید؟"
                                        class="btn-link-danger" title="حذف">
                                    <x-icons.trash-2 class="h-5 w-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        {{-- REFACTOR: Updated colspan from 7 to 9 --}}
                        <td class="table-cell-muted py-12 text-center" colspan="9">هیچ کمپینی با این مشخصات یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
            <div class="mt-4">{{ $campaigns->links() }}</div>
        @endif
    </div>

    {{-- =================================================================== --}}
    {{-- Campaign Stats Modal (Instruction #16) --}}
    {{-- =================================================================== --}}
    <x-dialog wire:model="showStatsModal" maxWidth="3xl">
        <x-dialog.panel>
            <div class="p-6">
                <h3 class="text-lg font-medium text-text-main">
                    آنالیز کمپین: {{ $viewingCampaign?->name }}
                </h3>
                <p class="mt-1 text-sm text-text-muted">
                    آمار فروش کتاب‌های این کمپین در پلتفرم
                    <span class="font-medium">{{ $viewingCampaign?->platform?->pName() }}</span>
                    از تاریخ
                    <span class="font-medium" dir="ltr">{{ $viewingCampaign?->start_date_jalali() }}</span>
                    تا
                    <span class="font-medium" dir="ltr">{{ $viewingCampaign?->end_date_jalali() }}</span>
                </p>

                {{-- UX Improvement: Added loading state for modal content --}}
                <div wire:loading wire:target="showCampaignStats">
                    <div class="py-12 flex justify-center items-center gap-x-2 text-text-muted">
                        <x-icons.spinner class="h-6 w-6"/>
                        <span>در حال بارگذاری آمار...</span>
                    </div>
                </div>

                <div wire:loading.remove wire:target="showCampaignStats">
                    @if($campaignStats)
                        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">تعداد کل فروش</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format($campaignStats['total_sales_count']) }}
                                </dd>
                            </div>
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">مجموع مبلغ فروش (ریال)</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format($campaignStats['total_amount']) }}
                                </dd>
                            </div>
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">مجموع سهم ناشر (ریال)</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format($campaignStats['total_publisher_share']) }}
                                </dd>
                            </div>
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">میانگین مبلغ فروش (ریال)</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format((int)$campaignStats['average_amount']) }}
                                </dd>
                            </div>
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">مجموع تخفیف (ریال)</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format($campaignStats['total_discount']) }}
                                </dd>
                            </div>
                            <div class="card p-4">
                                <dt class="truncate text-sm font-medium text-text-muted">مجموع مالیات (ریال)</dt>
                                <dd class="mt-1 text-3xl font-semibold tracking-tight text-text-main" dir="ltr">
                                    {{ number_format($campaignStats['total_tax']) }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        {{-- Show this only if stats are loaded but empty (e.g., $campaignStats is empty array) --}}
                        <p class="mt-6 text-center text-text-muted">آماری برای نمایش وجود ندارد.</p>
                    @endif
                </div>

            </div>
            <div class="flex flex-row-reverse gap-2 bg-surface-secondary px-4 py-3 sm:px-6">
                <button type="button" wire:click="$set('showStatsModal', false)" class="btn btn-outline w-full sm:w-auto">بستن</button>
            </div>
        </x-dialog.panel>
    </x-dialog>

</div>
