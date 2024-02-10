<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class ComponentTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEachBreak()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% include(\'/view/component.php\') %}</body></html>');
        $this->assertEquals('<html><head></head><body><p>Call From Component</p></body></html>', $out);
    }
}
