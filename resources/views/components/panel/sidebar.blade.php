@section('title', 'سایدبار')

{{-- =================================================================== --}}
{{-- Sidebar Section --}}
{{-- =================================================================== --}}

<!-- Off-canvas menu for mobile, sliding from the right -->
<div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-ref="dialog" aria-modal="true">
    <!-- Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50"></div>

    <div class="fixed inset-0 flex">
        <!-- Sidebar Panel -->
        <div x-show="sidebarOpen"
             @click.away="sidebarOpen = false"
             x-transition:enter="transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="relative ml-16 flex w-full max-w-xs flex-1">

            <!-- Close button -->
            <div x-show="sidebarOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute right-full top-0 flex w-16 justify-center pt-5">
                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <x-icons.x class="h-6 w-6 text-white"/>
                </button>
            </div>

            <!-- Sidebar component -->
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-surface-main px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center">
                    <span class="font-bold text-xl text-primary-600">نوین کتاب</span>
                </div>
                <nav class="flex flex-1 flex-col">
                    <x-panel.sidebar-items/>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:fixed lg:inset-y-0 lg:right-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto border-l border-border-color bg-surface-main pr-1 pb-4">
        <div class="flex h-20 shrink-0 items-center justify-center">
            <a href="{{ route('dashboard.index') }}" class="flex items-center gap-x-2">
                <span class="font-bold text-xl text-primary-800">نوین کتاب</span>
            </a>
        </div>
        <nav class="flex flex-1 flex-col">
            <x-panel.sidebar-items/>
        </nav>
    </div>
</div>
