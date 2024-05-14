<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class CommentTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEachBreak()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{# this a comment #}</body></html>');
        $this->assertEquals('<html><head></head><body><?php /* this a comment */ ?></body></html>', $out);
    }
}
