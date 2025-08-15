<?php
namespace Wnikk\SmartPagination\Macros;

use Illuminate\Database\Eloquent\Builder;
use Wnikk\SmartPagination\Pagination\SmartPaginator;

class PaginateMacro
{
    /**
     * Register the smartPaginate macro on the Builder class.
     *
     * @return void
     */
    public static function register(): void
    {
        Builder::macro('smartPaginate', function (
            int $perPage = null,
            array $columns = ['*'],
            string $pageName = 'page',
            int $page = null,
            ?bool $reverse = null
        ) {
            //$page = $page ?: SmartPaginator::resolveCurrentPage($pageName, null);
            $page    = $page ?: request()->get($pageName, null);
            $perPage = $perPage ?: $this->getModel()->getPerPage();
            $count   = $this->toBase()->getCountForPagination();
            $countPage = (int) ceil($count / $perPage);
            if ($reverse === null) {$reverse = config('smart-pagination.reverse', false);}
            if ($reverse) {
                $reversePage = is_null($page)? 1 : $countPage - ($page - 1);
            } else {
                $reversePage = $page;
            }

            return new SmartPaginator(
                $this->forPage($reversePage, $perPage)->get($columns),
                $count,
                $perPage,
                $reversePage,
                [
                    'path' => SmartPaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                    'reverse' => $reverse,
                ]
            );
        });
    }
}
