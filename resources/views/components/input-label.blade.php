@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label fw-semibold']) }}>
    {{ $value ?? $slot }}
</label>
