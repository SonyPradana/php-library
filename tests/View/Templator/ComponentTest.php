<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class ComponentTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderComponentScope()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% component(\'component.template\') %}<main>core component</main>{% endcomponent %}');
        $this->assertEquals('<html><head></head><body><main>core component</main></body></html>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderNestedComponentScope()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% component(\'componentnested.template\') %}card with nest{% endcomponent %}');
        $this->assertEquals('<html><head></head><body><div class="card">card with nest</div>' . PHP_EOL . '</body></html>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderComponentScopeMultyple()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% component(\'componentcard.template\') %}oke{% endcomponent %} {% component(\'componentcard.template\') %}oke 2 {% endcomponent %}');
        $this->assertEquals('<div class="card">oke</div>' . PHP_EOL . ' <div class="card">oke 2 </div>', trim($out));
    }

    /**
     * @test
     */
    public function itThrowWhenExtendNotFound()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        try {
            $templator->templates('{% component(\'notexits.template\') %}<main>core component</main>{% endcomponent %}');
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
            $templator->templates('{% component(\'componentyield.template\') %}<main>core component</main>{% endcomponent %}');
        } catch (\Throwable $th) {
            $this->assertEquals('yield section not found: component2.template', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanRenderComponentUsingNamedParameter()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $out       = $templator->templates('{% component(\'componentnamed.template\', bg:\'bg-red\', size:"md") %}inner text{% endcomponent %}');
        $this->assertEquals('<p class="bg-red md">inner text</p>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderComponentOppAprocess()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__ . '/view/'], ['']), __DIR__);
        $templator->setComponentNamespace('System\\Test\\View\\Templator\\');
        $out = $templator->templates('{% component(\'TestClassComponent\', bg:\'bg-red\', size:"md") %}inner text{% endcomponent %}');
        $this->assertEquals('<p class="bg-red md">inner text</p>', trim($out));
    }

    /**
     * @test
     */
    public function itCanGetDependencyView()
    {
        $finder    = new TemplatorFinder([__DIR__ . '/view/'], ['']);
        $templator = new Templator($finder, __DIR__);
        $templator->templates('{% component(\'component.template\') %}<main>core component</main>{% endcomponent %}', 'test');
        $this->assertEquals([
            $finder->find('component.template') => 1,
        ], $templator->getDependency('test'));
    }
}

class TestClassComponent
{
    private string $bg;
    private string $size;

    public function __construct(string $bg, string $size)
    {
        $this->bg   = $bg;
        $this->size = $size;
    }

    public function render(string $inner): string
    {
        return "<p class=\"{$this->bg} {$this->size}\">{$inner}</p>";
    }
}
