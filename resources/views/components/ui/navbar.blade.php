<nav class="relative p-2 hidden lg:block lg:px-32">
    <div class="flex  items-center justify-between">
        <div class="pt-2">
            <a href="/"><img class="h-24" src="/assets/images/logo/original.png" alt="Karazma Logo"></a>
        </div>
        <div class="space-x-8 md:flex hidden" dir="ltr">
            <a target="_blank" href="https://blog.karazma.co/" class="hover:text-darkGrayishBlue">مقالات</a>
            @if(auth()->check())
                <a href="{{route('user.main')}}" class="hover:text-darkGrayishBlue">اپلیکیشن کارازما</a>
            @else
                <a href="{{route('user.login')}}" class="hover:text-darkGrayishBlue">ورود و ثبت نام</a>
            @endif
        </div>


    </div>


</nav>
