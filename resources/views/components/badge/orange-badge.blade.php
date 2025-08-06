@props(['circle'=>true])

<div
    class="inline-flex items-center gap-x-1.5 rounded-md bg-orange-100 px-2 py-2 font-bold text-orange-700">
    @if($circle)
        <svg class="h-1.5 w-1.5 fill-orange-500" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3"/>
        </svg>
    @endif
    {{$slot}}
</div>
