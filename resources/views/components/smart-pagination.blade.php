@php
    $prevLabel = __('smart-pagination.previous');
    $nextLabel = __('smart-pagination.next');
    $dotsLabel = __('smart-pagination.dots');
@endphp

<nav>
    <ul class="pagination">

        {{-- ← Previous --}}
        @if ($total > 1 && $displayPage > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($displayPage - 1) }}" aria-label="{{ $prevLabel }}">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        @endif

        {{-- Always show first page --}}
        <li class="page-item {{ $displayPage === 1 ? 'active' : '' }}">
            <a class="page-link" href="{{ $pageUrl(1) }}">1</a>
        </li>

        {{-- Dots if needed --}}
        @if ($displayPage > 4)
            <li class="page-item disabled"><span class="page-link">{{ $dotsLabel }}</span></li>
        @endif

        {{-- Previous pages --}}
        @for ($i = max(2, $displayPage - 2); $i < $displayPage; $i++)
            <li class="page-item"><a class="page-link" href="{{ $pageUrl($i) }}">{{ $i }}</a></li>
        @endfor

        {{-- Current page --}}
        @if ($total > 1)
            <li class="page-item active" aria-current="page">
                <span class="page-link">{{ $displayPage }}</span>
            </li>
        @endif

        {{-- Next pages --}}
        @for ($i = $displayPage + 1; $i <= min($total, $displayPage + 2); $i++)
            <li class="page-item"><a class="page-link" href="{{ $pageUrl($i) }}">{{ $i }}</a></li>
        @endfor

        {{-- Dots if needed --}}
        @if ($displayPage < $total - 3)
            <li class="page-item disabled"><span class="page-link">{{ $dotsLabel }}</span></li>
        @endif

        {{-- Last page --}}
        @if ($total > 1 && $displayPage !== $total)
            <li class="page-item"><a class="page-link" href="{{ $pageUrl($total) }}">{{ $total }}</a></li>
        @endif

        {{-- → Next --}}
        @if ($total > 1 && $displayPage < $total)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($displayPage + 1) }}" aria-label="{{ $nextLabel }}">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        @endif

    </ul>
</nav>
