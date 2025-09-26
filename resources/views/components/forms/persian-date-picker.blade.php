@props([
    'name',
    'value' => null,
    'options' => [] // امکان پاس دادن آپشن‌های دلخواه
])

<div
    wire:ignore
    x-data="{ val: @entangle($attributes->wire('model')).defer }"
    x-init="
        let opts = {
            date: true,
            time: false,
            separatorChar: '/',
            changeMonthRotateYear: true,
            showTodayBtn: true,
            showEmptyBtn: true,
            autoHide: true,
            ...{{ json_encode($options) }}
        };

        // شروع کار
        jalaliDatepicker.startWatch(opts);

        // sync اولیه
        if (val) { $refs.input.value = val }

        // event از اینپوت به Livewire
        $refs.input.addEventListener('change', (e) => {
            val = e.target.value
        });

        // sync از Livewire به اینپوت
        $watch('val', (v) => {
            if ($refs.input.value !== v) {
                $refs.input.value = v ?? ''
            }
        })
    "
>
    <input
        x-ref="input"
        type="text"
        id="jalali-datepicker-{{ $name }}"
        data-jdp
        autocomplete="off"
        placeholder="تاریخ را انتخاب کنید"
        class="border rounded-lg px-3 w-full focus:ring focus:ring-indigo-200 focus:outline-none"
        value="{{ $value }}"
        {{ $attributes->whereStartsWith('wire:model') }}
    >
</div>
