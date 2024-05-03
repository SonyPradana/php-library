<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class EachContinueTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEachContinue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbsers as $number %}{% continue %}{% endforeach %}');
        $this->assertEquals('<?php foreach ($numbsers as $number ): ?><?php continue ; ?><?php endforeach; ?>', $out);
    }
}
