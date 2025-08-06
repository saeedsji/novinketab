<!-- Sticky header -->
<div class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4  sm:gap-x-6 sm:px-6 lg:px-8">
    <!-- Mobile menu button -->
    <button @click="sidebarOpen = true" type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden">
        <span class="sr-only">Open sidebar</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <div class="flex items-center flex-1">
            <h1 class="text-md md:hidden">پنل مدیریتی</h1>
        </div>

        <!-- Profile dropdown -->
        <div class="flex items-center gap-x-4 lg:gap-x-6">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button" class="cursor-pointer -m-1.5 flex items-center p-1.5">
                    <span class="sr-only">Open user menu</span>
                    <img class="h-8 w-8 rounded-full bg-gray-50" src="/assets/images/avatar.webp" alt="profile">
                    <span class="flex lg:items-center">
                        <span class="mr-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">
                            {{ auth()->user()->name ?? 'کاربر مهمان' }}
                        </span>
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 z-10 mt-2.5 w-48 origin-top-left rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                    x-cloak
                >
                    <a href="{{route('dashboard.index')}}" class="flex items-center gap-x-3 px-3 py-2 text-sm font-semibold leading-6 text-gray-900 hover:bg-gray-50">
                        <x-icons.home class="h-5 w-5 text-gray-400" />
                        <span>داشبورد</span>
                    </a>
                    <a href="{{ route('logout') }}"
                       class="flex items-center gap-x-3 px-3 py-2 text-sm font-semibold leading-6 text-gray-900 hover:bg-gray-50">
                        <x-icons.log-out class="h-5 w-5 text-gray-400" />
                        <span>خروج</span>
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
