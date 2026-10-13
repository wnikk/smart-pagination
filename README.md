# Smart Pagination for Laravel

Reverse pagination for Laravel with SEO-friendly links and stable URLs.

Newest content is on the first page, old pages keep their URL forever.
Built for blogs, news feeds, changelogs and any list that grows over time.

[![Tests](https://github.com/wnikk/smart-pagination/actions/workflows/tests.yml/badge.svg)](https://github.com/wnikk/smart-pagination/actions/workflows/tests.yml)

## Features

- **Stable URLs**: older content stays on the same page as new items arrive.
- **SEO-friendly**: custom URL patterns such as `/blog/page-2` or `/news-p3.html`.
- **Reverse numbering**: the newest page has no number, the oldest page is page 1.
- **One source of URLs**: `links()`, `url()`, `nextPageUrl()`, `toArray()` and JSON all agree.
- **Blade component** with Bootstrap-ready markup, `rel="prev"` / `rel="next"`, query string kept.
- **Works with any paginator**: use the component with the result of `paginate()` too.

## Requirements

- PHP 8.4+
- Laravel 13

For Laravel 11 and 12 use version `^1.18`.

## Installation

```bash
composer require wnikk/smart-pagination
```

## 🌐 SEO-Friendly Pagination with Custom URL Patterns

SmartPagination lets you generate clean, customizable and SEO-optimized pagination URLs.
Instead of relying on default query parameters like `?page=2`, you define your own URL
patterns such as `/blog/page-2`, `/news-p3.html` or even `/archive-4y`.

### ✅ Why It Matters for SEO

Search engines prefer stable, predictable URLs. When paginated content (blog posts, product
listings, news articles) grows over time, traditional pagination makes older content shift
across pages. This leads to:

- Duplicate indexing
- Loss of link equity
- Lower rankings for older content

With **reverse pagination** and **custom URL patterns** you get:

- The first page always shows the newest content
- Older content stays reachable at the same URL
- Search engines index the same pages over time

This improves crawl efficiency and preserves ranking signals for evergreen content.

### 🔧 Examples of Custom Patterns

Use `{page}` as a placeholder in your pattern:

| Pattern | Resulting URL (page 2) |
|---|---|
| `/page-{page}` | `/blog/page-2` |
| `-p{page}.html` | `/news-p2.html` |
| `/archive-{page}y` | `/archive-2y` |
| `?p={page}` | `/blog?p=2` |

## 🔁 Reverse Pagination: Keep Your URLs Stable as Content Grows

Reverse pagination makes sure that the **newest content always appears on the first page**,
while older content stays anchored to its original URL. This is what blogs, news feeds,
changelogs and any time-sensitive content need.

### 🔧 Example

You have **100 articles**, sorted newest first, **10 per page**.

#### 🔴 With reverse pagination disabled

| URL | Real page | Articles shown |
|---|---|---|
| `/news` | 1 | 91–100 (newest) |
| `/news/page-2` | 2 | 81–90 |
| ... | ... | ... |
| `/news/page-10` | 10 | 1–10 (oldest) |

Tomorrow you publish 10 more articles (total 110):

| URL | Real page | Articles shown |
|---|---|---|
| `/news` | 1 | 101–110 (newest) |
| `/news/page-2` | 2 | 91–100 (moved from page 1) |
| ... | ... | ... |
| `/news/page-11` | 11 | 1–10 (moved from page 10) |

Every numbered page now shows different articles. Links from other sites and search
engine indexes point at the wrong content.

#### ✅ With reverse pagination enabled

| URL | Real page | Articles shown |
|---|---|---|
| `/news` | 1 | 91–100 (newest) |
| `/news/page-9` | 2 | 81–90, always on page 9 |
| ... | ... | ... |
| `/news/page-1` | 10 | 1–10 (oldest), always on page 1 |

After the 10 new articles:

| URL | Real page | Articles shown |
|---|---|---|
| `/news` | 1 | 101–110 (newest) |
| `/news/page-10` | 2 | 91–100, a new page |
| `/news/page-9` | 3 | 81–90, same as before |
| ... | ... | ... |
| `/news/page-1` | 11 | 1–10, same as before |

#### Result

- New content appears on the first page
- Older content stays at the same URL
- Search engines keep a stable index
- No shifting of articles across pages

## Usage

Controller:

```php
$posts = Post::orderByDesc('created_at')->smartPaginate(10, reverse: true);
$posts->withPath('/post', '/page-{page}.html');
```

Blade:

```blade
{{ $posts->links() }}
```

That is all. The macro reads `?page=` from the request, loads the right records and
builds the links with the pattern.

### The `smartPaginate` macro

```php
smartPaginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?bool $reverse = null)
```

- `$page` is the number the visitor asked for. `null` reads it from the request.
- `$reverse` `null` takes `reverse_by_default` from the config.
- A page past the end returns an empty page, like `paginate()`.
- Bad input (`?page=abc`, `?page=0`) returns the page without a number.

### URL patterns

A pattern must contain `{page}`. Set it with `withPath()` or `withPagePattern()`:

```php
$posts->withPath('/news', '/page-{page}');   // /news/page-2
$posts->withPagePattern('-p{page}.html');    // /news-p2.html
$posts->withPagePattern('?p={page}');        // /news?p=2
```

Without a pattern the links use `?page=2`. Real page 1 is always the bare path: `/news`.

A default pattern can go into the config key `page_pattern`.

Register a route that matches the pattern, for example:

```php
Route::get('/news/page-{page}', [NewsController::class, 'index'])->whereNumber('page');
Route::get('/news', [NewsController::class, 'index']);
```

and pass the page to the macro: `smartPaginate(10, page: $page, reverse: true)`.

### Size of the page window

By default the links show two pages at each edge and two pages on each side of the current one,
with dots in the gaps: `1 2 ... 4 5 6 7 8 9 10`. Change it per paginator:

```php
$posts->onEachSide(1)->onEachEdge(1);   // 1 ... 4 5 6 ... 10
$posts->onEachSide(1)->onEachEdge(0);   // 4 5 6
```

or on the component with `:on-each-side="1"` and `:on-each-edge="1"`,
or for the whole app with the config keys `on_each_side` and `on_each_edge`.

### Query string and fragment

`appends()` and `fragment()` work as usual and are kept in every link:

```php
$posts->appends(['sort' => 'date'])->fragment('list');
// /news/page-2?sort=date#list
```

### The Blade component

Use the component directly, with `smartPaginate()` or with a plain `paginate()` result:

```blade
<x-smart-pagination
    :paginator="$posts"
    :reverse="true"
    page-pattern="/page-{page}"
    :show-prev-next="false"
    :on-each-side="2"
    :on-each-edge="2"
/>
```

Or pass options to `links()`:

```blade
{{ $posts->links(['showPrevNext' => false]) }}
{{ $posts->links('my.pagination') }}
```

The view gets `$paginator`, `$realPage`, `$displayPage`, `$totalPages`, `$reverse`,
`$showPrevNext`, `$onEachSide`, `$onEachEdge`, `$pageUrl($realPage)`, `$pageLabel($realPage)` and `$pages()`.

`$pages()` returns the window to render: the edge pages, the pages around the current one,
and `dots` entries for the gaps. Each page entry has `real`, `label`, `url`
and `active`.

### JSON and APIs

`toArray()` and `jsonSerialize()` use the same URLs and labels as the HTML.
Two extra keys are added: `display_page` and `reverse`.

## Configuration

```bash
php artisan vendor:publish --tag=smart-pagination-config
php artisan vendor:publish --tag=smart-pagination-views
php artisan vendor:publish --tag=smart-pagination-lang
```

```php
return [
    'reverse_by_default' => false,
    'page_pattern' => null,      // for example '/page-{page}'
    'on_each_side' => 2,
    'on_each_edge' => 2,
    'show_prev_next' => true,
];
```

Translation keys live under `smart-pagination::pagination.*`
(`label`, `previous`, `next`, `previous-label`, `next-label`, `dots`).

## Upgrading from 1.x

- PHP 8.4 and Laravel 13 are required.
- `url()` and the JSON output now follow reverse numbering and the page pattern.
  Code that expected `?page=N` with real page numbers in reverse mode must use `currentPage()` for the real page.
- The component constructor no longer accepts an array.
- Translation keys moved from `smart-pagination.*` to `smart-pagination::pagination.*`.

## License

MIT
