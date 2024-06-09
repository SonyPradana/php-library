<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\ServiceProvider;

class RegisterProvidersTest extends TestCase
{
    public function testBootstrap(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2/');
        $app->register(TestRegisterServiceProvider::class);
        $app->bootstrapWith([BootProviders::class]);

        $this->assertCount(3, (fn () => $this->{'booted_providers'})->call($app), '1 from defult provider, 1 from this test, and 1 from vendor.');
    }
}

class TestRegisterServiceProvider extends ServiceProvider
{
}

class TestVendorServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }
}
