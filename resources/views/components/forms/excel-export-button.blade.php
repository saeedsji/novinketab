@props(['name'])
<button wire:click="{{$name}}" wire:loading.attr="disabled" wire:target="{{$name}}" type="button"
        class="btn btn-outline">

    <div wire:loading.remove wire:target="{{$name}}" class="flex items-center">
        <x-icons.download class="h-5 w-5"/>
        <div class="mr-2">خروجی اکسل</div>
    </div>

    <div wire:loading wire:target="{{$name}}">
        <div class="flex">
            <div>
                <x-icons.spinner class="h-5 w-5 animate-spin"/>
            </div>
            <div class="mr-2">در حال پردازش...</div>
        </div>
    </div>

</button>
