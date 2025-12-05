<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Dummys\DummyClass;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::alias
 */
class AliasTest extends TestCase
{
    /** @test
     * @testdox Alias creates alternate name for abstract
     *
     * @covers \Container::alias
     * */
    public function aliasBasic(): void
    {
        $container = $this->container;
        $container->bind(self::class . 'Foo', fn () => 'foo');
        $container->alias(self::class . 'Foo', 'foo-alias');

        $this->assertEquals('foo', $container->get('foo-alias'));
    }

    /** @test
     * @testdox Alias resolves recursively
     *
     * @covers \Container::alias
     * */
    public function aliasRecursiveResolution(): void
    {
        $container = $this->container;
        $container->bind('foo', fn () => 'bar');
        $container->alias('foo', 'alias1');
        $container->alias('alias1', 'alias2');

        $this->assertEquals('bar', $container->get('alias2'));
    }

    /** @test
     * @testdox Alias shadowing previous alias works
     *
     * @covers \Container::alias
     * */
    public function aliasShadow(): void
    {
        $container = $this->container;
        $container->bind('foo', fn () => 'foo-instance');
        $container->bind('bar', fn () => 'bar-instance');
        $container->alias('foo', 'shadow');
        $container->alias('bar', 'shadow');

        $this->assertEquals('bar-instance', $container->get('shadow'));
    }

    /** @test
     * @testdox Alias returns original via getAlias()
     *
     * @covers \Container::getAlias
     * */
    public function aliasGetAlias(): void
    {
        $container = $this->container;
        $container->alias('foo', 'bar');

        $this->assertEquals('foo', $container->getAlias('bar'));
    }

    /** @test
     * @testdox Alias used in bind resolves correctly
     *
     * @covers \Container::alias
     * */
    public function aliasUsedInBind(): void
    {
        $container = $this->container;
        $container->alias('foo', 'bar');
        $container->bind('bar', fn () => 'baz');

        $this->assertEquals('baz', $container->get('foo'));
    }

    /** @test
     * @testdox Alias chain loops should not infinite loop
     *
     * @covers \Container::alias
     * */
    public function aliasPreventsLoop(): void
    {
        $this->expectException(\Exception::class);

        $container = $this->container;
        $container->alias('foo', 'bar');
        $container->alias('bar', 'foo');

        $container->get('foo');
    }

    /** @test
     * @testdox Alias and shared binding return same instance
     *
     * @covers \Container::alias
     * */
    public function aliasSharedBinding(): void
    {
        $this->container->bind(DummyClass::class, null, true); // Bind as shared (singleton)
        $this->container->alias(DummyClass::class, 'dummy_alias');

        $instance1 = $this->container->get(DummyClass::class);
        $instance2 = $this->container->get('dummy_alias');

        $this->assertSame($instance1, $instance2);
    }
}
