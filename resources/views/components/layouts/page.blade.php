<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'تکنوراه | پلتفرم هوشمندسازی تعامل با مشتریان')</title>

    <meta name="description" content="@yield('description', 'با تکنوراه، فرآیندهای ارتباط با مشتریان را هوشمندسازی کنید، سرنخ‌ها را به مشتریان وفادار تبدیل کرده و رشد کسب‌وکار خود را سرعت ببخشید.')">
    <meta name="keywords" content="@yield('keywords', 'اتوماسیون، تکنوراه، سفر مشتری، مدیریت سرنخ، افزایش نرخ تبدیل, CRM')">
    <meta name="author" content="تکنوراه">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title', 'تکنوراه | پلتفرم هوشمندسازی تعامل با مشتریان')">
    <meta property="og:description" content="@yield('description', 'با تکنوراه، فرآیندهای ارتباط با مشتریان را هوشمندسازی کنید، سرنخ‌ها را به مشتریان وفادار تبدیل کرده و رشد کسب‌وکار خود را سرعت ببخشید.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('image', url('/assets/images/logo/original.png'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'تکنوراه | پلتفرم هوشمندسازی تعامل با مشتریان')">
    <meta name="twitter:description" content="@yield('description', 'با تکنوراه، فرآیندهای ارتباط با مشتریان را هوشمندسازی کنید، سرنخ‌ها را به مشتریان وفادار تبدیل کرده و رشد کسب‌وکار خود را سرعت ببخشید.')">
    <meta name="twitter:image" content="@yield('image', url('/assets/images/logo/original.png'))">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#107C41">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('style')
</head>

<body class="bg-gray-50 antialiased" dir="rtl" x-data="{ isDemoModalOpen: false, isMobileMenuOpen: false }" @keydown.escape.window="isDemoModalOpen = isMobileMenuOpen = false">

{{-- 2. کل این بلاک header را جایگزین header قبلی خود کنید --}}
<header class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-sm shadow-sm z-50 transition-all duration-300">
    <div class="container mx-auto px-6 py-4">
        {{-- Main header content --}}
        <div class="flex items-center justify-between">
            <a href="/" class="text-xl font-bold text-gray-800">
                <span class="text-green-600">تکنو</span>راه
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden lg:flex items-center space-x-8 rtl:space-x-reverse">
                <a href="#features" class="text-gray-600 hover:text-green-600 transition-colors duration-300">ویژگی‌ها</a>
                <a href="#testimonials" class="text-gray-600 hover:text-green-600 transition-colors duration-300">نظرات مشتریان</a>
                <a href="#faq" class="text-gray-600 hover:text-green-600 transition-colors duration-300">سوالات متداول</a>
            </nav>

            {{-- Desktop Action Buttons --}}
            <div class="hidden lg:flex items-center space-x-2 rtl:space-x-reverse">
                <button @click="isDemoModalOpen = true" class="px-5 py-2 text-sm font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50 transition-colors duration-300">
                    درخواست دمو
                </button>
                <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors duration-300">
                    ورود به پنل
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="lg:hidden">
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-gray-600 focus:outline-none">
                    <span class="sr-only">باز کردن منو</span>
                    {{-- Hamburger Icon --}}
                    <svg x-show="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    {{-- Close Icon --}}
                    <svg x-show="isMobileMenuOpen" style="display: none;" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Panel --}}
        <div x-show="isMobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="lg:hidden bg-white rounded-lg shadow-lg mt-4"
             style="display: none;"
             @click.away="isMobileMenuOpen = false">
            <nav class="flex flex-col p-4 space-y-2">
                <a href="#features" @click="isMobileMenuOpen = false" class="block px-4 py-2 text-gray-700 rounded-md hover:bg-gray-100">ویژگی‌ها</a>
                <a href="#testimonials" @click="isMobileMenuOpen = false" class="block px-4 py-2 text-gray-700 rounded-md hover:bg-gray-100">نظرات مشتریان</a>
                <a href="#faq" @click="isMobileMenuOpen = false" class="block px-4 py-2 text-gray-700 rounded-md hover:bg-gray-100">سوالات متداول</a>

                <div class="border-t border-gray-200 pt-4 mt-2 space-y-3">
                    <button @click="isDemoModalOpen = true; isMobileMenuOpen = false" class="w-full text-center px-5 py-2 text-sm font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50 transition-colors duration-300">
                        درخواست دمو
                    </button>
                    <a href="{{ route('login') }}" class="block w-full text-center px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors duration-300">
                        ورود به پنل
                    </a>
                </div>
            </nav>
        </div>
    </div>
</header>



@yield('content')


<footer class="bg-white border-t border-gray-200">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-right">
            <div class="mb-6 md:mb-0">
                <a href="#home" class="text-2xl font-bold text-gray-800">
                    <span class="text-green-600">تکنو</span>راه
                </a>
                <p class="mt-2 text-sm text-gray-500">سیستم هوشمندسازی تعامل برای رشد پایدار کسب‌وکار شما.</p>
            </div>
            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                <a href="#" class="text-gray-500 hover:text-green-600 transition-colors">درباره ما</a>
                <a href="#" class="text-gray-500 hover:text-green-600 transition-colors">تماس با ما</a>
            </div>
        </div>
        <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
            <p class="text-sm text-gray-400">© {{ date('Y') }} تکنوراه. تمام حقوق محفوظ است.</p>
            <div class="flex mt-4 sm:mt-0 space-x-5 rtl:space-x-reverse">
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.71v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" /></svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-3.096 0 1.548 1.548 0 013.096 0zM6.55 16.338H3.45V7.748h3.1v8.59zM17.64 3H6.36C4.512 3 3 4.512 3 6.36v11.28C3 19.488 4.512 21 6.36 21h11.28C19.488 21 21 19.488 21 17.64V6.36C21 4.512 19.488 3 17.64 3z" clip-rule="evenodd" /></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

@yield('script')
@livewireScripts

</body>
</html>
