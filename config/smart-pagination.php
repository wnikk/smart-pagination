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
    | Show "Previous" and "Next" links
    |--------------------------------------------------------------------------
    */
    'show_prev_next' => true,
];
