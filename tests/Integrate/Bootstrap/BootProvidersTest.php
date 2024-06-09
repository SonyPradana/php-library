<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

class BootProvidersTest extends TestCase
{
    public function testBootstrap(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2/');

        $this->assertFalse($app->isBooted());
        $app->bootstrapWith([BootProviders::class]);
        $this->assertTrue($app->isBooted());
    }
}
