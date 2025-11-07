<div dir="rtl" class="bg-gray-50" wire:init="loadCharts">
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">داشبورد آنالیز جامع</h1>
                <p class="mt-1 text-sm text-gray-500">تحلیل کامل داده‌های فروش بر اساس معیارها و بازه‌های زمانی
                    مختلف.</p>
            </div>
        </div>

        {{-- Date Filters --}}
        {{-- (تغییر) کلاس گرید برای جای دادن فیلترهای جدید --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">از تاریخ</label>
                    <x-forms.persian-date-picker
                        name="startDate"
                        wire:model.live="startDate"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">تا تاریخ</label>
                    <x-forms.persian-date-picker
                        name="endDate"
                        wire:model.live="endDate"
                        :value="null"
                        :options="['time' => false, 'persianDigits' => true]"
                    />
                </div>

                {{-- (جدید) فیلتر پلتفرم --}}
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">پلتفرم</label>
                    <select wire:model.live="platform" id="platform" class="form-input form-select mt-1">
                        <option value="">همه پلتفرم‌ها</option>
                        @foreach($platforms as $platformOption)
                            <option value="{{ $platformOption->value }}">{{ $platformOption->pName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- (جدید) فیلتر کتاب --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">کتاب</label>
                    <livewire:shared.book-selector wire:model.live="book_id"/>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">فروش کل</h3>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ number_format($stats['total_revenue']) }} ریال</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">تعداد عناوین
                    فروش رفته</h3>
                <p class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($stats['total_sales_count']) }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">سهم ناشر</h3>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format($stats['total_publisher_share']) }}
                    ریال</p></div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">مجموع
                    تخفیف</h3>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['total_discount']) }} ریال</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">بیشترین تعداد
                    فروش
                </h3>
                <p class="mt-2 text-lg font-bold text-gray-800 truncate">{{ $stats['best_selling_book_title'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">بیشترین مبلغ
                    فروش</h3>
                <p class="mt-2 text-lg font-bold text-gray-800 truncate">{{ $stats['most_profitable_book_title'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm"><h3 class="text-sm font-medium text-gray-500">تعداد کل
                    کتاب‌ها</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format($stats['total_books']) }}</p></div>

        </div>

        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">روند فروش در
                        طول زمان</h3>
                    <canvas id="salesOverTimeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">فروش بر اساس
                        پلتفرم</h3>
                    <canvas id="salesByPlatformChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">فروش بر اساس
                        جنسیت گوینده</h3>
                    <canvas id="salesByGenderChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">۵۰ کتاب
                        پرفروش بر اساس مبلغ</h3>
                    <canvas id="topBooksByRevenueChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">۵۰ کتاب
                        پرفروش بر اساس تعداد</h3>
                    <canvas id="topBooksBySalesChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm"><h3 class="font-semibold text-gray-700 mb-4">فروش بر
                        اساس دسته بندی</h3>
                    <canvas id="salesByCategoryChart"></canvas>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden xl:col-span-1">
                <h3 class="p-4 text-lg font-semibold text-gray-800 border-b">نویسندگان برتر</h3>
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">نام</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">فروش کل</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($topAuthors as $author)
                        <tr class="border-b">
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $author->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($author->total_revenue) }}
                                ریال
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-4 text-center text-gray-500">داده‌ای یافت نشد.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden xl:col-span-2">
                <h3 class="p-4 text-lg font-semibold text-gray-800 border-b">فروش‌های اخیر</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">کتاب</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">پلتفرم</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">تاریخ فروش</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">مبلغ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentPayments as $payment)
                            <tr class="border-b">
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $payment->book->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $payment->sale_platform ? \App\Enums\Book\SalesPlatformEnum::from($payment->sale_platform)->pName() : '-'}}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ \Morilog\Jalali\Jalalian::forge($payment->sale_date)->format('Y/m/d H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($payment->amount) }}ریال
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">داده‌ای یافت نشد.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')

@endpush
