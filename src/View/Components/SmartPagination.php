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
    public string $pagePattern;// default: '?page={page}';

    /**
     * The paginator instance
     *
     * @var LengthAwarePaginator
     */
    protected LengthAwarePaginator $paginator;

    /**
     * Current real page number from paginator
     *
     * @var int
     */
    public int $realPage;

    /**
     * Total number of pages
     *
     * @var int
     */
    public int $total;

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
    public string $baseUrl;

    /**
     * Whether pagination is in reverse mode
     *
     * @var bool
     */
    public bool $reverse;

    /**
     * Create a new component instance.
     *
     * @param LengthAwarePaginator $paginator
     * @param bool|null $reverse
     */
    public function __construct(LengthAwarePaginator $paginator, ?bool $reverse = null, ?string $pagePattern = null)
    {
        $this->paginator = $paginator;
        $this->realPage = $paginator->currentPage();
        $this->total = $paginator->lastPage();

        $this->reverse = $this->resolveReverse($reverse);
        $this->displayPage = $this->reverse
            ? $this->total - $this->realPage + 1
            : $this->realPage;

        $this->pagePattern = $pagePattern??'';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('smart-pagination::components.smart-pagination');
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
            ? $this->total - $realPage + 1
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

    /**
     * Determine reverse mode from config or override.
     *
     * @param bool|null $override
     * @return bool
     */
    private function resolveReverse(?bool $override): bool
    {
        if (!is_null($override)) {
            return $override;
        }

        return config('smart-pagination.reverse_by_default', false);
    }
}
