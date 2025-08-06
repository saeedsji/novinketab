@props(['href'=>'#','target'=>'_self'])


<a target="{{$target}}" href="{{$href}}" class="bg-primary text-white px-2 py-3 text-center rounded-lg w-full font-bold cursor-pointer">
    {{$slot}}
</a>
