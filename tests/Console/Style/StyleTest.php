<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\IO\ResourceOutputStream;
use System\Console\Style\Colors;
use System\Console\Style\Style;

use function System\Console\style;

final class StyleTest extends TestCase
{
    /** @test */
    public function itCanRenderTextColorTerminalCode()
    {
        $cmd  = new Style('text');
        $text = $cmd->textBlue();

        $this->assertEquals(sprintf('%s[34;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanRenderBgColorTerminalCode()
    {
        $cmd  = new Style('text');
        $text = $cmd->bgBlue();

        $this->assertEquals(sprintf('%s[39;44mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue bankground terminal code');
    }

    /** @test */
    public function itCanRenderTextAndBgColorTerminalCode()
    {
        $cmd  = new Style('text');
        $text = $cmd->textRed()->bgBlue();

        $this->assertEquals(sprintf('%s[31;44mtext%s[0m', chr(27), chr(27)), $text, 'text must return red text and blue text terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingRawRuleInterface()
    {
        $cmd  = new Style('text');
        $text = $cmd->raw(Colors::hexText('#ffd787'));

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');

        $cmd  = new Style('text');
        $text = $cmd->raw(Colors::rgbText(0, 0, 0));

        $this->assertEquals(sprintf('%s[38;2;0;0;0;49mtext%s[0m', chr(27), chr(27)), $text);
    }

    /** @test */
    public function itCanRenderColorRaw()
    {
        $cmd  = new Style('text');
        $text = $cmd->raw('38;2;0;0;0');

        $this->assertEquals(sprintf('%s[39;49;38;2;0;0;0mtext%s[0m', chr(27), chr(27)), $text);
    }

    /** @test */
    public function itCanRenderColorMultyRaw()
    {
        $cmd  = new Style('text');
        $text = $cmd
            ->raw('38;2;0;0;0')
            ->raw('48;2;255;255;255');

        $this->assertEquals(sprintf('%s[39;49;38;2;0;0;0;48;2;255;255;255mtext%s[0m', chr(27), chr(27)), $text);
    }

    /** @test */
    public function itCanRenderChainCode()
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->out(false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanPostRenderStyle()
    {
        $printer = [
            style('i')->bgBlue(),
            style(' love ')->bgBlue(),
            style('php')->bgBlue(),
        ];

        ob_start();
        echo 'start ';
        foreach ($printer as $print) {
            echo $print;
        }
        echo ' end';
        $out = ob_get_clean();

        $this->assertEquals(
            sprintf('start %s[39;44mi%s[0m%s[39;44m love %s[0m%s[39;44mphp%s[0m end', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)),
            $out
        );
    }

    /** @test */
    public function itCanRenderTextColorTerminalCodeWithPushNewLineTabsSpaces()
    {
        $cmd  = new Style('text');
        $text = $cmd
            ->textBlue()
            ->tabs(2)
        ;
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), "\t\t", chr(27)), $text);

        $cmd  = new Style('text');
        $text = $cmd
            ->textBlue()
            ->new_lines(2)
        ;
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), "\n\n", chr(27)), $text);

        $cmd  = new Style('text');
        $text = $cmd
            ->textBlue()
            ->repeat('.', 5)
        ;
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), '.....', chr(27)), $text);
    }

    /** @test */
    public function itCanRenderColorUsingTextColor()
    {
        $cmd  = new Style('text');
        $text = $cmd->textColor(Colors::hexText('#ffd787'));

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingTextColorWithHexString()
    {
        $cmd  = new Style('text');
        $text = $cmd->textColor('#ffd787');

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingBgColor()
    {
        $cmd  = new Style('text');
        $text = $cmd->bgColor(Colors::hexBg('#ffd787'));

        $this->assertEquals(sprintf('%s[39;48;2;255;215;135mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingBgColorWithHexString()
    {
        $cmd  = new Style('text');
        $text = $cmd->bgColor('#ffd787');

        $this->assertEquals(sprintf('%s[39;48;2;255;215;135mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanRenderColorVariantUsingMagicCall()
    {
        $text = (new Style('text'))->text_red_500();

        $this->assertEquals(sprintf('%s[38;2;239;68;68;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');

        $text = (new Style('text'))->bg_blue_500();

        $this->assertEquals(sprintf('%s[39;48;2;59;130;246mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanThrowExceptionWhenColorVariantNotRegister()
    {
        try {
            (new Style('text'))->text_red_10();
        } catch (Throwable $th) {
            $this->assertEquals('Undefined constant self::RED_10', $th->getMessage());
        }
    }

    /** @test */
    public function itCanCountTextLengthWithRuleCounted()
    {
        $text = new Style('12345');
        $text->bgBlue()->textWhite()->underline();

        $this->assertEquals(5, $text->length());
    }

    /** @test */
    public function itCanCountTextNumberLengthWithRuleCounted()
    {
        $text = new Style(12345);
        $text->bgBlue()->textWhite()->underline();

        $this->assertEquals(5, $text->length());

        // add using invoke
        $text(123)->bgBlue()->textWhite()->underline();
        $this->assertEquals(3, $text->length());

        // add using push
        $text->push(123)->bgBlue()->textWhite()->underline();
        $this->assertEquals(6, $text->length());
    }

    /** @test */
    public function itCanPushUsingStyle()
    {
        $cmd  = new Style('text');
        $cmd->textBlue();

        $tap = new Style('text2');
        $tap->textRed();

        // push using tab
        $cmd->tap($tap);

        $text = $cmd->__toString();

        $this->assertEquals(
            sprintf('%s[34;49mtext%s[0m%s[31;49mtext2%s[0m', chr(27), chr(27), chr(27), chr(27)),
            $text
        );
    }

    /** @test */
    public function itCanRenderAndResetDecorate()
    {
        $cmd  = new Style('text');
        $text = $cmd->textBlue()->resetDecorate();

        $this->assertEquals(sprintf('%s[34;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanRenderAndResetDecorateUsingRawReset()
    {
        $cmd  = new Style('text');
        $text = $cmd->textBlue()->rawReset([0, 22]);

        $this->assertEquals(sprintf('%s[34;49mtext%s[0;22m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanPrintUsingYield()
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->yield();
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanPrintUsingYieldAndContinue()
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->yield()
            ->push('php')
            ->textBlue()
        ;
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m', chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanPrintUsingYieldContinueAndOut()
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->yield()
            ->push('php')
            ->textBlue()
            ->out(false)
        ;
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itOnlyPrintIfConditionTrue()
    {
        $cmd = new Style('text');
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(true, false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text);

        // using callback
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(fn (): bool => true, false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text);

        // if false
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(false, false);
        $text = ob_get_clean();

        $this->assertEquals('', $text);
    }

    /**
     * Test writing to a valid stream.
     */
    public function testWriteToStream(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);
        $style        = new Style('');

        $style->setOutputStream($outputStream);
        $style->write(false);

        rewind($stream);
        $this->assertEquals('', stream_get_contents($stream));
        fclose($stream);
    }

    /** @test */
    public function itCanRenderWithNoColor(): void
    {
        $cmd = new Style('text', [
            'colorize' => false,
            'decorate' => true,
        ]);

        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->underline()
        ;

        ob_start();
        $cmd->out(false);
        $text = ob_get_clean();

        $this->assertEquals(
            sprintf('%s[mi%s[0m%s[mlove%s[0m%s[4mphp%s[0;24m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)),
            $text,
            'render without color rule'
        );
    }

    /** @test */
    public function itCanRenderWithNoDecorate(): void
    {
        $cmd = new Style('text', [
            'colorize' => true,
            'decorate' => false,
        ]);

        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->underline()
        ;

        ob_start();
        $cmd->out(false);
        $text = ob_get_clean();

        $this->assertEquals(
            sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0;24m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)),
            $text,
            'render without decorate rule'
        );
    }

    /** @test */
    public function itCanRenderWithNoColorNoDecorete(): void
    {
        $cmd = new Style('text', [
            'colorize' => false,
            'decorate' => false,
        ]);

        $cmd('i')
            ->textDim()
            ->bold()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->underline()
        ;

        ob_start();
        $cmd->out(false);
        $text = ob_get_clean();

        $this->assertEquals('ilovephp', $text, 'render without color and decorate rule');
    }

    /** @test */
    public function itCanRenderWithDecoratedUsingTap(): void
    {
        $cmd  = new Style('text', [
            'colorize' => false,
            'decorate' => false,
        ]);
        $cmd->textBlue()->bold();

        $tap = new Style('text2', [
            'colorize' => false,
            'decorate' => false,
        ]);
        $tap->textRed();

        $cmd->tap($tap);

        ob_start();
        $cmd->out(false);
        $text = ob_get_clean();

        $this->assertEquals(
            'texttext2',
            $text
        );
    }

    /** @test */
    public function itCanRenderTextPad()
    {
        $cmd  = new Style('');
        $text = $cmd->pad('red', 10, '*', STR_PAD_BOTH)
        ;

        $this->assertEquals(sprintf('%s[39;49m***red****%s[0m', chr(27), chr(27)), (string) $text);
    }
}
