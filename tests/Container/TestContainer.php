<?php

declare(strict_types=1);

namespace System\Test\Container;

use PHPUnit\Framework\TestCase;
use System\Container\Container;

abstract class TestContainer extends TestCase
{
    protected ?Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function tearDown(): void
    {
        $this->container = null;
    }
}
