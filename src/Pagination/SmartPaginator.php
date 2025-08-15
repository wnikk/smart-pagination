<?php
namespace Wnikk\SmartPagination\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Wnikk\SmartPagination\View\Components\SmartPagination;
use Illuminate\Pagination\PaginationState;

class SmartPaginator extends LengthAwarePaginator
{
    /**
     * Whether pagination is in reverse mode
     *
     * @var bool
     */
    public bool $reverse = false;

    /**
     * Display the pagination links.
     **/
    public function links($view = null, $data = [])
    {
        if (is_array($view) && empty($data)) {
            $data = $view;
            $view = null;
        }
        $component = new SmartPagination(
            $this,
            $this->reverse
        );
        return $component->render($data);
    }
}