<?php

namespace Wnikk\SmartPagination\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use InvalidArgumentException;
use Wnikk\SmartPagination\Pagination\SmartPaginator;

/**
 * Blade component that renders pagination links.
 *
 * Works with any LengthAwarePaginator. Handles reverse mode (display page
 * numbers counted from the end) and custom URL patterns.
 */
class SmartPagination extends Component
{
    /**
     * Pattern for page URLs, '' means "?{pageName}={page}".
     */
    public string $pagePattern = '';

    public LengthAwarePaginator $paginator;

    /**
     * Real page number from the paginator (1 = the page without a number).
     */
    public int $realPage = 1;

    public int $totalPages = 1;

    /**
     * Page number shown to the visitor (counted from the end in reverse mode).
     */
    public int $displayPage = 1;

    public bool $reverse = false;

    public bool $showPrevNext = true;

    /**
     * Pages shown on each side of the current one.
     */
    public int $onEachSide = 2;

    /**
     * Pages shown at each edge (first and last). 0 hides the edges.
     */
    public int $onEachEdge = 2;

    public function __construct(
        LengthAwarePaginator $paginator,
        ?bool $reverse = null,
        ?string $pagePattern = null,
        ?bool $showPrevNext = null,
        ?int $onEachSide = null,
        ?int $onEachEdge = null,
    ) {
        $this->paginator  = $paginator;
        $this->realPage   = $paginator->currentPage();
        $this->totalPages = $paginator->lastPage();

        $this->reverse      = $reverse ?? (bool) config('smart-pagination.reverse_by_default', false);
        $this->showPrevNext = $showPrevNext ?? (bool) config('smart-pagination.show_prev_next', true);
        $this->displayPage  = $this->pageLabel($this->realPage);
        $this->onEachSide   = max(0, $onEachSide ?? (int) config('smart-pagination.on_each_side', 2));
        $this->onEachEdge   = max(0, $onEachEdge ?? (int) config('smart-pagination.on_each_edge', 2));

        $pagePattern ??= (string) config('smart-pagination.page_pattern', '');
        if ($pagePattern !== '') {
            static::assertPattern($pagePattern);
        }
        $this->pagePattern = $pagePattern;
    }

    /**
     * @throws InvalidArgumentException when the pattern has no {page} placeholder
     */
    public static function assertPattern(string $pattern): void
    {
        if (! str_contains($pattern, '{page}')) {
            throw new InvalidArgumentException("Page pattern must contain the '{page}' placeholder.");
        }
    }

    /**
     * Get the view that represents the component.
     *
     * @param  string|null $view
     * @param  array       $data
     * @return View
     */
    public function render($view = null, $data = [])
    {
        return view($view ?: 'smart-pagination::components.smart-pagination', array_merge($this->data(), $data));
    }

    /**
     * Page number shown to the visitor for a real page.
     */
    public function pageLabel(int $realPage): int
    {
        return $this->reverse ? $this->totalPages - $realPage + 1 : $realPage;
    }

    /**
     * URL of a real page. Real page 1 is the bare path, with query and fragment kept.
     */
    public function pageUrl(int $realPage): string
    {
        $path     = $this->paginator->path();
        $query    = $this->query();
        $fragment = ($f = $this->paginator->fragment()) ? '#'.$f : '';
        $join     = str_contains($path, '?') ? '&' : '?';

        if ($realPage === 1) {
            return $path.($query ? $join.Arr::query($query) : '').$fragment;
        }

        $pattern = $this->pagePattern !== '' ? $this->pagePattern : '?'.$this->paginator->getPageName().'={page}';
        $part    = str_replace('{page}', (string) $this->pageLabel($realPage), $pattern);

        if (str_starts_with($part, '?')) {
            parse_str(substr($part, 1), $own);

            return $path.$join.Arr::query(array_merge($query, $own)).$fragment;
        }

        return rtrim($path, '/').$part.($query ? '?'.Arr::query($query) : '').$fragment;
    }

    /**
     * Pages to show: onEachEdge pages at both ends, onEachSide pages around the current one, dots in the gaps.
     *
     * @return list<array{type: 'dots'}|array{type: 'page', real: int, label: int, url: string, active: bool}>
     */
    public function pages(): array
    {
        $center = min(max($this->realPage, 1), $this->totalPages);
        $wanted = range($center - $this->onEachSide, $center + $this->onEachSide);
        if ($this->onEachEdge > 0) {
            $wanted = [...range(1, $this->onEachEdge), ...$wanted, ...range($this->totalPages - $this->onEachEdge + 1, $this->totalPages)];
        }
        $numbers = array_values(array_unique(array_filter($wanted, fn (int $n) => $n >= 1 && $n <= $this->totalPages)));
        sort($numbers);

        $pages    = [];
        $previous = 0;
        foreach ($numbers as $n) {
            if ($previous > 0 && $n - $previous > 1) {
                $pages[] = ['type' => 'dots'];
            }
            $pages[] = [
                'type'   => 'page',
                'real'   => $n,
                'label'  => $this->pageLabel($n),
                'url'    => $this->pageUrl($n),
                'active' => $n === $this->realPage,
            ];
            $previous = $n;
        }

        return $pages;
    }

    /**
     * Query parameters of the paginator without the page parameter.
     *
     * @return array<string, mixed>
     */
    protected function query(): array
    {
        if ($this->paginator instanceof SmartPaginator) {
            $query = $this->paginator->queryParameters();
        } else {
            parse_str((string) parse_url($this->paginator->url(1), PHP_URL_QUERY), $query);
        }
        unset($query[$this->paginator->getPageName()]);

        return $query;
    }
}
