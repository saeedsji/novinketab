@props([
    'label',
    'type',
    'placeholder',
    'selectedModels',
    'searchResults',
    'searchTerm',
    'error' => null,
])

<div {{ $attributes }}>
    <label class="form-label">{{ $label }}</label>

    {{-- Search Input & Results Dropdown --}}
    <div class="relative mt-2"
         x-data
         @click.away="$wire.set('searches.{{ $type }}', '')">
        <input type="text"
               wire:model.live.debounce.300ms="searches.{{ $type }}"
               class="form-input"
               placeholder="{{ $placeholder }}">

        {{-- Show dropdown only if search term is not empty --}}
        @if(!empty($searchTerm))
            <div x-show="true" x-transition
                 class="absolute z-20 bg-white mt-1 w-full rounded-md border border-border-color bg-surface-primary shadow-lg max-h-56 overflow-auto">
                @if($searchResults->isNotEmpty())
                    <ul>
                        @foreach($searchResults as $result)
                            <li wire:key="result-{{ $type }}-{{ $result->id }}"
                                wire:click="addItem('{{ $type }}', {{ $result->id }})"
                                class="cursor-pointer select-none relative py-2 px-4 text-text-main hover:bg-surface-secondary">
                                {{ $result->name }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-4 py-2 text-sm text-text-muted">نتیجه‌ای یافت نشد.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Selected Items (Pills) --}}
    <div class="mt-1 flex flex-wrap items-center gap-2 rounded-md border border-border-color p-2 min-h-[42px]">
        @forelse($selectedModels as $model)
            <span wire:key="selected-{{ $type }}-{{ $model->id }}" class="badge-primary">
                {{ $model->name }}
                <button type="button" wire:click="removeItem('{{ $type }}', {{ $model->id }})"
                        class="mr-1.5 -my-1 p-0.5 inline-flex items-center justify-center rounded-full text-primary-200 hover:bg-primary-600 hover:text-white focus:outline-none">
                    <span class="sr-only">حذف {{ $model->name }}</span>
                    <x-icons.x class="h-3.5 w-3.5"/>
                </button>
            </span>
        @empty
            <span class="text-sm text-text-muted px-2">هیچ موردی انتخاب نشده است.</span>
        @endforelse
    </div>



    {{-- Validation Error --}}
    @if($error)
        @error($error) <span class="form-error">{{ $message }}</span> @enderror
    @endif
</div>
