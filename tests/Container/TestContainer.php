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

    public function callProtected(string $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($this->container);
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->container, $args);
    }

    public function getProtectedProperty(string $propertyName)
    {
        $reflection = new \ReflectionClass($this->container);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($this->container);
    }
}
