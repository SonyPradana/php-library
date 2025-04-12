<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class NamingTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderNaming()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name }}, ages {{ $age }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name ); ?>, ages <?php echo htmlspecialchars($age ); ?> </h1></body></html>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderNamingWithoutEscape()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {!! $name !!}, ages {!! $age !!} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo $name ; ?>, ages <?php echo $age ; ?> </h1></body></html>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderNamingWithCallFunction()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>time: }{{ now()->timestamp }}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>time: }<?php echo htmlspecialchars(now()->timestamp ); ?></h1></body></html>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderNamingTernary()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>your {{ $name ?? \'nuno\' }}, ages {{ $age ? 17 : 28 }} </h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1>your <?php echo htmlspecialchars($name ?? \'nuno\' ); ?>, ages <?php echo htmlspecialchars($age ? 17 : 28 ); ?> </h1></body></html>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderNamingSkip()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{{ $render }}, {% raw %}your {{ name }}, ages {{ age }}{% endraw %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php echo htmlspecialchars($render ); ?>, your {{ name }}, ages {{ age }}</h1></body></html>', $out);
    }
}
