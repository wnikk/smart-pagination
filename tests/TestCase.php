<?php

namespace Tests;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Optional: register your package's service provider
    protected function getPackageProviders($app)
    {
        return [
            \Wnikk\SmartPagination\SmartPaginationServiceProvider::class,
        ];
    }

    // Optional: set up environment
    protected function getEnvironmentSetUp($app)
    {
        // Example: $app['config']->set('smart-pagination.reverse_by_default', true);
    }
}
