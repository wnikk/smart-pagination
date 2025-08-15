<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Wnikk\SmartPagination\View\Components\SmartPagination;
use Tests\TestCase;

class SmartPaginationTest extends TestCase
{
    protected function createPaginator(int $currentPage, int $totalItems = 100, int $perPage = 10): LengthAwarePaginator
    {
        $items = Collection::times($totalItems, fn ($i) => "Item $i");
        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $totalItems,
            $perPage,
            $currentPage,
            ['path' => '/posts', 'pageName' => 'page']
        );
    }

    public function test_standard_page_url_generation()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false);

        $this->assertEquals('/posts', $component->pageUrl(1));
        $this->assertEquals('/posts?page=2', $component->pageUrl(2));
    }

    public function test_reverse_page_url_generation()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, true);

        $this->assertEquals('/posts?page=10', $component->pageUrl(1)); // reverse: page 1 → real page 10
        $this->assertEquals('/posts?page=9', $component->pageUrl(2));
        $this->assertEquals('/posts', $component->pageUrl(10)); // reverse: last page → real page 1
    }

    public function test_display_page_number_in_reverse_mode()
    {
        $paginator = $this->createPaginator(1); // real page 1
        $component = new SmartPagination($paginator, true);

        $this->assertEquals(10, $component->displayPage); // total 10 pages, reverse: display 10
    }

    public function test_canonical_url_without_page_parameter()
    {
        $this->withoutMiddleware();
        $this->get('/posts?page=1&tag=city');

        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false);

        $canonical = $component->canonicalUrl();
        $this->assertEquals('/posts?tag=city', $canonical);
    }

    public function test_canonical_url_with_query_parameters()
    {
        $this->withoutMiddleware();
        $this->get('/posts?page=3&tag=city&sort=new');

        $paginator = $this->createPaginator(3);
        $component = new SmartPagination($paginator, false);

        $canonical = $component->canonicalUrl();
        $this->assertEquals('/posts?tag=city&sort=new', $canonical);
    }
}
