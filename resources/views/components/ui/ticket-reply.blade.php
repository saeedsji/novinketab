@props(['reply','admin'=>false])

@if($admin)
    @php $justify = $reply->type === \App\Enums\Ticket\ReplyType::user->value ? 'justify-end':''  @endphp
@else
    @php $justify = $reply->type === \App\Enums\Ticket\ReplyType::admin->value ? 'justify-end':''  @endphp
@endif
@php $bg = $reply->type === \App\Enums\Ticket\ReplyType::user->value ? 'bg-gray-200':'bg-green-100'  @endphp

<div class="flex {{$justify}} items-start px-2 lg:px-2 py-2">
    <div class="{{$bg}} rounded-lg px-3 py-2">
        <p class="text-gray-900 lg:max-w-4xl">
            {!! nl2br($reply->text) !!}
        </p>
        @if($reply->link)
            <div class="mt-2" dir="ltr">
                <a target="_blank" class="font-bold text-blue-600" href="{{$reply->link}}">
                    {{$reply->link}}
                </a>
            </div>
        @endif
        @if($reply->file())
            <div class="mt-2">
                <a target="_blank" class="font-bold text-primary"
                   href="{{$reply->file()}}">
                    <div class="flex">
                        <x-icons.paperclip/>
                        <div class="mr-2"> فایل ضمیمه</div>
                    </div>
                </a>
            </div>
        @endif

        <div class="text-xs text-left mt-4">
            {{$reply->created_at()}}
        </div>
        @if($admin)
            <div class="mt-6">
                @if($reply->user_id == auth()->id())
                    <div class="flex flex-row gap-3">
                        <a href="{{route('admin.reply.edit',$reply->id)}}" wire:navigate class="text-green-600">
                            <x-icons.edit/>
                        </a>
                        <a wire:confirm="آیا از حذف دائمی این پاسخ اطمینان دارید؟"
                           wire:click="delete({{$reply->id}})" class="text-red-600 cursor-pointer">
                            <x-icons.trash-2/>
                        </a>

                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
