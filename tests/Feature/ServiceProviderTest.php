<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;
use Wnikk\SmartPagination\View\Components\SmartPagination;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertFalse(config('smart-pagination.reverse_by_default'));
        $this->assertTrue(config('smart-pagination.show_prev_next'));
        $this->assertNull(config('smart-pagination.page_pattern'));
    }

    public function test_publish_sources_exist(): void
    {
        foreach (['smart-pagination-config', 'smart-pagination-views', 'smart-pagination-lang'] as $tag) {
            $paths = ServiceProvider::pathsToPublish(null, $tag);

            $this->assertNotEmpty($paths, $tag);
            foreach (array_keys($paths) as $source) {
                $this->assertFileExists($source, $tag);
            }
        }
    }

    public function test_boot_does_not_resolve_the_blade_compiler(): void
    {
        $this->assertFalse($this->app->resolved('blade.compiler'));
    }

    public function test_component_alias_is_registered_once_the_compiler_is_resolved(): void
    {
        $this->assertSame(
            SmartPagination::class,
            Blade::getClassComponentAliases()['smart-pagination'] ?? null
        );
    }

    public function test_translations_are_loaded(): void
    {
        $this->assertSame('Previous', __('smart-pagination::pagination.previous-label'));
    }
}
