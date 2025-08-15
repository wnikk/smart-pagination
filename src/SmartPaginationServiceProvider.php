<?php
namespace Wnikk\SmartPagination;

use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/../config/smart-pagination.php' => config_path('smart-pagination.php'),
        ], 'smart-pagination-config');

        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'smart-pagination');

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/smart-pagination'),
        ], 'smart-pagination-views');
    }
}
