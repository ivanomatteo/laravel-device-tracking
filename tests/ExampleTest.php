<?php

namespace IvanoMatteo\LaravelDeviceTracking\Tests;

use Orchestra\Testbench\TestCase;
use IvanoMatteo\LaravelDeviceTracking\LaravelDeviceTrackingServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelDeviceTrackingServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
