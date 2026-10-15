@props([
    'searchQuery' => '',
    'selectedDetails' => [],
    'detailOptions' => [],
    'label' => 'Search',
    'placeholder' => 'Search this page',
    'buttonLabel' => 'Filter',
    'clearLabel' => 'Clear',
    'inputId' => 'platform-search',
    'action' => url()->current(),
])

@php
    $searchQuery = trim((string) $searchQuery);
    $selectedDetails = collect($selectedDetails)->map(fn($value) => trim((string) $value))->filter()->values();
    $detailOptions = collect($detailOptions)->map(fn($value) => trim((string) $value))->filter()->unique()->values();
@endphp

<form method="GET" action="{{ $action }}" class="bg-white border rounded-4 p-3 p-lg-4 mb-4">

    <!-- Search Bar Section -->
    <div class="mb-3">
        <label class="form-label fw-semibold small" for="{{ $inputId }}">{{ $label }}</label>
        <div class="input-group">
            <input id="{{ $inputId }}" type="search" name="q" class="form-control"
                placeholder="{{ $placeholder }}" value="{{ $searchQuery }}" style="-webkit-appearance: none;">
            <button type="submit" class="btn btn-danger px-4 m-0">{{ $buttonLabel }}</button>
            @if ($searchQuery !== '')
                <a href="{{ $action }}"
                    class="btn btn-outline-secondary px-4 m-0 d-inline-flex align-items-center justify-content-center">{{ $clearLabel }}</a>
            @endif
        </div>
    </div>

    <!-- Filters Section -->
    @if ($detailOptions->isNotEmpty())
        <div class="mt-3">
            <div class="small fw-semibold text-secondary mb-2">Filter by detail</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($detailOptions as $index => $detailOption)
                    @php($detailId = $inputId . '-detail-' . $index)
                    <input type="checkbox" class="btn-check" id="{{ $detailId }}" name="details[]"
                        value="{{ $detailOption }}"
                        onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()"
                        @checked($selectedDetails->contains($detailOption))>
                    <label class="btn btn-outline-danger btn-sm rounded-pill"
                        for="{{ $detailId }}">{{ $detailOption }}</label>
                @endforeach
            </div>
            <div class="form-text">Choose one or more detail bubbles to narrow the results.</div>
        </div>
    @endif

    <!-- Results Text -->
    @if ($searchQuery !== '')
        <div class="small text-secondary mt-2">Showing results for <span class="fw-semibold">{{ $searchQuery }}</span>
        </div>
    @endif
</form>
