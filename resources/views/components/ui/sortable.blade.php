@props(['column', 'sortCol', 'sortAsc'])

<button wire:click="sortBy('{{ $column }}')" {{ $attributes->merge(['class' => 'flex items-center gap-2 group']) }}>
    <span>{{ $slot }}</span>

    <span class="text-gray-400">
        @if ($sortCol === $column)
            @if ($sortAsc)
                <x-icons.arrow-long-up />
            @else
                <x-icons.arrow-long-down />
            @endif
        @else
            <span class="opacity-70 group-hover:opacity-100 transition-opacity">
                <x-icons.arrows-up-down />
            </span>
        @endif
    </span>
</button>
