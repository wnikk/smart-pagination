@if ($paginator->hasPages())
<nav aria-label="{{ __('smart-pagination::pagination.label') }}">
    <ul class="pagination">
        @if ($showPrevNext && $realPage > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($realPage - 1) }}" rel="prev" aria-label="{{ __('smart-pagination::pagination.previous-label') }}">
                    <span aria-hidden="true">{{ __('smart-pagination::pagination.previous') }}</span>
                </a>
            </li>
        @endif

        @foreach ($pages() as $page)
            @if ($page['type'] === 'dots')
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ __('smart-pagination::pagination.dots') }}</span></li>
            @elseif ($page['active'])
                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page['label'] }}</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $page['url'] }}">{{ $page['label'] }}</a></li>
            @endif
        @endforeach

        @if ($showPrevNext && $realPage < $totalPages)
            <li class="page-item">
                <a class="page-link" href="{{ $pageUrl($realPage + 1) }}" rel="next" aria-label="{{ __('smart-pagination::pagination.next-label') }}">
                    <span aria-hidden="true">{{ __('smart-pagination::pagination.next') }}</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
@endif
