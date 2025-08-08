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
        $out       = $templator->templates('{% foreach $numbers as $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals(
            '<?php foreach ($numbers as $number): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>',
            $out
        );
    }

    /**
     * @test
     */
    public function itCanRenderEachWithKeyValue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbers as $key => $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals(
            '<?php foreach ($numbers as $key => $number): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>',
            $out
        );
    }

    /**
     * @test
     */
    public function itCanRenderNestedEach()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $categories as $category %}{{ $category->name }}{% foreach $category->items as $item %}{{ $item->name }}{% endforeach %}{% endforeach %}';
        $expected  = '<?php foreach ($categories as $category): ?><?php echo htmlspecialchars($category->name ); ?><?php foreach ($category->items as $item): ?><?php echo htmlspecialchars($item->name ); ?><?php endforeach; ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }

    /**
     * @test
     */
    public function itCanRenderNestedEachWithKeyValue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $data as $key => $values %}{{ $key }}{% foreach $values as $index => $item %}{{ $index }}: {{ $item }}{% endforeach %}{% endforeach %}';
        $expected  = '<?php foreach ($data as $key => $values): ?><?php echo htmlspecialchars($key ); ?><?php foreach ($values as $index => $item): ?><?php echo htmlspecialchars($index ); ?>: <?php echo htmlspecialchars($item ); ?><?php endforeach; ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }

    /**
     * @test
     */
    public function itCanRenderMultipleForeachBlocks()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $users as $user %}{{ $user->name }}{% endforeach %}{% foreach $products as $product %}{{ $product->name }}{% endforeach %}';
        $expected  = '<?php foreach ($users as $user): ?><?php echo htmlspecialchars($user->name ); ?><?php endforeach; ?><?php foreach ($products as $product): ?><?php echo htmlspecialchars($product->name ); ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }
}
