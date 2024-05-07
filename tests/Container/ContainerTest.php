<?php

declare(strict_types=1);

namespace System\Test\Container;

use PHPUnit\Framework\TestCase;
use System\Container\Container;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function itCanAlias()
    {
        $container = new Container();
        $container->alias('framework', 'fast');
        $container->set('framework', fn () => 'php-mvc');

        $this->assertEquals($container->get('framework'), $container->get('fast'));
        $this->assertEquals($container->make('framework'), $container->make('fast'));
        $this->assertTrue($container->has('fast'));
    }

    /**
     * @test
     */
    public function itCanThrowWhenAbstarctSameWithAlias()
    {
        $container = new Container();
        $container->set('framework', fn () => 'php-mvc');

        try {
            $container->alias('framework', 'framework');
        } catch (\Throwable $th) {
            $this->assertEquals('framework is aliased to itself.', $th->getMessage());
        }
    }
}
