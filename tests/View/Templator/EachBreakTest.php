<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class EachBreakTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEachBreak()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% foreach $numbsers as $number %}{% break %}{% endforeach %}</body></html>');
        $this->assertEquals('<html><head></head><body><?php foreach ($numbsers as $number ): ?><?php break ; ?><?php endforeach; ?></body></html>', $out);
    }
}
