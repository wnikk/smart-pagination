<?php

namespace Wnikk\SmartPagination\Pagination;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Wnikk\SmartPagination\View\Components\SmartPagination;

/**
 * LengthAwarePaginator that knows about reverse mode and page patterns.
 *
 * Every URL the paginator gives out (url(), nextPageUrl(), toArray(), links())
 * goes through the SmartPagination component, so JSON and HTML agree.
 */
class SmartPaginator extends LengthAwarePaginator
{
    /**
     * Whether pagination is in reverse mode.
     */
    public bool $reverse = false;

    /**
     * Page pattern for URL generation, '' means the default "?page={page}".
     */
    public string $pagePattern = '';

    protected ?SmartPagination $component = null;

    /**
     * @param array{path?: string, pageName?: string, reverse?: bool, pagePattern?: string} $options
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);

        $this->reverse     = (bool) ($options['reverse'] ?? false);
        $this->pagePattern = (string) ($options['pagePattern'] ?? '');
    }

    /**
     * The Blade component that builds URLs and the page window.
     */
    public function component(): SmartPagination
    {
        return $this->component ??= new SmartPagination($this, $this->reverse, $this->pagePattern ?: null);
    }

    /**
     * Page number shown to the visitor for the current page.
     */
    public function displayPage(): int
    {
        return $this->component()->pageLabel($this->currentPage());
    }

    /**
     * Set the base path and, optionally, the page pattern.
     *
     * @param  string      $path
     * @param  string|null $pattern must contain {page}; '' or null keeps the current pattern
     * @return $this
     */
    public function withPath($path, ?string $pattern = null): static
    {
        parent::withPath($path);

        if ($pattern !== null && $pattern !== '') {
            $this->withPagePattern($pattern);
        }

        return $this;
    }

    /**
     * Set the page pattern, for example '/page-{page}' or '?p={page}'.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException when the pattern has no {page} placeholder
     */
    public function withPagePattern(string $pattern): static
    {
        SmartPagination::assertPattern($pattern);

        $this->pagePattern              = $pattern;
        $this->component()->pagePattern = $pattern;

        return $this;
    }

    /**
     * Number of pages shown on each side of the current one.
     *
     * @param  int   $count
     * @return $this
     */
    public function onEachSide($count): static
    {
        $this->onEachSide              = max(0, (int) $count);
        $this->component()->onEachSide = $this->onEachSide;

        return $this;
    }

    /**
     * Number of pages shown at each edge; 0 hides the first and last pages.
     *
     * @return $this
     */
    public function onEachEdge(int $count): static
    {
        $this->component()->onEachEdge = max(0, $count);

        return $this;
    }

    /**
     * Query string parameters added with appends().
     *
     * @return array<string, mixed>
     */
    public function queryParameters(): array
    {
        return $this->query;
    }

    /**
     * URL of a real page (1 = the page without a number).
     *
     * @param int $page
     */
    public function url($page): string
    {
        return $this->component()->pageUrl((int) $page);
    }

    /**
     * Alias of url() that makes the "real page" meaning explicit.
     */
    public function pageUrl(int $realPage): string
    {
        return $this->component()->pageUrl($realPage);
    }

    /**
     * Render the pagination links.
     *
     * @param  string|array|null $view
     * @param  array             $data
     * @return View
     */
    public function render($view = null, $data = [])
    {
        if (is_array($view)) {
            [$view, $data] = [null, $view + $data];
        }

        return $this->component()->render($view, $data);
    }

    /**
     * Link array for JSON output, with display page numbers as labels.
     */
    public function linkCollection(): Collection
    {
        $links = new Collection;

        foreach ($this->component()->pages() as $page) {
            $links->push($page['type'] === 'dots'
                ? ['url' => null, 'label' => '...', 'active' => false]
                : ['url' => $page['url'], 'label' => (string) $page['label'], 'page' => $page['real'], 'active' => $page['active']]);
        }

        return $links
            ->prepend(['url' => $this->previousPageUrl(), 'label' => __('pagination.previous'), 'active' => false])
            ->push(['url' => $this->nextPageUrl(), 'label' => __('pagination.next'), 'active' => false]);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'display_page' => $this->displayPage(),
            'reverse'      => $this->reverse,
        ];
    }
}
