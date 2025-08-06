@props(['circle'=>true])

<div class="inline-flex items-center gap-x-1.5 rounded-md bg-red-100 px-1.5 py-0.5 font-medium text-red-700">
    @if($circle)
        <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3"/>
        </svg>
    @endif
    {{$slot}}
</div>
