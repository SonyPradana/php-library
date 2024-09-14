<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Exceptions\DirectiveCanNotBeRegister;
use System\View\Exceptions\DirectiveNotRegister;
use System\View\Templator;
use System\View\Templator\DirectiveTemplator;
use System\View\TemplatorFinder;

final class DirectiveTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEachBreak()
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% sum(1, 2) %}</body></html>');
        $this->assertEquals("<html><head></head><body><?php echo System\View\Templator\DirectiveTemplator::call('sum', 1, 2); ?></body></html>", $out);
    }

    /**
     * @test
     */
    public function itThowExcaptionDueDirectiveNotRegister()
    {
        $this->expectException(DirectiveNotRegister::class);
        DirectiveTemplator::call('unknow', 0);
    }

    public function itCanNotRegisterDirective(): void
    {
        $this->expectException(DirectiveCanNotBeRegister::class);
        DirectiveTemplator::register('include', fn ($file): string => $file);
    }

    public function itCanRegisterAndCallDirective(): void
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $this->assertEquals(2, DirectiveTemplator::call('sum', 1, 1));
    }
}
