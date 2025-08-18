<?php
namespace Tests\Unit;

use Tests\TestCase;
use Tests\Fixtures\ArticleModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Wnikk\SmartPagination\View\Components\SmartPagination;

class SmartPaginationTest extends TestCase
{
    /**
     * Create a paginator instance for testing.
     *
     * @param int $currentPage
     * @param int $totalItems
     * @param int $perPage
     * @return LengthAwarePaginator
     */
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

    /**
     * Test the constructor initializes properties correctly.
     */
    public function test_standard_page_url_generation()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false);

        $this->assertEquals('/posts', $component->pageUrl(1));
        $this->assertEquals('/posts?page=2', $component->pageUrl(2));
    }

    /**
     * Test the constructor initializes properties correctly in reverse mode.
     */
    public function test_reverse_page_url_generation()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, true);

        $this->assertEquals('/posts', $component->pageUrl(1)); // reverse: display first page →  page 1
        $this->assertEquals('/posts?page=9', $component->pageUrl(2)); // reverse: display page 9 →  page 2
        $this->assertEquals('/posts?page=1', $component->pageUrl(10)); // reverse: last page → display page 10
    }

    /**
     * Test the displayPage property in standard mode.
     */
    public function test_display_page_number_in_reverse_mode()
    {
        $paginator = $this->createPaginator(1); // display page 1
        $component = new SmartPagination($paginator, true);

        $this->assertEquals(10, $component->displayPage); // total 10 pages, reverse: display 10
    }

    /**
     * Test the displayPage property in reverse mode.
     */
    public function test_custom_path_pattern_generates_seo_friendly_url()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false, '-{page}p');

        $this->assertEquals('/posts', $component->pageUrl(1));
        $this->assertEquals('/posts-2p', $component->pageUrl(2));
        $this->assertEquals('/posts-10p', $component->pageUrl(10));
    }

    /**
     * Test the custom HTML suffix pattern for pagination URLs.
     */
    public function test_custom_html_suffix_pattern()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false, '-p{page}.html');

        $this->assertEquals('/posts', $component->pageUrl(1));
        $this->assertEquals('/posts-p2.html', $component->pageUrl(2));
    }

    /**
     * Test the custom query pattern for pagination URLs.
     */
    public function test_custom_query_pattern()
    {
        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false, '?p={page}');

        $this->assertEquals('/posts', $component->pageUrl(1));
        $this->assertEquals('/posts?p=2', $component->pageUrl(2));
    }

    /**
     * Test the reverse mode with a custom pattern.
     */
    public function test_reverse_mode_with_custom_pattern()
    {
        $paginator = $this->createPaginator(1); // real page 1
        $component = new SmartPagination($paginator, true, '/page-{page}');

        $this->assertEquals('/posts', $component->pageUrl(1)); // display empty → real 1
        $this->assertEquals('/posts/page-9', $component->pageUrl(2));
        $this->assertEquals('/posts/page-1', $component->pageUrl(10)); // display 1 → real 10
    }

    /**
     * Test the pageUrl method with a custom pattern.
     */
    public function test_invalid_pattern_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $paginator = $this->createPaginator(1);
        $component = new SmartPagination($paginator, false, 'invalid-pattern-without-placeholder');
        $component->pageUrl(2);
    }

    /**
     * Test the pagination of ArticleModel using paginate.
     */
    public function test_article_model_pagination()
    {
        // Create 100 articles
        ArticleModel::factory()->count(100)->create();

        // default paginate articles (10 per page)
        $paginator = ArticleModel::paginate(10);
        $paginator->withPath('/article');

        $this->assertEquals('/article', $paginator->path());
        $this->assertEquals('/article?page=1', $paginator->url(1));// default page 1 URL
        $this->assertEquals('/article?page=2', $paginator->url(2));
        $this->assertEquals('/article?page=10', $paginator->url(10));
    }

    /**
     * Test the pagination of ArticleModel using smartPaginate.
     */
    public function test_article_model_smart_pagination()
    {
        // Create 100 articles
        ArticleModel::factory()->count(100)->create();

        // Smart paginate articles (10 per page)
        $paginator = ArticleModel::smartPaginate(10);
        $paginator->withPath('/article');

        $this->assertEquals('/article', $paginator->path());
        $this->assertEquals('/article', $paginator->pageUrl(1)); // reverse: display first page →  page 1
        $this->assertEquals('/article?page=2', $paginator->pageUrl(2)); // reverse: display page 2 →  page 2
        $this->assertEquals('/article?page=10', $paginator->pageUrl(10)); // reverse: last page → display page 10
        $this->assertEquals(10, $paginator->lastPage());
    }

    /**
     * Test the pagination of ArticleModel using smartPaginate reverse.
     */
    public function test_article_model_smart_pagination_reverse()
    {
        // Create 100 articles
        ArticleModel::factory()->count(100)->create();

        // Smart paginate articles (10 per page)
        $paginator = ArticleModel::smartPaginate(10, reverse: true);
        $paginator->withPath('/article');

        $this->assertEquals('/article', $paginator->path());
        $this->assertEquals('/article', $paginator->pageUrl(1)); // reverse: display first page →  page 1
        $this->assertEquals('/article?page=9', $paginator->pageUrl(2)); // reverse: display page 9 →  page 2
        $this->assertEquals('/article?page=1', $paginator->pageUrl(10)); // reverse: last page → display page 10
        $this->assertEquals(10, $paginator->lastPage());
    }

    /**
     * Test the pagination of ArticleModel using smartPaginate reverse + SEO.
     */
    public function test_article_model_smart_pagination_reverse_seo()
    {
        // Create 100 articles
        ArticleModel::factory()->count(100)->create();

        // Smart paginate articles (10 per page)
        $paginator = ArticleModel::smartPaginate(10, reverse: true);
        $paginator->withPath('/article', '/p{page}.html'); // custom pattern for SEO

        $this->assertEquals('/article', $paginator->path());
        $this->assertEquals('/article', $paginator->pageUrl(1)); // reverse: display first page →  page 1
        $this->assertEquals('/article/p9.html', $paginator->pageUrl(2)); // reverse: display page 9 →  page 2
        $this->assertEquals('/article/p1.html', $paginator->pageUrl(10)); // reverse: last page → display page 10
        $this->assertEquals(10, $paginator->lastPage());
    }
}
