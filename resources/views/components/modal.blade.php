@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    data-x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    data-x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    data-x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    data-x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    data-x-on:close.stop="show = false"
    data-x-on:keydown.escape.window="show = false"
    data-x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    data-x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    data-x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        data-x-show="show"
        class="fixed inset-0 transform transition-all"
        data-x-on:click="show = false"
        data-x-transition:enter="ease-out duration-300"
        data-x-transition:enter-start="opacity-0"
        data-x-transition:enter-end="opacity-100"
        data-x-transition:leave="ease-in duration-200"
        data-x-transition:leave-start="opacity-100"
        data-x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        data-x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        data-x-transition:enter="ease-out duration-300"
        data-x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        data-x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        data-x-transition:leave="ease-in duration-200"
        data-x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        data-x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
