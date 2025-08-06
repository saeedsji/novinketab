@section('title', 'داشبورد')

<div class="flex flex-col">
    <div class="font-bold">{{$user->name}} - {{$user->info()}}</div>
    <div class="flex gap-2 mt-2">
        <h1 class="font-semibold">به پنل مدیریتی خوش آمدید!</h1>
        <span class="text-red-600"><x-icons.heart/></span>
    </div>
    <div class="mt-4">

    </div>
</div>

