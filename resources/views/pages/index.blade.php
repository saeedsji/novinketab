@extends('components.layouts.page')

{{-- Define SEO and page-specific details for Novin Ketab --}}
@section('title', 'ورود به پنل مدیریت نوین کتاب')
@section('description', 'پنل مدیریت نوین کتاب، مرکز جامع برای مدیریت محتوا، کاربران و گزارشات. برای دسترسی به امکانات، لطفاً وارد شوید.')
@section('keywords', 'نوین کتاب, پنل مدیریت, ورود ادمین, داشبورد')
@section('author', 'نوین کتاب')

{{-- Custom Styles for Gradient Text and Animations --}}
@push('styles')
    <style>
        .gradient-text {
            background: linear-gradient(90deg, #f59e0b, #ef4444, #ec4899, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
            animation: gradient-animation 5s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
@endpush


{{-- Main Content Section --}}
@section('content')
    {{-- Main wrapper with a dark theme and decorative radial gradients --}}
    <div class="relative isolate min-h-screen overflow-hidden bg-gray-900 text-white font-sans">
        {{-- Background Gradients for ambiance --}}
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#a855f7] to-[#f97316] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>

        {{-- Header --}}
        <header class="absolute inset-x-0 top-0 z-50">
            <div class="container mx-auto px-6 lg:px-8">
                <nav class="flex items-center justify-between py-6" aria-label="Global">
                    {{-- Logo --}}
                    <div class="flex lg:flex-1">
                        <a href="#" class="-m-1.5 p-1.5 text-2xl font-bold text-white">
                            نوین<span class="text-orange-500">کتاب</span>
                        </a>
                    </div>
                    {{-- Login Button --}}
                    <div class="flex flex-1 justify-end">
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-slate-300 hover:text-orange-400 transition-colors">
                            ورود به پنل
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        {{-- Hero Section --}}
        <main class="relative flex items-center justify-center min-h-screen px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-7xl">
                    <span class="gradient-text">پنل مدیریتی نوین کتاب</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-slate-400 max-w-2xl mx-auto">
                    به پنل مدیریت یکپارچه نوین کتاب خوش آمدید. اینجا همه چیز برای کنترل، انتشار و تحلیل محتوای شما فراهم است.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('login') }}"
                       class="rounded-lg bg-orange-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-orange-600/30 hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 transition-all transform hover:scale-105">
                        ورود به حساب کاربری
                    </a>
                </div>
            </div>
        </main>

        {{-- Background Gradient at the bottom --}}
        <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
            <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#f97316] to-[#ec4899] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>
    </div>

    {{-- Features Section --}}
    <section class="py-20 sm:py-24 bg-slate-50">
        <div class="container mx-auto px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">قدرت در دستان شماست</h2>
                <p class="mt-4 text-lg text-gray-600">
                    مجموعه‌ای از ابزارهای قدرتمند برای مدیریت همه‌جانبه.
                </p>
            </div>
            <div class="mx-auto mt-16 grid max-w-none grid-cols-1 gap-8 text-center md:grid-cols-2 lg:grid-cols-3 lg:max-w-6xl">
                {{-- Feature 1: Smart Publishing --}}
                <div class="flex flex-col items-center p-8 bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-purple-400 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">انتشار هوشمند</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600">
                        مطالب خود را زمان‌بندی کنید تا به صورت خودکار در زمان مناسب منتشر شوند و به بیشترین بازخورد برسند.
                    </p>
                </div>
                {{-- Feature 2: User Management --}}
                <div class="flex flex-col items-center p-8 bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-pink-400 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">مدیریت کاربران</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600">
                        به سادگی سطوح دسترسی مختلف برای نویسندگان، ویراستاران و مدیران تعریف کرده و تیم خود را مدیریت کنید.
                    </p>
                </div>
                {{-- Feature 3: Analytics & Reports --}}
                <div class="flex flex-col items-center p-8 bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-red-400 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900">تحلیل و گزارش</h3>
                    <p class="mt-2 text-base leading-7 text-gray-600">
                        با گزارش‌های دقیق و لحظه‌ای از بازدیدها، محبوب‌ترین مطالب و عملکرد نویسندگان خود مطلع شوید.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900">
        <div class="container mx-auto px-6 lg:px-8 py-8 text-center">
            <p class="text-sm text-slate-400">
                &copy; {{ date('Y') }} تمامی حقوق برای نوین کتاب محفوظ است.
            </p>
        </div>
    </footer>
@endsection
