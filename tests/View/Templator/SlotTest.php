<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;

final class SlotTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderSectionScope()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\') %}<strong>taylor</strong>{% endsection %}');
        $this->assertEquals('<p><strong>taylor</strong></p>', trim($out));
    }

    /**
     * @test
     */
    public function itThrowWhenExtendNotFound()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        try {
            $templator->templates('{% extend(\'section.html\') %} {% section(\'title\') %}<strong>taylor</strong>{% endsection %}');
        } catch (\Throwable $th) {
            $this->assertEquals('Template file not found: section.html', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanRenderSectionInline()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\', \'taylor\') %}');
        $this->assertEquals('<p>taylor</p>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderSectionInlineEscape()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates('{% extend(\'section.template\') %} {% section(\'title\', \'<script>alert(1)</script>\') %}');
        $this->assertEquals('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderMultySection()
    {
        $templator = new Templator(__DIR__ . '/view/', __DIR__);
        $out       = $templator->templates('
            {% extend(\'section.template\') %}

            {% sections %}
            title : <strong>taylor</strong>
            {% endsections %}
        ');
        $this->assertEquals('<p><strong>taylor</strong></p>', trim($out));
    }
}
