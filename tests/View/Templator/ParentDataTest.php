<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class ParentDataTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderParenData()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>my name is {{ $__[\'full.name\'] }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>my name is <?php echo htmlspecialchars($__[\'full.name\'] ); ?> </h1></body></html>', $out);
    }
}
