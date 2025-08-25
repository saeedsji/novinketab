@section('title', 'ایمپورت خروجی پلتفرم ها')


<div dir="rtl">
    {{-- Page Title --}}
    <h1 class="text-2xl font-bold mb-6 text-gray-700">ایمپورت خروجی پلتفرم ها</h1>

    {{-- Import Form --}}
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-lg font-semibold border-b pb-3 mb-4 text-gray-600">فرم ایمپورت جدید</h2>
        <form wire:submit.prevent="import" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Platform Selection --}}
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">پلتفرم فروش</label>
                    <select id="platform" wire:model.defer="platform"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">یک پلتفرم را انتخاب کنید...</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                        @endforeach
                    </select>
                    @error('platform') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- File Upload --}}
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">فایل اکسل (xlsx, csv)</label>
                    <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                         x-on:livewire-upload-finish="isUploading = false"
                         x-on:livewire-upload-error="isUploading = false"
                         x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <input type="file" id="file" wire:model="file"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">

                        {{-- Progress Bar --}}
                        <div x-show="isUploading" class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                            <div class="bg-indigo-600 h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                    @error('file') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end pt-4">
                <button type="submit"
                        class="gap-2 bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center"
                        wire:loading.attr="disabled">

                    {{-- آیکون اسپینر که فقط در حالت لودینگ نمایش داده می‌شود --}}
                    <x-icons.spinner wire:loading wire:target="import"/>

                    {{-- متن دکمه در حالت عادی --}}
                    <div wire:loading.remove wire:target="import">
                        شروع ایمپورت
                    </div>

                    {{-- متن دکمه در حالت لودینگ --}}
                    <div wire:loading wire:target="import">
                        لطفا منتظر بمانید ...
                    </div>

                </button>
            </div>
        </form>
    </div>

    {{-- Import Logs History --}}
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold border-b pb-3 mb-4 text-gray-600">تاریخچه ایمپورت‌ها</h2>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <select wire:model.live="filterPlatform" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">همه پلتفرم‌ها</option>
                @foreach($platforms as $platform)
                    <option value="{{ $platform->value }}">{{ $platform->pName() }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="">همه وضعیت‌ها</option>
                <option value="completed">موفق</option>
                <option value="processing">در حال پردازش</option>
                <option value="failed">ناموفق</option>
                <option value="pending">در انتظار</option>
            </select>
        </div>

        {{-- Logs Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">کاربر
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">پلتفرم
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وضعیت
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">پرداخت‌های مرتبط
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">آمار
                    </th>

                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">عملیات</span>
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50" wire:key="log-{{ $log->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->user?->name ?? 'نامشخص' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->platform->pName() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @switch($log->status)
                                    @case('completed') bg-green-100 text-green-800 @break
                                    @case('processing') bg-yellow-100 text-yellow-800 @break
                                    @case('failed') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">
                            {{ number_format($log->payments_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col">
                                <span class="text-green-600">جدید: {{ $log->new_records }}</span>
                                <span class="text-blue-600">آپدیت: {{ $log->updated_records }}</span>
                                <span class="text-red-600">ناموفق: {{ $log->failed_records }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                            <div class="flex items-center justify-end gap-x-4">
                                <button wire:click="showLogDetails({{ $log->id }})"
                                        class="text-indigo-600 hover:text-indigo-900">مشاهده جزئیات
                                </button>

                                {{-- Delete Button --}}
                                <button
                                    wire:click="deleteLog({{ $log->id }})"
                                    wire:confirm="هشدار جدی! آیا از حذف این لاگ و تعداد {{ $log->payments_count }} پرداخت مرتبط با آن اطمینان دارید؟ این عملیات غیر قابل بازگشت است."
                                    class="text-red-600 hover:text-red-900 flex items-center gap-1"
                                    wire:loading.attr="disabled"
                                    wire:target="deleteLog({{ $log->id }})">

                                    {{-- Loading Spinner --}}
                                    <div wire:loading wire:target="deleteLog({{ $log->id }})">
                                        <x-icons.spinner class="w-4 h-4"/>
                                    </div>

                                    {{-- Button Text --}}
                                    <div wire:loading.remove wire:target="deleteLog({{ $log->id }})">
                                        حذف
                                    </div>
                                    <div wire:loading wire:target="deleteLog({{ $log->id }})">
                                        صبر کنید...
                                    </div>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">هیچ لاگی یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>

    {{-- Log Details Modal --}}
    @if($selectedLog)
        <x-dialog wire:model="showModal">
            <x-dialog.panel>
                <h3 class="text-lg font-medium text-gray-900 mb-4">جزئیات ایمپورت #{{ $selectedLog->id }}</h3>
                <div class="text-sm space-y-2">
                    <p><strong>پلتفرم:</strong> {{ $selectedLog->platform->pName() }}</p>
                    <p><strong>وضعیت:</strong> {{ $selectedLog->status }}</p>
                    <p><strong>فایل:</strong> {{ basename($selectedLog->file_path) }}</p>
                    <hr class="my-2">
                    <p><strong>موفق (جدید):</strong> {{ $selectedLog->new_records }}</p>
                    <p><strong>موفق (بروزرسانی):</strong> {{ $selectedLog->updated_records }}</p>
                    <p><strong>ناموفق:</strong> {{ $selectedLog->failed_records }}</p>

                    @if($selectedLog->details && count($selectedLog->details) > 0)
                        <div class="mt-4">
                            <h4 class="font-semibold">ردیف‌های ناموفق:</h4>
                            <div class="max-h-96 overflow-y-auto bg-gray-50 p-2 rounded-md mt-2 text-xs">
                                <pre dir="ltr"
                                     class="text-left">{{ json_encode($selectedLog->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
                {{-- No need for a close button here as per your instruction --}}
            </x-dialog.panel>
        </x-dialog>
    @endif
</div>
