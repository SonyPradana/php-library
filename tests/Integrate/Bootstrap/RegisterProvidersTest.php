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
        $this->assertCount(2, (fn () => $this->{'looded_providers'})->call($app), '1 from defult provider, 1 from this test.');
    }
}

class TestRegisterServiceProvider extends ServiceProvider
{
}
