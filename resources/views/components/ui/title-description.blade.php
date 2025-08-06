@props(['title','description'])

<!-- Description Section -->
<div x-data="{ showMore: false }" class="p-6 bg-white rounded-md">
    <div class="text-gray-700 mb-2 text-justify">
        <div x-show="!showMore">
            {{\Illuminate\Support\Str::limit($description,160)}}
        </div>

        <div x-show="showMore">
            {!! nl2br($description) !!}
        </div>
    </div>
    <a href="#" @click.prevent="showMore = !showMore" class="text-blue-600  ">
        <span x-text="showMore ? 'نمایش کمتر' : 'نمایش بیشتر'"></span>
    </a>
</div>

