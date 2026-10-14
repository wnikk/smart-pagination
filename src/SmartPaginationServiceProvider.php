<?php

namespace Wnikk\SmartPagination;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
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

        // Registered when the compiler is first used, not at boot: the Blade facade would build the
        // compiler right away, and it throws when storage/framework/views is missing. The application
        // must still boot in that case, and report the missing directory on the first rendered view.
        $this->callAfterResolving('blade.compiler', static function (BladeCompiler $blade): void {
            $blade->component(SmartPagination::class, 'smart-pagination');
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'smart-pagination');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'smart-pagination');
    }
}
