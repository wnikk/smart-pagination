<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Tests\Fixtures\ArticleModel;
use Tests\TestCase;
use Wnikk\SmartPagination\Pagination\SmartPaginator;

class SmartPaginatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ArticleModel::factory()->count(100)->create();
    }

    public function test_standard_mode(): void
    {
        $paginator = ArticleModel::smartPaginate(10)->withPath('/article');

        $this->assertInstanceOf(SmartPaginator::class, $paginator);
        $this->assertSame('/article', $paginator->pageUrl(1));
        $this->assertSame('/article?page=2', $paginator->pageUrl(2));
        $this->assertSame('/article?page=10', $paginator->url(10));
        $this->assertSame(10, $paginator->lastPage());
        $this->assertSame(1, $paginator->currentPage());
        $this->assertSame(1, $paginator->displayPage());
        $this->assertSame(1, $paginator->items()[0]->id);
    }

    public function test_reverse_mode(): void
    {
        $paginator = ArticleModel::smartPaginate(10, reverse: true)->withPath('/article');

        $this->assertSame('/article', $paginator->pageUrl(1));
        $this->assertSame('/article?page=9', $paginator->pageUrl(2));
        $this->assertSame('/article?page=1', $paginator->url(10));
        $this->assertSame(10, $paginator->displayPage());
    }

    public function test_reverse_with_pattern_in_one_call(): void
    {
        $paginator = ArticleModel::smartPaginate(10, reverse: true)->withPath('/article', '/p{page}.html');

        $this->assertSame('/article', $paginator->pageUrl(1));
        $this->assertSame('/article/p9.html', $paginator->pageUrl(2));
        $this->assertSame('/article/p1.html', $paginator->pageUrl(10));
        $this->assertSame('/article/p9.html', $paginator->nextPageUrl());
        $this->assertNull($paginator->previousPageUrl());
    }

    public function test_with_path_is_chainable_and_pattern_is_checked(): void
    {
        $paginator = ArticleModel::smartPaginate(10);

        $this->assertSame($paginator, $paginator->withPath('/a'));
        $this->assertSame($paginator, $paginator->withPagePattern('/p{page}'));

        $this->expectException(InvalidArgumentException::class);
        $paginator->withPath('/a', 'broken');
    }

    public function test_requested_display_page_maps_to_real_page_in_reverse(): void
    {
        $paginator = ArticleModel::orderByDesc('id')->smartPaginate(10, page: 1, reverse: true);

        $this->assertSame(10, $paginator->currentPage());
        $this->assertSame(1, $paginator->displayPage());
        $this->assertSame(10, $paginator->items()[0]->id);
    }

    public function test_page_is_read_from_the_request(): void
    {
        $this->app->instance('request', Request::create('/article', 'GET', ['page' => '3']));

        $paginator = ArticleModel::smartPaginate(10, reverse: true);

        $this->assertSame(8, $paginator->currentPage());
        $this->assertSame(3, $paginator->displayPage());
    }

    public function test_bad_page_input_gives_the_page_without_a_number(): void
    {
        foreach (['abc', '-5', '0', '', '2.5', '1e1', ['x']] as $bad) {
            $this->app->instance('request', Request::create('/article', 'GET', ['page' => $bad]));

            $paginator = ArticleModel::smartPaginate(10, reverse: true);

            $this->assertSame(1, $paginator->currentPage(), 'page='.json_encode($bad));
            $this->assertSame(10, $paginator->displayPage(), 'page='.json_encode($bad));
        }
    }

    public function test_page_past_the_end_gives_an_empty_page(): void
    {
        $normal = ArticleModel::smartPaginate(10, page: 11);
        $this->assertSame(11, $normal->currentPage());
        $this->assertCount(0, $normal->items());

        $reverse = ArticleModel::smartPaginate(10, page: 11, reverse: true);
        $this->assertSame(11, $reverse->currentPage());
        $this->assertCount(0, $reverse->items());
    }

    public function test_empty_table(): void
    {
        ArticleModel::query()->delete();

        $paginator = ArticleModel::smartPaginate(10, reverse: true);

        $this->assertSame(1, $paginator->currentPage());
        $this->assertSame(0, $paginator->total());
        $this->assertFalse($paginator->hasPages());
    }

    public function test_reverse_by_default_from_config(): void
    {
        config(['smart-pagination.reverse_by_default' => true]);

        $paginator = ArticleModel::smartPaginate(10)->withPath('/article');

        $this->assertTrue($paginator->reverse);
        $this->assertSame('/article?page=9', $paginator->pageUrl(2));
    }

    public function test_appends_are_kept_in_every_url(): void
    {
        $paginator = ArticleModel::smartPaginate(10, reverse: true)
            ->withPath('/article', '/page-{page}')
            ->appends(['sort' => 'date']);

        $this->assertSame('/article?sort=date', $paginator->url(1));
        $this->assertSame('/article/page-9?sort=date', $paginator->url(2));
    }

    public function test_to_array_uses_display_numbers(): void
    {
        $array = ArticleModel::smartPaginate(10, reverse: true)->withPath('/article', '/page-{page}')->toArray();

        $this->assertSame('/article', $array['first_page_url']);
        $this->assertSame('/article/page-1', $array['last_page_url']);
        $this->assertSame('/article/page-9', $array['next_page_url']);
        $this->assertNull($array['prev_page_url']);
        $this->assertSame(10, $array['display_page']);
        $this->assertTrue($array['reverse']);

        $labels = array_column($array['links'], 'label');
        $this->assertSame(['&laquo; Previous', '10', '9', '8', '...', '2', '1', 'Next &raquo;'], $labels);
        $this->assertTrue($array['links'][1]['active']);
        $this->assertSame('/article/page-9', $array['links'][2]['url']);
    }

    public function test_links_render_display_numbers_and_rel_hints(): void
    {
        $html = ArticleModel::smartPaginate(10, page: 9, reverse: true)->withPath('/article', '/page-{page}')->links()->render();

        $this->assertStringContainsString('aria-current="page"><span class="page-link">9</span>', $html);
        $this->assertStringContainsString('href="/article" rel="prev"', $html);
        $this->assertStringContainsString('href="/article/page-8" rel="next"', $html);
        $this->assertStringContainsString('<a class="page-link" href="/article/page-1">1</a>', $html);
        $this->assertStringContainsString('aria-label="Pagination"', $html);
        $this->assertStringNotContainsString('$displayPage', $html);
    }

    public function test_links_accept_a_custom_view(): void
    {
        view()->addNamespace('t', __DIR__.'/../views');

        $html = ArticleModel::smartPaginate(10, reverse: true)->withPath('/article')->links('t::custom')->render();

        $this->assertSame('custom:1/10/10', trim($html));
    }

    public function test_on_each_side_and_edge_are_chainable(): void
    {
        $paginator = ArticleModel::smartPaginate(10, page: 5)->withPath('/article')->onEachSide(1)->onEachEdge(1);

        $labels = array_column($paginator->toArray()['links'], 'label');
        $this->assertSame(['&laquo; Previous', '1', '...', '4', '5', '6', '...', '10', 'Next &raquo;'], $labels);

        $html = $paginator->links()->render();
        $this->assertStringNotContainsString('>2</a>', $html);
        $this->assertStringContainsString('href="/article?page=4">4</a>', $html);
    }

    public function test_links_hide_prev_next_from_config(): void
    {
        config(['smart-pagination.show_prev_next' => false]);

        $html = ArticleModel::smartPaginate(10, page: 5)->withPath('/article')->links()->render();

        $this->assertStringNotContainsString('rel="next"', $html);
        $this->assertStringNotContainsString('rel="prev"', $html);
    }
}
