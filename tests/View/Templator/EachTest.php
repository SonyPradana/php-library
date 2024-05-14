<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class EachTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderEach()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbsers as $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals('<?php foreach ($numbsers as $number ): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderEachWithKeyValue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbsers as $key => $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals('<?php foreach ($numbsers as $key  => $number ): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>', $out);
    }
}
