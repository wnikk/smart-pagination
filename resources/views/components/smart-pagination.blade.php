
@if ($paginator->hasPages())
<nav>

    <div>
        $displayPage: {{ $displayPage }}<br/>
        $realPage: {{ $realPage }}<br/>
        $totalPages: {{ $totalPages }}<br/>
    </div>

    <ul class="pagination">

        {{-- ← Previous --}}
        @if ($showPrevNext && $realPage > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($realPage-1) }}" aria-label="{{ __('smart-pagination.previous-label') }}">
                    <span aria-hidden="true">{{ __('smart-pagination.previous') }}</span>
                </a>
            </li>
        @endif

        {{-- First group page --}}
        @if ($realPage > 3) <li class="page-item"><a class="page-link" href="{{ $pageUrl(1) }}"> 1 </a></li> @endif
        @if ($realPage > 4) <li class="page-item"><a class="page-link" href="{{ $pageUrl(2) }}"> 2 </a></li> @endif

        {{-- Dots if needed --}}
        @if ($realPage > 5) <li class="page-item disabled"><span class="page-link">{{ __('smart-pagination.dots') }}</span></li> @endif

        {{-- Previous pages --}}
        @if ($realPage > 2) <li class="page-item"><a class="page-link" href="{{ $pageUrl($realPage - 2) }}"> {{ $realPage - 2 }} </a></li> @endif
        @if ($realPage > 1) <li class="page-item"><a class="page-link" href="{{ $pageUrl($realPage - 1) }}"> {{ $realPage - 1 }} </a></li> @endif

        {{-- Current page --}}
        <li class="page-item active" aria-current="page"><span class="page-link">{{ $realPage }}</span></li>

        {{-- Next pages --}}
        @if ($realPage < $totalPages  ) <li class="page-item"><a class="page-link" href="{{ $pageUrl($realPage + 1) }}"> {{ $realPage + 1 }} </a></li> @endif
        @if ($realPage < $totalPages-1) <li class="page-item"><a class="page-link" href="{{ $pageUrl($realPage + 2) }}"> {{ $realPage + 2 }} </a></li> @endif

        {{-- Dots if needed --}}
        @if ($realPage < $totalPages-4) <li class="page-item disabled"><span class="page-link">{{ __('smart-pagination.dots') }}</span></li> @endif

        {{-- Last page group --}}
        @if ($realPage < $totalPages-3) <li class="page-item"><a class="page-link" href="{{ $pageUrl($totalPages - 1) }}"> {{ $totalPages - 1 }} </a></li> @endif
        @if ($realPage < $totalPages-2) <li class="page-item"><a class="page-link" href="{{ $pageUrl($totalPages) }}"> {{ $totalPages }} </a></li> @endif

        {{-- → Next --}}
        @if ($showPrevNext && $realPage < $totalPages)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($realPage + 1) }}" aria-label="{{ __('smart-pagination.next-label') }}">
                    <span aria-hidden="true">{{ __('smart-pagination.next') }}</span>
                </a>
            </li>
        @endif

    </ul>
</nav>
@endif