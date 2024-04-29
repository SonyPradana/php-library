<?php

declare(strict_types=1);

namespace System\Test\Container;

use PHPUnit\Framework\TestCase;
use System\Container\Container;

class ContainerArrayAccessTest extends TestCase
{
    /**
     * @test
     */
    public function itCanGetHas()
    {
        $container = new Container();
        $container->set('test01', 1);

        $this->assertTrue(isset($container['test01']));
    }

    /**
     * @test
     */
    public function itCanGet()
    {
        $container = new Container();
        $container->set('test01', 1);

        $this->assertEquals(1, $container['test01']);
    }

    /**
     * @test
     */
    public function itCanSet()
    {
        $container           = new Container();
        $container['test01'] = 1;

        $this->assertEquals(1, $container->get('test01'));
    }

    /**
     * @test
     */
    public function itCanUnset()
    {
        $container = new Container();
        $container->set('test01', 1);
        unset($container['test01']);

        $this->assertFalse($container->has('test01'));
    }
}
