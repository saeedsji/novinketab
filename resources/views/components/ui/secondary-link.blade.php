@props(['href'=>'#'])


<a href="{{$href}}" class=" rounded-md w-full block bg-white px-3.5 py-2.5 lg:text-lg text-center font-semibold text-gray-900 shadow-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
  {{$slot}}
</a>
