<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\Fixtures\ArticleModel;
use Tests\TestCase;

class BladeComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ArticleModel::factory()->count(100)->create();
    }

    public function test_component_tag_with_a_plain_paginator(): void
    {
        $posts = ArticleModel::paginate(10)->withPath('/posts');

        $html = Blade::render('<x-smart-pagination :paginator="$posts" :reverse="true" page-pattern="/page-{page}" />', compact('posts'));

        $this->assertStringContainsString('aria-current="page"><span class="page-link">10</span>', $html);
        $this->assertStringContainsString('href="/posts/page-9">9</a>', $html);
        $this->assertStringContainsString('href="/posts/page-1">1</a>', $html);
        $this->assertStringContainsString('href="/posts/page-9" rel="next"', $html);
    }

    public function test_component_tag_without_prev_next(): void
    {
        $posts = ArticleModel::paginate(10, page: 3)->withPath('/posts');

        $html = Blade::render('<x-smart-pagination :paginator="$posts" :show-prev-next="false" />', compact('posts'));

        $this->assertStringNotContainsString('rel=', $html);
        $this->assertStringContainsString('href="/posts?page=2">2</a>', $html);
    }

    public function test_nothing_is_rendered_for_one_page(): void
    {
        $posts = ArticleModel::paginate(100)->withPath('/posts');

        $html = Blade::render('<x-smart-pagination :paginator="$posts" />', compact('posts'));

        $this->assertSame('', trim($html));
    }
}
