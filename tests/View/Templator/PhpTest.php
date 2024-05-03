<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class PhpTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderParenData()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% php %} echo \'taylor\'; {% endphp %}</body></html>');
        $this->assertEquals('<html><head></head><body><?php  echo \'taylor\';  ?></body></html>', $out);
    }
}
