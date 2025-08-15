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
    public function __construct(LengthAwarePaginator $paginator, ?bool $reverse = null)
    {
        $this->realPage = $paginator->currentPage();
        $this->total = $paginator->lastPage();

        $this->reverse = $this->resolveReverse($reverse);
        $this->displayPage = $this->reverse
            ? $this->total - $this->realPage + 1
            : $this->realPage;

        $this->baseUrl = $this->resolveBaseUrl();
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
     * @param int $displayPage
     * @return string
     */
    public function pageUrl(int $displayPage): string
    {
        $realPage = $this->reverse
            ? $this->total - $displayPage + 1
            : $displayPage;

        return $realPage === 1 ? $this->baseUrl : $this->baseUrl . $realPage;
    }

    /**
     * Generate canonical URL for SEO.
     * Includes all query parameters except 'page'.
     * Omits page parameter if it's the first page.
     *
     * @return string
     */
    public function canonicalUrl(): string
    {
        $params = request()->except('page');
        $query = http_build_query($params);

        $isFirstPage = $this->realPage === 1;

        return url()->current() . ($query ? '?' . $query : '');
    }

    /**
     * Build base URL for pagination links.
     *
     * @return string
     */
    private function resolveBaseUrl(): string
    {
        $params = request()->except('page');
        $query = http_build_query($params);

        return url()->current() . ($query ? '?' . $query . '&page=' : '?page=');
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
