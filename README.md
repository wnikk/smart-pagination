# Smart Pagination for Laravel

SmartPagination is a Laravel Blade component that simplifies pagination rendering and adds support
for reverse pagination (descending page numbers).
It’s designed to be flexible, SEO-friendly, and easy to customize.

Designed for blogs, news feeds, and any content that grows over time.


## Features
- Reverse page numbering: newest content on `?page=88`, oldest on `?page=1`
- Stable links: `?page=1` always points to the oldest page
- SEO-friendly canonical URLs
- Preserves all other query parameters
- Blade component with Bootstrap-ready markup

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
In your Blade view (SEO-links):
```blade
<x-smart-pagination :paginator="$posts" :reverse="true" />
```

To include canonical URL:
```blade
<link rel="canonical" href="{{ $component->canonicalUrl() }}">
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