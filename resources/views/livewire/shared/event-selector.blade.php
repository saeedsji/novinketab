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
            placeholder="جستجوی نام رویداد..."
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
            autocomplete="off"
        />

        {{-- Loading Spinner & Clear Button Container --}}
        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            {{-- Loading Spinner --}}
            <div wire:loading wire:target="search, handleFocus">
                <div class="w-5 h-5 border-2 border-gray-300 border-t-indigo-500 rounded-full animate-spin"></div>
            </div>

            {{-- Clear Button --}}
            @if($search)
                <div wire:loading.remove wire:target="search, handleFocus">
                    <button
                        type="button"
                        wire:click="clearSelection"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
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
        @if(!$eventNames->isEmpty())
            <ul>
                @foreach($eventNames as $eventName)
                    <li
                        wire:click="selectEvent('{{ $eventName }}')"
                        class="px-4 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150"
                    >
                        <div class="font-semibold text-gray-800">{{ $eventName }}</div>
                    </li>
                @endforeach
            </ul>
        @elseif(strlen($search) > 0)
            <div class="p-4 text-center text-gray-500">
                رویدادی با نام «{{ $search }}» یافت نشد.
            </div>
        @else
            <div class="p-4 text-center text-gray-500">
                برای جستجو، شروع به تایپ کنید.
            </div>
        @endif
    </div>
</div>
