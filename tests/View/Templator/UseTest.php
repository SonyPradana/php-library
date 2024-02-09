<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\Text\Str;
use System\View\Templator;

final class UseTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderUse()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
    }

    /**
     * @test
     */
    public function itCanRenderUseMultyTime()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}{% use ('Test\Test as Test2') %}</html>");
        $match     = Str::contains($out, 'use Test\Test');
        $this->assertTrue($match);
        $match     = Str::contains($out, 'use Test\Test as Test2');
        $this->assertTrue($match);
    }
}
