<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class IfTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderIf()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $out       = $templator->templates('<html><head></head><body><h1>{% if ($true === true) %} show {% endif %}</h1><h1>{% if ($true === false) %} show {% endif %}</h1></body></html>');
        $this->assertEquals('<html><head></head><body><h1><?php if (($true === true) ): ?> show <?php endif; ?></h1><h1><?php if (($true === false) ): ?> show <?php endif; ?></h1></body></html>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderIfElse()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $out       = $templator->templates('<div>{% if ($condition) %}true case{% else %}false case{% endif %}</div>');
        $this->assertEquals('<div><?php if (($condition) ): ?>true case<?php else: ?>false case<?php endif; ?></div>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderNestedIf()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($level1) %}Level 1 true{% if ($level2) %}Level 2 true{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($level1) ): ?>Level 1 true<?php if (($level2) ): ?>Level 2 true<?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }

    /**
     * @test
     */
    public function itCanRenderComplexNestedIfElse()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($level1) %}Level 1 true{% if ($level2) %}Level 2 true{% else %}Level 2 false{% if ($level3) %}Level 3 true inside level 2 false{% endif %}{% endif %}{% else %}Level 1 false{% if ($otherCondition) %}Other condition true{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($level1) ): ?>Level 1 true<?php if (($level2) ): ?>Level 2 true<?php else: ?>Level 2 false<?php if (($level3) ): ?>Level 3 true inside level 2 false<?php endif; ?><?php endif; ?><?php else: ?>Level 1 false<?php if (($otherCondition) ): ?>Other condition true<?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }

    /**
     * @test
     */
    public function itCanHandleMultipleIfBlocksWithNesting()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__]), __DIR__);
        $template  = '<div>{% if ($block1) %}Block 1 content{% if ($nested1) %}Nested 1{% endif %}{% endif %}{% if ($block2) %}Block 2 content{% if ($nested2) %}Nested 2{% if ($deepnested) %}Deep nested{% endif %}{% endif %}{% endif %}</div>';
        $expected  = '<div><?php if (($block1) ): ?>Block 1 content<?php if (($nested1) ): ?>Nested 1<?php endif; ?><?php endif; ?><?php if (($block2) ): ?>Block 2 content<?php if (($nested2) ): ?>Nested 2<?php if (($deepnested) ): ?>Deep nested<?php endif; ?><?php endif; ?><?php endif; ?></div>';

        $this->assertEquals($expected, $templator->templates($template));
    }
}
