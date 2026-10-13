<?php

namespace Wnikk\SmartPagination\Macros;

use Illuminate\Database\Eloquent\Builder;
use Wnikk\SmartPagination\Pagination\SmartPaginator;

class PaginateMacro
{
    /**
     * Register the smartPaginate macro on the Eloquent builder.
     *
     * smartPaginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?bool $reverse = null)
     *
     * $page is the page number the visitor asked for (the "display" page);
     * null reads it from the request. Bad input gives the page without a number.
     * In reverse mode the display page is counted from the end: the page
     * without a number holds the newest records, page 1 holds the oldest.
     */
    public static function register(): void
    {
        Builder::macro('smartPaginate', function (
            ?int $perPage = null,
            array $columns = ['*'],
            string $pageName = 'page',
            ?int $page = null,
            ?bool $reverse = null,
        ): SmartPaginator {
            /** @var Builder $this */
            $reverse ??= (bool) config('smart-pagination.reverse_by_default', false);
            $perPage = $perPage ?: $this->getModel()->getPerPage();

            // Anything that is not a whole number from 1 up counts as "no page asked".
            $requested = filter_var($page ?? request()->input($pageName), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $requested = $requested === false ? null : $requested;

            $total    = $this->toBase()->getCountForPagination();
            $lastPage = max((int) ceil($total / $perPage), 1);

            $realPage = match (true) {
                $requested === null => 1,
                ! $reverse          => $requested,
                default             => $lastPage - $requested + 1,
            };

            // A display page past the end gives an empty page, like paginate() does.
            if ($realPage < 1) {
                $realPage = $lastPage + 1;
            }

            return new SmartPaginator(
                $this->forPage($realPage, $perPage)->get($columns),
                $total,
                $perPage,
                $realPage,
                [
                    'path'     => SmartPaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                    'reverse'  => $reverse,
                ]
            );
        });
    }
}
