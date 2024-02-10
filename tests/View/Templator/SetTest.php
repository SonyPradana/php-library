<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class SetTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderSetString()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('{% set $foo=\'bar\' %}');
        $this->assertEquals('<?php $foo = \'bar\'; ?>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderSetInt()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('{% set $bar=123 %}');
        $this->assertEquals('<?php $bar = 123; ?>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderSetArray()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('{% set $arr=[12, \'34\'] %}');
        $this->assertEquals('<?php $arr = [12, \'34\']; ?>', $out);
    }
}
