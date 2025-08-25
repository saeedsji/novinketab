@section('title', 'داشبورد مدیریتی')

<div dir="rtl" class="font-sans bg-gray-50/50 p-4 sm:p-6 lg:p-8">
    <div class="space-y-8">

        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center">

                <div class="flex-shrink-0 mb-4 sm:mb-0 sm:ml-6">
            <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600">
               <x-icons.user class="h-8 w-8"/>
            </span>
                </div>

                <div class="w-full">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">سلام، {{ $user->name }}!</h1>
                        <p class="text-gray-500 mt-1">به پنل مدیریت نوین کتاب خوش آمدید.</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                            @if($user->email)
                                <div class="flex items-center text-gray-600">
                                    <x-icons.mail class="w-5 h-5 text-gray-400 ml-2"/>
                                    <span class="font-medium text-gray-800">{{ $user->email }}</span>
                                </div>
                            @endif

                            @if($user->phone)
                                <div class="flex items-center text-gray-600">
                                    <x-icons.phone class="w-5 h-5 text-gray-400 ml-2"/>
                                    <span class="font-medium text-gray-800">{{ $user->phone }}</span>
                                </div>
                            @endif

                            @if($userRole)
                                <div class="flex items-center text-gray-600">
                                    <x-icons.user-check class="w-5 h-5 text-gray-400 ml-2"/>
                                    <span>نقش:</span>
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">{{ $userRole }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-700 mb-4">آمار کلیدی در یک نگاه</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-center gap-x-6">
                    <div class="bg-blue-100 text-blue-600 rounded-lg p-3">
                        <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">مجموع کتاب‌ها</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_books']) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-center gap-x-6">
                    <div class="bg-green-100 text-green-600 rounded-lg p-3">
                        <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">پرداخت‌های این ماه</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['payments_this_month']) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 flex items-center gap-x-6">
                    <div class="bg-indigo-100 text-indigo-600 rounded-lg p-3">
                        <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">ایمپورت‌های موفق</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['successful_imports']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-700 mb-4">ماژول‌های اصلی برنامه</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                <a href="{{ route('book.index') }}" wire:navigate
                   class="group bg-white p-6 rounded-xl shadow-md border border-gray-200 flex flex-col hover:shadow-xl hover:border-blue-500 transition-all duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">مدیریت محتوای اصلی</h3>
                    <p class="mt-2 text-sm text-gray-500 flex-grow">مدیریت  کتاب‌ها، نویسندگان و ناشران به عنوان زیرساخت اصلی سیستم.</p>
                    <div class="mt-auto pt-4 text-sm font-semibold text-blue-600 group-hover:text-blue-800 transition-colors">
                        ورود به بخش <span aria-hidden="true" class="mr-1">&larr;</span>
                    </div>
                </a>

                <a href="{{ route('payment.import') }}" wire:navigate
                   class="group bg-white p-6 rounded-xl shadow-md border border-gray-200 flex flex-col hover:shadow-xl hover:border-green-500 transition-all duration-300">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">ایمپورت و یکپارچه‌سازی</h3>
                    <p class="mt-2 text-sm text-gray-500 flex-grow">وارد کردن فایل‌های خروجی پلتفرم‌ها و تجمیع داده‌ها در یک سیستم واحد.</p>
                    <div class="mt-auto pt-4 text-sm font-semibold text-green-600 group-hover:text-green-800 transition-colors">
                        ورود به ایمپورتر <span aria-hidden="true" class="mr-1">&larr;</span>
                    </div>
                </a>

                <a href="{{ route('payment.index') }}" wire:navigate
                   class="group bg-white p-6 rounded-xl shadow-md border border-gray-200 flex flex-col hover:shadow-xl hover:border-amber-500 transition-all duration-300">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">مدیریت جامع پرداخت‌ها</h3>
                    <p class="mt-2 text-sm text-gray-500 flex-grow">نمای ۳۶۰ درجه از تمام تراکنش‌ها و پرداخت‌های ثبت‌شده از کانال‌های مختلف فروش.</p>
                    <div class="mt-auto pt-4 text-sm font-semibold text-amber-600 group-hover:text-amber-800 transition-colors">
                        مشاهده پرداخت‌ها <span aria-hidden="true" class="mr-1">&larr;</span>
                    </div>
                </a>

                <a href="{{ route('analytics.index') }}" wire:navigate
                   class="group bg-white p-6 rounded-xl shadow-md border border-gray-200 flex flex-col hover:shadow-xl hover:border-purple-500 transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 1.5m-5-1.5l1 1.5m-5-1.5l-1-1.5m5 1.5l1-1.5M9 18l-1-1.5m1 1.5l1-1.5m-5-1.5l-1 1.5m1-1.5l1 1.5m9-1.5l-1 1.5M15 18l1-1.5m-1 1.5l-1-1.5m-5-1.5l-1 1.5m1-1.5l1 1.5" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">تحلیل و هوش تجاری</h3>
                    <p class="mt-2 text-sm text-gray-500 flex-grow">تبدیل داده‌های خام به دانش کاربردی برای تصمیم‌گیری‌های هوشمندانه.</p>
                    <div class="mt-auto pt-4 text-sm font-semibold text-purple-600 group-hover:text-purple-800 transition-colors">
                        مشاهده تحلیل‌ها <span aria-hidden="true" class="mr-1">&larr;</span>
                    </div>
                </a>

            </div>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-700 mb-4">آخرین ایمپورت‌ها</h2>
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                <div class="space-y-4">
                    @forelse($recentImports as $log)
                        <div class="flex items-center justify-between text-sm pb-4 border-b last:border-b-0">
                            <div>
                                <p class="font-semibold text-gray-700">{{ $log->platform->pName() }}</p>
                                <p class="text-xs text-gray-500">توسط {{ $log->user?->name ?? 'سیستم' }}
                                    در {{ $log->created_at }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @switch($log->status)
                                    @case('completed') bg-green-100 text-green-800 @break
                                    @case('processing') bg-yellow-100 text-yellow-800 @break
                                    @case('failed') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ $log->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-center text-gray-500 py-4">هنوز هیچ فایلی ایمپورت نشده است.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
