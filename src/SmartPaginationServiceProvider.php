<?php
namespace Wnikk\SmartPagination;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Wnikk\SmartPagination\View\Components\SmartPagination;
use Wnikk\SmartPagination\Macros\PaginateMacro;

class SmartPaginationServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/smart-pagination.php', 'smart-pagination');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/messages.php' => config_path('smart-pagination.php'),
        ], 'smart-pagination-config');

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/smart-pagination'),
        ], 'smart-pagination-views');

        // Register the SmartPaginator macro
        PaginateMacro::register();

        // Register the SmartPagination Blade component
        Blade::component('smart-pagination', SmartPagination::class);

        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'smart-pagination');

        // Load translations from package
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang');
    }
}
