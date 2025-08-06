@props(['title', 'active' => false])

@php
    // Dropdown is active if any of its child links are active
    $active = $active ?? false;
@endphp

<li x-data="{ open: @json($active) }">
    {{-- text-right برای راست‌چین شدن متن --}}
    <button @click="open = !open" class="text-gray-700 hover:text-secondary hover:bg-gray-50 group flex w-full items-center gap-x-3 rounded-md p-2 text-right text-sm font-semibold leading-6">
        {{-- Icon slot --}}
        @if(isset($icon))
            {{ $icon }}
        @endif

        <span class="flex-1">{{ $title }}</span>

        {{-- آیکون chevron-left برای RTL و چرخش 90- درجه برای نمایش صحیح --}}
        <svg :class="{ '-rotate-90': open }" class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- pr-4 برای ایجاد تورفتگی از سمت راست در زیرمنو صحیح است --}}
    <ul x-show="open" x-collapse class="mt-1 space-y-1">
        {{ $slot }}
    </ul>
</li>
