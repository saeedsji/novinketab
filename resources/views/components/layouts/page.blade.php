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


@yield('content')


@yield('script')
@livewireScripts

</body>
</html>
