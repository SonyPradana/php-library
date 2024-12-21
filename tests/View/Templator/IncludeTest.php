<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class IncludeTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderInclude()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% include(\'/view/component.php\') %}</body></html>');
        $this->assertEquals('<html><head></head><body><p>Call From Component</p></body></html>', $out);
    }
}
