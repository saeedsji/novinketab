@props(['allTags'])

{{-- ۱. بخش HTML کامپوننت --}}
{{-- x-data حالا فقط تابع جاوااسکریپت را فراخوانی می‌کند و داده‌ها را به آن پاس می‌دهد --}}
<div
    x-data="tagFilterComponent({
        selectedTags: @entangle($attributes->wire('model')).live,
        allTags: @json($allTags)
    })"
    x-on:click.outside="open = false"
>
    <label for="tags-filter-input" class="form-label">فیلتر بر اساس تگ‌ها</label>
    <div class="relative mt-1">
        <div class="form-input flex flex-wrap items-center gap-2 min-h-[38px] cursor-text" @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())">
            <template x-for="tag in selectedTags" :key="tag">
                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-sky-100 dark:bg-sky-800/50 px-2 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">
                    <span x-text="tag"></span>
                    <button type="button" @click.stop="selectedTags = selectedTags.filter(t => t !== tag)" class="group relative h-3.5 w-3.5 rounded-sm hover:bg-sky-500/20">
                        <x-icons.x class="h-3 w-3 text-sky-600/50 group-hover:text-sky-600/75" />
                    </button>
                </span>
            </template>
            <input
                x-ref="searchInput"
                x-model="search"
                @keydown.enter.prevent="if(filteredTags.length > 0) toggleTag(filteredTags[0])"
                @keydown.backspace="if(search === '' && selectedTags.length > 0) selectedTags.pop()"
                type="text"
                id="tags-filter-input"
                placeholder="افزودن تگ..."
                class="flex-grow bg-transparent border-0 focus:ring-0 p-0 text-sm"
            >
        </div>

        <div
            x-show="open"
            x-transition
            class="absolute z-10 mt-1 w-full rounded-md bg-surface-main dark:bg-surface-secondary shadow-lg max-h-60 overflow-auto border border-border-main"
        >
            <ul class="py-1">
                <template x-for="tag in filteredTags" :key="tag">
                    <li @click="toggleTag(tag)" class="relative cursor-pointer select-none py-2 px-4 text-text-main hover:bg-surface-secondary dark:hover:bg-surface-main/40">
                        <span class="block truncate" x-text="tag"></span>
                    </li>
                </template>
                <template x-if="filteredTags.length === 0 && search !== ''">
                    <li class="px-4 py-2 text-sm text-text-muted">تگی با این نام یافت نشد.</li>
                </template>
            </ul>
        </div>
    </div>
</div>

{{-- ۲. بخش JavaScript کامپوننت --}}
{{-- با استفاده از @once، این اسکریپت فقط یک بار در صفحه لود می‌شود، حتی اگر از کامپوننت چند بار استفاده کنید --}}
@once
    <script>
        function tagFilterComponent(config) {
            return {
                open: false,
                search: '',
                selectedTags: config.selectedTags,
                allTags: config.allTags,

                get filteredTags() {
                    if (this.search === '') return this.allTags.filter(tag => !this.selectedTags.includes(tag));
                    return this.allTags.filter(tag => {
                        return !this.selectedTags.includes(tag) && tag.toLowerCase().includes(this.search.toLowerCase());
                    });
                },

                toggleTag(tag) {
                    if (this.selectedTags.includes(tag)) {
                        this.selectedTags = this.selectedTags.filter(t => t !== tag);
                    } else {
                        this.selectedTags.push(tag);
                    }
                    this.search = '';
                }
            }
        }
    </script>
@endonce
