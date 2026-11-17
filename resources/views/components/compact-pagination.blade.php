@props([
    'routeName',
    'pageName',
    'currentPage',
    'totalPages',
    'queryParams' => [],
    'anchor' => null,
])

@php
    $currentPage = max(1, (int) $currentPage);
    $totalPages = max(1, (int) $totalPages);
    $anchorSuffix = filled($anchor) ? '#'.$anchor : '';
    $previousUrl = $currentPage <= 1 ? null : route($routeName, array_merge($queryParams, [$pageName => $currentPage - 1])).$anchorSuffix;
    $nextUrl = $currentPage >= $totalPages ? null : route($routeName, array_merge($queryParams, [$pageName => $currentPage + 1])).$anchorSuffix;
@endphp

<nav class="nmtafe-pagination-bar" aria-label="Pagination">
    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2 gap-lg-3 overflow-auto">
        <ul class="pagination justify-content-center mb-0 flex-shrink-0">
            
            {{-- Previous Button --}}
            @if ($previousUrl === null)
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $previousUrl }}" aria-label="Previous">Previous</a>
                </li>
            @endif

            {{-- Current Page Indicator --}}
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link text-nowrap">Page {{ $currentPage }} of {{ $totalPages }}</span>
            </li>

            {{-- Next Button --}}
            @if ($nextUrl === null)
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">Next</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $nextUrl }}" aria-label="Next">Next</a>
                </li>
            @endif

        </ul>

        {{-- Jump to Page Form --}}
        <form method="GET" action="{{ route($routeName) }}{{ filled($anchor) ? '#'.$anchor : '' }}" class="d-flex align-items-center gap-2 mb-0 flex-shrink-0">
            @foreach ($queryParams as $key => $value)
                @continue($key === $pageName)

                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="{{ $pageName }}-jump" class="small text-secondary text-nowrap mb-0">Jump to page</label>
            <input
                id="{{ $pageName }}-jump"
                name="{{ $pageName }}"
                type="number"
                min="1"
                max="{{ $totalPages }}"
                class="form-control form-control-sm w-auto"
                value="{{ $currentPage }}"
                @disabled($totalPages <= 1)
                aria-label="Jump to page number"
            >
            <button type="submit" class="btn btn-danger btn-sm" @disabled($totalPages <= 1)>Go</button>
        </form>
    </div>
</nav>