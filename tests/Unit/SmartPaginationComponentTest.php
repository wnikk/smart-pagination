<?php

namespace Tests\Unit;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Tests\TestCase;
use Wnikk\SmartPagination\View\Components\SmartPagination;

class SmartPaginationComponentTest extends TestCase
{
    protected function paginator(int $currentPage, int $total = 100, int $perPage = 10): LengthAwarePaginator
    {
        $items = Collection::times($total, fn ($i) => "Item $i");

        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            ['path' => '/posts', 'pageName' => 'page']
        );
    }

    public function test_standard_urls(): void
    {
        $component = new SmartPagination($this->paginator(1), false);

        $this->assertSame('/posts', $component->pageUrl(1));
        $this->assertSame('/posts?page=2', $component->pageUrl(2));
        $this->assertSame(1, $component->displayPage);
    }

    public function test_reverse_urls_count_from_the_end(): void
    {
        $component = new SmartPagination($this->paginator(1), true);

        $this->assertSame('/posts', $component->pageUrl(1));
        $this->assertSame('/posts?page=9', $component->pageUrl(2));
        $this->assertSame('/posts?page=1', $component->pageUrl(10));
        $this->assertSame(10, $component->displayPage);
    }

    public function test_reverse_labels_are_display_pages(): void
    {
        $component = new SmartPagination($this->paginator(2), true);

        $this->assertSame(9, $component->displayPage);
        $this->assertSame(10, $component->pageLabel(1));
        $this->assertSame(1, $component->pageLabel(10));
    }

    public function test_path_patterns(): void
    {
        $this->assertSame('/posts-2p', (new SmartPagination($this->paginator(1), false, '-{page}p'))->pageUrl(2));
        $this->assertSame('/posts-p2.html', (new SmartPagination($this->paginator(1), false, '-p{page}.html'))->pageUrl(2));
        $this->assertSame('/posts/page-2', (new SmartPagination($this->paginator(1), false, '/page-{page}'))->pageUrl(2));
        $this->assertSame('/posts', (new SmartPagination($this->paginator(1), false, '/page-{page}'))->pageUrl(1));
    }

    public function test_query_pattern(): void
    {
        $component = new SmartPagination($this->paginator(1), false, '?p={page}');

        $this->assertSame('/posts', $component->pageUrl(1));
        $this->assertSame('/posts?p=2', $component->pageUrl(2));
    }

    public function test_reverse_with_pattern(): void
    {
        $component = new SmartPagination($this->paginator(1), true, '/page-{page}');

        $this->assertSame('/posts', $component->pageUrl(1));
        $this->assertSame('/posts/page-9', $component->pageUrl(2));
        $this->assertSame('/posts/page-1', $component->pageUrl(10));
    }

    public function test_query_string_and_fragment_are_kept(): void
    {
        $paginator = $this->paginator(1)->appends(['sort' => 'date', 'tag' => 'php'])->fragment('list');

        $plain = new SmartPagination($paginator, false);
        $this->assertSame('/posts?sort=date&tag=php#list', $plain->pageUrl(1));
        $this->assertSame('/posts?sort=date&tag=php&page=3#list', $plain->pageUrl(3));

        $path = new SmartPagination($paginator, false, '/page-{page}');
        $this->assertSame('/posts/page-3?sort=date&tag=php#list', $path->pageUrl(3));

        $query = new SmartPagination($paginator, false, '?p={page}');
        $this->assertSame('/posts?sort=date&tag=php&p=3#list', $query->pageUrl(3));
    }

    public function test_invalid_pattern_is_refused_at_once(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SmartPagination($this->paginator(1), false, 'no-placeholder');
    }

    public function test_pattern_comes_from_config_when_not_given(): void
    {
        config(['smart-pagination.page_pattern' => '/p{page}']);

        $component = new SmartPagination($this->paginator(1), false);

        $this->assertSame('/posts/p2', $component->pageUrl(2));
    }

    public function test_reverse_comes_from_config_when_not_given(): void
    {
        config(['smart-pagination.reverse_by_default' => true]);

        $component = new SmartPagination($this->paginator(1));

        $this->assertTrue($component->reverse);
        $this->assertSame('/posts?page=9', $component->pageUrl(2));
    }

    public function test_window_on_first_page(): void
    {
        $component = new SmartPagination($this->paginator(1), false);

        $this->assertSame([1, 2, 3, 'dots', 9, 10], $this->shape($component->pages()));
        $this->assertTrue($component->pages()[0]['active']);
    }

    public function test_window_in_the_middle(): void
    {
        $component = new SmartPagination($this->paginator(6), false);

        $this->assertSame([1, 2, 'dots', 4, 5, 6, 7, 8, 9, 10], $this->shape($component->pages()));
    }

    public function test_window_has_no_dots_for_small_gaps(): void
    {
        $component = new SmartPagination($this->paginator(5), false);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 'dots', 9, 10], $this->shape($component->pages()));
    }

    public function test_window_in_reverse_shows_display_labels(): void
    {
        $component = new SmartPagination($this->paginator(1), true);

        $this->assertSame([10, 9, 8, 'dots', 2, 1], $this->shape($component->pages()));
        $this->assertSame('/posts?page=9', $component->pages()[1]['url']);
    }

    public function test_window_for_three_pages(): void
    {
        $component = new SmartPagination($this->paginator(2, 30), false);

        $this->assertSame([1, 2, 3], $this->shape($component->pages()));
    }

    public function test_window_past_the_end_centres_on_the_last_page(): void
    {
        $component = new SmartPagination($this->paginator(11), true);

        $this->assertSame([10, 9, 'dots', 3, 2, 1], $this->shape($component->pages()));
        $this->assertSame([], array_filter($component->pages(), fn ($p) => $p['active'] ?? false));
    }

    public function test_window_size_from_arguments(): void
    {
        $component = new SmartPagination($this->paginator(5), false, onEachSide: 1, onEachEdge: 1);
        $this->assertSame([1, 'dots', 4, 5, 6, 'dots', 10], $this->shape($component->pages()));

        $component = new SmartPagination($this->paginator(5), false, onEachSide: 1, onEachEdge: 0);
        $this->assertSame([4, 5, 6], $this->shape($component->pages()));

        $component = new SmartPagination($this->paginator(1), true, onEachSide: 0, onEachEdge: 0);
        $this->assertSame([10], $this->shape($component->pages()));
    }

    public function test_window_size_from_config(): void
    {
        config(['smart-pagination.on_each_side' => 3, 'smart-pagination.on_each_edge' => 1]);

        $component = new SmartPagination($this->paginator(6), false);

        $this->assertSame([1, 'dots', 3, 4, 5, 6, 7, 8, 9, 10], $this->shape($component->pages()));
    }

    public function test_negative_window_size_is_treated_as_zero(): void
    {
        $component = new SmartPagination($this->paginator(5), false, onEachSide: -3, onEachEdge: -1);

        $this->assertSame([5], $this->shape($component->pages()));
    }

    /** @return list<int|string> */
    protected function shape(array $pages): array
    {
        return array_map(fn ($p) => $p['type'] === 'dots' ? 'dots' : $p['label'], $pages);
    }
}
