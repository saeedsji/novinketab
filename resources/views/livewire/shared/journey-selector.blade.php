<div
    class="relative"
    dir="rtl"
    x-data="{ open: @entangle('showDropdown') }"
>
    <div class="relative">
        {{-- Search Input --}}
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            wire:focus="handleFocus"
            @keydown.escape.prevent="open = false"
            placeholder="جستجوی کمپین..."
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
            autocomplete="off"
        />

        {{-- Loading Spinner & Clear Button Container --}}
        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <div wire:loading wire:target="search, handleFocus">
                <div class="w-5 h-5 border-2 border-gray-300 border-t-indigo-500 rounded-full animate-spin"></div>
            </div>
            @if($search)
                <div wire:loading.remove wire:target="search, handleFocus">
                    <button type="button" wire:click="clearSelection" class="text-gray-400 hover:text-gray-600">
                        <x-icons.x-circle class="w-5 h-5"/>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Search Results Dropdown --}}
    <div
        x-show="open"
        x-transition
        @click.away="open = false"
        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
        style="display: none;"
    >
        @if(!$journeys->isEmpty())
            <ul>
                @foreach($journeys as $journey)
                    <li wire:click="selectJourney({{ $journey->id }})" class="px-4 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150">
                        <div class="font-semibold text-gray-800">{{ $journey->name }}</div>
                    </li>
                @endforeach
            </ul>
        @elseif(strlen($search) > 0)
            <div class="p-4 text-center text-gray-500">کمپینی با نام «{{ $search }}» یافت نشد.</div>
        @else
            <div class="p-4 text-center text-gray-500">برای جستجو، شروع به تایپ کنید.</div>
        @endif
    </div>
</div>
