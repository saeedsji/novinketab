<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'پنل مدیریت')</title>

    {{-- Assets remain unchanged as requested --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 ">

{{-- Main layout container with Alpine.js state --}}
<div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">

    <!-- Off-canvas menu for mobile, show/hide based on sidebar state -->
    <x-panel.sidebar/>

    <!-- Main content area -->
    <div class="lg:pr-72">
        <x-panel.header/>

        <main class="py-10">
            <div class="px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>


@stack('scripts')
</body>
</html>
