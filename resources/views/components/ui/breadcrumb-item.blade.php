@props(['title','href'=>'','first'=>false])

<li class="whitespace-nowrap">
    <div class="flex items-center">
        @if(!$first)
            <x-icons.chevron-left/>
        @endif
        @if($href)
            <a wire:navigate href="{{$href}}" class="text-gray-400 hover:text-gray-500">
                <span class="mr-2 font-bold text-sm text-gray-400 hover:text-gray-600">{{$title}}</span>
            </a>
        @else
            <span class="font-bold text-sm text-gray-700 hover:text-gray-900">{{$title}}</span>
        @endif
    </div>
</li>
