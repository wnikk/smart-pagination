<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reverse mode by default
    |--------------------------------------------------------------------------
    | When true, smartPaginate() and the Blade component count pages from the
    | end: the newest records live on the page without a number, and the
    | oldest records stay on page 1 forever.
    */
    'reverse_by_default' => false,

    /*
    |--------------------------------------------------------------------------
    | Default page pattern
    |--------------------------------------------------------------------------
    | Used when no pattern is given to withPath() or to the component.
    | Must contain the {page} placeholder. null means "?page={page}".
    | Examples: '/page-{page}', '-p{page}.html', '?p={page}'.
    */
    'page_pattern' => null,

    /*
    |--------------------------------------------------------------------------
    | Size of the page window
    |--------------------------------------------------------------------------
    | on_each_side: pages shown on each side of the current page.
    | on_each_edge: pages shown at the start and at the end; 0 hides them.
    | Gaps are shown as dots. With 2 and 2, page 6 of 10 gives: 1 2 ... 4 5 6 7 8 9 10
    */
    'on_each_side' => 2,
    'on_each_edge' => 2,

    /*
    |--------------------------------------------------------------------------
    | Show "Previous" and "Next" links
    |--------------------------------------------------------------------------
    */
    'show_prev_next' => true,
];
