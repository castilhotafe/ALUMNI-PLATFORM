@props([
    'id',
    'name',
    'label',
    'options' => [],
    'selected' => '',
    'placeholder' => 'Search or choose...',
    'helperText' => null,
    'required' => false,
    'xDisabled' => null,
])

@php
    $normalizedOptions = collect($options)
        ->map(function ($option) {
            if (is_array($option)) {
                return [
                    'value' => (string) ($option['value'] ?? ''),
                    'label' => (string) ($option['label'] ?? ''),
                ];
            }

            return [
                'value' => (string) data_get($option, 'value', data_get($option, 'id', '')),
                'label' => (string) data_get($option, 'label', data_get($option, 'name', '')),
            ];
        })
        ->filter(fn($option) => $option['value'] !== '' && $option['label'] !== '')
        ->values();

    $selectedValue = filled($selected) ? (string) $selected : '';
@endphp

<div class="nmtafe-searchable-select" data-x-data="searchableSelect({ selectedValue: @js($selectedValue), options: @js($normalizedOptions) })" data-x-on:click.outside="open = false">
    <label for="{{ $id }}-search" class="form-label fw-semibold">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" data-x-bind:value="selectedValue" value="{{ $selectedValue }}">

    <div class="position-relative">
        <input id="{{ $id }}-search" type="search" class="form-control nmtafe-searchable-select__input"
            placeholder="{{ $placeholder }}" autocomplete="off" data-x-model="query" data-x-on:focus="open = true"
            data-x-on:input="open = true"
            @if ($xDisabled) data-x-bind:disabled="{{ $xDisabled }}" @endif>

        <div class="nmtafe-searchable-select__panel" data-x-show="open" data-x-transition>
            <div class="nmtafe-searchable-select__list">
                <template data-x-for="option in filteredOptions" data-x-bind:key="option.value">
                    <button type="button" class="nmtafe-searchable-select__option" data-x-on:click="select(option)"
                        @if ($xDisabled) data-x-bind:disabled="{{ $xDisabled }}" @endif>
                        <span class="nmtafe-searchable-select__option-label" data-x-text="option.label"></span>
                    </button>
                </template>

                <div class="nmtafe-searchable-select__empty" data-x-show="filteredOptions.length === 0">
                    No matches found.
                </div>
            </div>
        </div>
    </div>

    @if ($helperText)
        <div class="form-text">{{ $helperText }}</div>
    @endif
</div>
