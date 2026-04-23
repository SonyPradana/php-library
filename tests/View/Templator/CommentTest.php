<?php

declare(strict_types=1);

namespace System\Test\View\Templator;

use PHPUnit\Framework\TestCase;
use System\View\Templator;
use System\View\TemplatorFinder;

final class CommentTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderComment()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{# this a comment #}');
        $this->assertEquals('<?php /* this a comment */ ?>', $out);
    }

    /**
     * @test
     */
    public function itCanRenderMultilineComment()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{# 
            line 1
            line 2
        #}');
        $this->assertStringContainsString('/* line 1', $out);
        $this->assertStringContainsString('line 2 */', $out);
    }

    /**
     * @test
     */
    public function itCanRenderCommentWithSpecialCharacters()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{# comment with {{ $var }} and {% directive %} #}');
        $this->assertEquals('<?php /* comment with {{ $var }} and {% directive %} */ ?>', $out);
    }
}
