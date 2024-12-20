<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class SlotTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderSlotScope()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% slot(\'slot.template\') %}<main>core component</main>{% endslot %}');
        $this->assertEquals('<html><head></head><body><main>core component</main></body></html>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderSlotScopeMultyple()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% slot(\'slotcard.template\') %}oke{% endslot %} {% slot(\'slotcard.template\') %}oke 2 {% endslot %}');
        $this->assertEquals("<div>oke</div>\n <div>oke 2 </div>", trim($out));
    }

    /**
     * @test
     */
    public function itThrowWhenExtendNotFound()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates('{% slot(\'notexits.template\') %}<main>core component</main>{% endslot %}');
        } catch (\Throwable $th) {
            $this->assertEquals('View path not exists `Template file not found: notexits.template`', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itThrowWhenExtendNotFoundyield()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates('{% slot(\'slotyield.template\') %}<main>core component</main>{% endslot %}');
        } catch (\Throwable $th) {
            $this->assertEquals('yield section not found: slot2.template', $th->getMessage());
        }
    }
}
