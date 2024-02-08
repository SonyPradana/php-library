<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\Text\Str;
use System\View\Templator;

final class NamespaceTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderNamespace()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}</html>");
        $match     = Str::contains($out, 'namespace Test\Test');
        $this->assertTrue($match);
    }

    /**
     * @test
     */
    public function itCanRenderNamespaceMultyTime()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates("'<html>{% use ('Test\Test') %}{% use ('Test\Test as Test2') %}</html>");
        $match     = Str::contains($out, 'namespace Test\Test');
        $this->assertTrue($match);
        $match     = Str::contains($out, 'namespace Test\Test as Test2');
        $this->assertTrue($match);
    }
}
