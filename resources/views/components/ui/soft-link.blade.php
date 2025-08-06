@props(['href'=>'#'])

<a href="{{$href}}" class="w-full rounded-md block  bg-orange-50 px-3.5 py-2.5 text-lg text-center font-semibold text-primary shadow-md hover:bg-orange-100">
    {{$slot}}
</a>
