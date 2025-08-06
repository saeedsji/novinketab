@props(['faq', 'loop'])


<div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="pt-6 bg-white mt-2 shadow-md p-4 rounded-lg">
    <dt>
        <button type="button" x-description="Expand/collapse question button"
            class="flex w-full items-start justify-between text-right text-gray-900" aria-controls="faq-0"
            @click="open = !open" aria-expanded="false" x-bind:aria-expanded="open.toString()">
            <span class="font-bold text-primary text-sm lg:text-lg leading-loose">{{ $faq->question }}</span>
            <span x-show="open">
                <x-icons.chevron-up />
            </span>
            <span x-show="!open">
                <x-icons.chevron-down />
            </span>
        </button>
    </dt>
    <dd class="mt-2 pr-2" id="faq-0" x-show="open" x-cloak x-transition:enter.duration.500ms>
        <p class="text-md  text-gray-600 prose max-w-none">
            {!! nl2br($faq->answer) !!}
        </p>
    </dd>
</div>
