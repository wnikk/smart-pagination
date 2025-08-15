<?php
namespace Wnikk\SmartPagination\View\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\Component;

/**
 * SmartPagination component for Laravel Blade.
 *
 * Handles reverse pagination and canonical URL generation.
 */
class SmartPagination extends Component
{
    /**
     * Pattern used to generate page URLs
     *
     * @var string
     */
    public string $pagePattern = '';// default: '?page={page}';

    /**
     * The paginator instance
     *
     * @var LengthAwarePaginator
     */
    public LengthAwarePaginator $paginator;

    /**
     * Current real page number from paginator
     *
     * @var int
     */
    public int $realPage = 1;

    /**
     * Total number of pages
     *
     * @var int
     */
    public int $totalPages = 1;

    /**
     * Displayed page number (adjusted for reverse mode)
     *
     * @var int
     */
    public int $displayPage;

    /**
     * Base URL for pagination links
     *
     * @var string
     */
    public string $baseUrl = '';

    /**
     * Whether pagination is in reverse mode
     *
     * @var bool
     */
    public bool $reverse = false;

    /**
     * Whether to show "Previous" and "Next" links
     *
     * @var bool
     */
    public bool $showPrevNext = true;

    /**
     * Create a new component instance.
     *
     * @param LengthAwarePaginator|array $paginator
     * @param bool|null $reverse
     */
    public function __construct(LengthAwarePaginator|array $paginator, ?bool $reverse = null, ?string $pagePattern = null, ?bool $showPrevNext = null)
    {
        if (is_array($paginator)) {
            $reverse = $paginator['reverse'] ?? $reverse;
            $pagePattern = $paginator['pagePattern'] ?? $pagePattern;
            $showPrevNext = $paginator['showPrevNext'] ?? $showPrevNext;
            $paginator = $paginator['paginator'] ?? null;
        }
        if (!$paginator instanceof LengthAwarePaginator) {
            throw new \InvalidArgumentException('Expected paginator to be an instance of LengthAwarePaginator.');
        }

        $this->paginator = $paginator;
        $this->realPage = $paginator->currentPage();
        $this->totalPages = $paginator->lastPage();

        $this->reverse = $reverse ?? config('smart-pagination.reverse_by_default', false);
        $this->showPrevNext = $showPrevNext ?? config('smart-pagination.show_prev_next', true);

        $this->displayPage = $this->reverse
            ? $this->totalPages - $this->realPage + 1
            : $this->realPage;

        $this->pagePattern = $pagePattern??'';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render($data = [])
    {
        $data = $data + [
            'paginator' => $this->paginator,
            'realPage' => $this->realPage,
            'totalPages' => $this->totalPages,
            'displayPage' => $this->displayPage,
            'baseUrl' => $this->baseUrl,
            'reverse' => $this->reverse,
            'pagePattern' => $this->pagePattern,
            'showPrevNext' => $this->showPrevNext,
            'pageUrl' => [$this, 'pageUrl'],
        ];
        return view('smart-pagination::components.smart-pagination', $data);
    }

    /**
     * Generate URL for a given display page.
     *
     * @param int $realPage
     * @return string
     */
    public function pageUrl(int $realPage): string
    {
        $displayPage = $this->reverse
            ? $this->totalPages - $realPage + 1
            : $realPage;

        // Return the URL without the page parameter
        if ($realPage === 1) {
            return $this->paginator->path();
        }

        // If no custom pattern is provided, use paginator's default URL
        if (!$this->pagePattern) {
            return $this->paginator->url($displayPage);
        }

        // Validate that the pattern contains the {page} placeholder
        if (!str_contains($this->pagePattern, '{page}')) {
            throw new \InvalidArgumentException("Page pattern must contain '{page}' placeholder.");
        }
        $pagePart = str_replace('{page}', $displayPage, $this->pagePattern);

        // Get base path from paginator
        $baseUrl = rtrim($this->paginator->path(), '/');

        // If pattern starts with '?', treat as query string
        if (str_starts_with($pagePart, '?')) {
            $params = $this->paginator->getOptions()['query'] ?? [];
            unset($params['page']);
            $query = http_build_query($params);
            $prefix = $query ? '?' . $query . '&' : '?';
            return $baseUrl . $prefix . ltrim($pagePart, '?');
        }

        // Otherwise treat as path segment
        return $baseUrl . $pagePart;
    }
}
