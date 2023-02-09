<?php

namespace Jaulz\Podium\Tests;

use Jaulz\Podium\PodiumServiceProvider;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PodiumServiceProvider::class,
            PostgresqlEnhancedServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {
    }
}