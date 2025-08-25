<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', '-')</title>

    <meta name="description" content="@yield('description', '')">
    <meta name="keywords" content="@yield('keywords', '')">
    <meta name="author" content="تکنوراه">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title', '')">
    <meta property="og:description" content="@yield('description', '')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('image', url('/assets/images/logo/original.png'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', '')">
    <meta name="twitter:description" content="@yield('description', '')">
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
