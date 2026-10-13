<?php

namespace Wnikk\SmartPagination;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Wnikk\SmartPagination\Macros\PaginateMacro;
use Wnikk\SmartPagination\View\Components\SmartPagination;

class SmartPaginationServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/smart-pagination.php', 'smart-pagination');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/smart-pagination.php' => config_path('smart-pagination.php'),
        ], 'smart-pagination-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/smart-pagination'),
        ], 'smart-pagination-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/smart-pagination'),
        ], 'smart-pagination-lang');

        PaginateMacro::register();

        Blade::component('smart-pagination', SmartPagination::class);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'smart-pagination');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'smart-pagination');
    }
}
