<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use function System\Console\style;

use System\Console\Style\Colors;
use System\Console\Style\Style;

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

        $this->assertEquals(sprintf('%s[38;2;244;67;54;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');

        $text = (new Style('text'))->bg_blue_500();

        $this->assertEquals(sprintf('%s[39;48;2;33;150;243mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanThrowExceptionWhenColorVariantNotRegister()
    {
        $this->expectError();
        (new Style('text'))->text_red_10();
    }

    /** @test */
    public function itCanCountTextLengthWithoutRuleCounted()
    {
        $text = new Style('12345');
        $text->bgBlue()->textWhite()->underline();

        $this->assertEquals(5, $text->length());
    }

    /** @test */
    public function itCanCountTextNumberLengthWithoutRuleCounted()
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
}