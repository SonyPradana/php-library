<?php

declare(strict_types=1);

namespace System\Test\Integrate\Testing;

use System\Integrate\Application;
use System\Integrate\Http\Karnel;
use System\Integrate\Testing\TestCase;

final class TestCaseTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '\Bootstrap\RegisterProvidersTest.php';
        $this->app = new Application(dirname(__DIR__) . '/assets/app2');
        $this->app->set(Karnel::class, fn () => new Karnel($this->app));

        parent::setUp();
    }

    public function testTestRunSmoothly(): void
    {
        $this->assertTrue(true);
    }
}
