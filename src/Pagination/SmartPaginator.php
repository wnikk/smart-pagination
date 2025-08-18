<?php
namespace Wnikk\SmartPagination\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\View;
use Wnikk\SmartPagination\View\Components\SmartPagination;

class SmartPaginator extends LengthAwarePaginator
{
    /**
     * Whether pagination is in reverse mode
     *
     * @var bool
     */
    public bool $reverse = false;

    /**
     * Create a new paginator instance.
     */
    protected ?SmartPagination $component = null;

    /**
     * @inherited
     */
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);

        $this->component = new SmartPagination(
            $this,
            $this->reverse
        );
    }

    /**
     * Display the pagination links.
     *
     * @param string|array|null $view
     * @param array $data
     * @return \Illuminate\Contracts\View\View|\Closure|string
     **/
    public function links($view = null, $data = [])
    {
        if (is_array($view) && empty($data)) {
            $data = $view;
            $view = null;
        }
        return $this->component->render($data);
    }

    /**
     * Generate the URL for a given page number.
     *
     * @param int $page
     * @return string
     */
    public function pageUrl($page): string
    {
        return $this->component->pageUrl($page);
    }

    /**
     * Set page pattern for URL generation.
     *
     * @param string $path
     * @param string|null $pattern
     * @return void
     * @throws \InvalidArgumentException
     */
    public function withPath($path, $pattern = null)
    {
        if ($pattern) {
            if (!str_contains($pattern, '{page}')) {
                throw new \InvalidArgumentException("Page pattern must contain '{page}' placeholder.");
            }
            $this->component->pagePattern = $pattern;
        }
        parent::withPath($path);
    }
}