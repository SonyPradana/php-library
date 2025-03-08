<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class BooleanTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderBoolean()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<input x-enable="{% bool(1 == 1) %}">');
        $this->assertEquals(
            '<input x-enable="<?= (1 == 1) ? \'true\' : \'false\' ?>">',
            $out
        );
    }
}
