@props([
    'type' => 'info',  // 'success', 'error', 'warning', 'info'
    'title' => '',     // Title of the alert
    'dismissable' => false, // If true, show dismiss button
])

@php
    // Define alert styles based on the type
    $alertClasses = [
        'success' => ['bg-green-50', 'text-green-800', 'text-green-400'],
        'error' => ['bg-red-100', 'text-red-800', 'text-red-400'],
        'warning' => ['bg-yellow-50', 'text-yellow-800', 'text-yellow-400'],
        'info' => ['bg-blue-50', 'text-blue-800', 'text-blue-400'],
    ];

    $classes = $alertClasses[$type];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    class="rounded-md {{ $classes[0] }} p-3"
>
    <div class="flex justify-between items-center">
        <div class="ml-3 flex flex-col text-right ">
            <div class="flex gap-2 items-center">
                @if ($type === 'error')
                    <div class="{{ $classes[2] }}">
                        <x-icons.alert-circle size="20"/>
                    </div>
                @elseif ($type === 'success')
                    <div class="{{ $classes[2] }}">
                        <x-icons.check-circle w="20" h="20"/>
                    </div>
                @elseif ($type === 'warning')
                    <div class="{{ $classes[2] }}">
                        <x-icons.alert-triangle size="20"/>
                    </div>
                @elseif ($type === 'info')
                    <div class="{{ $classes[2] }}">
                        <x-icons.help-circle w="20" h="20"/>
                    </div>
                @endif

                @if($title)
                    <h3 class="text-base font-bold items-center {{ $classes[1] }}">{{ $title }}</h3>
                @endif
            </div>
            @if($slot)
                <div class="mt-2 text-sm pr-1 {{ $classes[1] }}">
                    {{ $slot }}
                </div>
            @endif
        </div>

        @if($dismissable)
            <div class="mr-auto">
                <div class="-mx-1.5 -my-1.5">
                    <button
                        @click="show = false"
                        type="button"
                        class="inline-flex rounded-md {{ $classes[0] }} p-1.5 {{ $classes[2] }} hover:bg-opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    >
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
