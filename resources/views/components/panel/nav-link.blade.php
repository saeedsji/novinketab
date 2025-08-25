@props(['href', 'active'])

@php
    // تعیین خودکار وضعیت فعال بودن لینک
    $active = $active ?? request()->is(ltrim($href, '/'));

    // جایگزینی رنگ 'secondary' با رنگ سبز مستقیم از Tailwind
    $classes = ($active ?? false)
        ? 'bg-gray-100 text-secondary-600 border-l-4 border-secondary-600' // حالت فعال
        : 'text-gray-700 hover:text-secondary-600 hover:bg-gray-50';    // حالت عادی
@endphp

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes . ' group flex pr-2 gap-x-2 rounded-sm p-2 text-sm leading-6 font-semibold']) }}>
        {{ $slot }}
    </a>
</li>
