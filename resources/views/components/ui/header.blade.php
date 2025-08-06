@props(['href' => '#','title'=>''])

<div class="bg-white rounded-md shadow-sm p-2 lg:p-3 mb-3">
    <div class="flex flex-row items-center">

        <div class="basis-1/6">
            <a href="{{ $href }}">
                <x-icons.chevron-right/>
            </a>
        </div>

        <div class="basis-5/6 text-center">
            <h2 class="text-secondary xl:text-lg font-bold text-sm ">{!! $title !!}</h2>
        </div>


        <div class="basis-1/6 flex justify-end">
            @if($slot)
                {{$slot}}
            @endif
        </div>

    </div>
</div>
