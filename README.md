# Smart Pagination for Laravel

SmartPagination is a Laravel Blade component that simplifies pagination rendering and adds support
for reverse pagination (descending page numbers).
It’s designed to be flexible, SEO-friendly, and easy to customize.

Designed for blogs, news feeds, and any content that grows over time.

## Features
- Stable URLs — older content stays on the same page even as new items are added
- SEO-Friendly — search engines retain indexing without shifting links
- User-Centric — newest content always appears on the first page
- Blade component - easy to use in Blade templates with Bootstrap-ready markup
- Flexible Routing - supports custom URL patterns via `page-pattern`
- Preserves all other query parameters
- Reverse pagination support - show newest content first

---

## 🌐 SEO-Friendly Pagination with Custom URL Patterns

SmartPagination allows you to generate clean, customizable,
and SEO-optimized pagination URLs for your applications.
Instead of relying on default query parameters like `?page=2`,
you can define your own URL patterns such as `/blog/page-2`, `/news-p3.html`, or even `/archive-4y.html`.

### ✅ Why It Matters for SEO

Search engines prefer stable, predictable URLs. When paginated content (like blog posts, product listings, or news articles) grows over time, traditional pagination can cause older content to shift across pages — which may lead to:

- Duplicate indexing
- Loss of link equity
- Lower rankings for older content

With **reverse pagination** and **custom URL patterns**, you can ensure that:

- The first page always shows the newest content
- Older content remains accessible at the same URL
- Search engines consistently index the same pages over time

This improves crawl efficiency and preserves ranking signals for evergreen content.

### 🔧 Examples of Custom Patterns

You can define your own `pagePattern` using `{page}` as a placeholder:

| Pattern      | Resulting URL (Page 2) |
|--------------|------------------------|
| `page-{page}` | `/blog/page-2`         |
| `news-p{page}.html` | `/news/news-p2.html`   |
| `archive-{page}y` | `/archive?p=2`         |
| `products`   | `/products/1`          |

## 🔁 Reverse Pagination: Keep Your URLs Stable as Content Grows

Reverse pagination is a powerful feature that ensures your **newest content always appears on the first page**, while older content stays anchored to its original URLs. This is especially useful for blogs, news feeds, changelogs, or any time-sensitive content.

With **reverse pagination**, the first page always shows the latest items, and older pages remain unchanged — making your site more SEO-friendly.

### 🔧 Examples of Reverse Pagination

You have **100 articles**, sorted by newest first (`DESC`), showing **10 per page**.

#### 🔴 With Reverse Pagination Disabled

| Display Page | Real Page | Articles Shown |
|--------------|-----------|----------------|
| `/news` (page 1) | page 1 | 91–100 (newest) |
| `/news/page-2` | page 2 | 81–90 |
| ... | ... | ... |
| `/news/page-10` | page 10 | 1–10 (oldest) |

Tomorrow you publish 10 more articles (total: 110):

| Display Page | Real Page | Articles Shown |
|--------------|-----------|----------------|
| `/news` (page 1) | page 1 | 101–110 (newest) |
| `/news/page-2` | page 2 | 91–100 |
| ... | ... | ... |
| `/news/page-11` | page 11 | 1–10 (still oldest) |

#### ✅ With Reverse Pagination Enabled

| Display Page      | Real Page | Articles Shown                 |
|-------------------|-----------|--------------------------------|
| `/news` (page 10) | page 1 | 91–100 (newest)                |
| `/news/page-9`    | page 2 | 81–90         always on page-9 |
| ...               | ... | ...                            |
| `/news/page-1`    | page 10 | 1–10 (oldest) always on page-1 |

#### Result:
- New content appears on the first page
- Older content stays at the same URL
- Search engines retain stable indexing
- No shifting of articles across pages

---

## Installation
Install via Composer:
```bash
composer require wnikk/smart-pagination
```


## Usage
In your controller:
```php
$posts = \App\Models\Post::orderByDesc('created_at')->paginate(10);
```
In your Blade view (by default):
```blade
<x-smart-pagination :paginator="$posts" />
```
In your Blade view (SEO-links + SEO-friendly):
```blade
<x-smart-pagination 
    :paginator="$news" 
    :reverse="true"
    page-pattern="news-p{page}.html" 
/>
```

## Publish Configuration (optional)
Publish Configuration
```bash
php artisan vendor:publish --tag=smart-pagination-config
```
Publish Blade Views
```bash
php artisan vendor:publish --tag=smart-pagination-views
```


## License
MIT