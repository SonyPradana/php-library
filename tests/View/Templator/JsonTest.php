<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class JsonTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderJson()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% json($data) %}</body></html>');
        $this->assertEquals(
            '<html><head></head><body><?php echo json_encode($data, 0 | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR, 512); ?></body></html>',
            $out
        );
    }

    /**
     * @test
     */
    public function itCanRenderJsonWithOptionalParam()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% json($data, 1, 500) %}</body></html>');
        $this->assertEquals(
            '<html><head></head><body><?php echo json_encode($data, 1 | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR, 500); ?></body></html>',
            $out
        );
    }
}
