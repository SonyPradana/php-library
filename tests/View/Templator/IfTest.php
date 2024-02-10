<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class IfTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderIf()
    {
        $templator = new Templator(__DIR__, __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{% if ($true === true) %} show {% endif %}</h1><h1>{% if ($true === false) %} show {% endif %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php if (($true === true) ): ?> show <?php endif; ?></h1><h1><?php if (($true === false) ): ?> show <?php endif; ?></h1></body></html>', $out);
    }
}
