<div x-data="{open:true}" x-show="open" x-cloak x-transition:leave.duration.500ms
     class="relative isolate flex items-center gap-x-6 overflow-hidden bg-gray-50 py-2.5 px-8  sm:before:flex-1"
     dir="rtl">
    <svg viewBox="0 0 577 310" aria-hidden="true"
         class="absolute top-1/2 left-[max(-7rem,calc(50%-52rem))] -z-10 w-[36.0625rem] -translate-y-1/2 transform-gpu blur-2xl">
        <path id="1d77c128-3ec1-4660-a7f6-26c7006705ad" fill="url(#49a52b64-16c6-4eb9-931b-8e24bf34e053)"
              fill-opacity=".3"
              d="m142.787 168.697-75.331 62.132L.016 88.702l142.771 79.995 135.671-111.9c-16.495 64.083-23.088 173.257 82.496 97.291C492.935 59.13 494.936-54.366 549.339 30.385c43.523 67.8 24.892 159.548 10.136 196.946l-128.493-95.28-36.628 177.599-251.567-140.953Z"/>
        <defs>
            <linearGradient id="49a52b64-16c6-4eb9-931b-8e24bf34e053" x1="614.778" x2="-42.453" y1="26.617"
                            y2="96.115"
                            gradientUnits="userSpaceOnUse">
                <stop stop-color="#D05A48"/>
                <stop offset="1" stop-color="#FF80B5"/>
            </linearGradient>
        </defs>
    </svg>
    <svg viewBox="0 0 577 310" aria-hidden="true"
         class="absolute top-1/2 left-[max(45rem,calc(50%+8rem))] -z-10 w-[36.0625rem] -translate-y-1/2 transform-gpu blur-2xl">
        <use href="#1d77c128-3ec1-4660-a7f6-26c7006705ad"/>
    </svg>
    <div class="flex flex-wrap items-center gap-y-2 gap-x-3">
        <p class="text-sm leading-6 text-gray-900">
           <span class="hidden md:inline-block"> با چند قدم ساده شروع کن</span>
            <span class="hidden md:inline-block">-</span>
            <strong class="font-semibold text-xs md:text-sm">مهارت آموزی با تو کارازما</strong>

        </p>
        <a href="{{route('user.main')}}#contact-form"
           class="flex-none rounded-full bg-gray-900 py-1 px-3 text-sm font-semibold text-white shadow-sm bg-secendry hover:bg-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
              بخش کارجو<i class="fa fa-arrow-left text-xs mr-2"></i></a>
    </div>
    <div class="flex flex-1 justify-end">
        <button @click="open=false" type="button" class="-m-3 p-3  focus-visible:outline-offset-[-4px]">
            <x-icons.x/>
        </button>
    </div>
</div>
