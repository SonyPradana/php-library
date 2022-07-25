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
        $text =  $cmd->textBlue();

        $this->assertEquals("\e[34;49mtext\e[0m", $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanRenderBgColorTerminalCode()
    {
        $cmd  = new Style('text');
        $text =  $cmd->bgBlue();

        $this->assertEquals("\e[39;44mtext\e[0m", $text, 'text must return blue bankground terminal code');
    }

    /** @test */
    public function itCanRenderTextAndBgColorTerminalCode()
    {
        $cmd  = new Style('text');
        $text =  $cmd->textRed()->bgBlue();

        $this->assertEquals("\e[31;44mtext\e[0m", $text, 'text must return red text and blue text terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingRawRuleInterface()
    {
        $cmd  = new Style('text');
        $text =  $cmd->raw(Colors::hexText('#ffd787'));

        $this->assertEquals("\e[39;49;38;2;255;215;135mtext\e[0m", $text, 'text must return raw color terminal code');

        $cmd  = new Style('text');
        $text = $cmd->raw(Colors::rgbText(0, 0, 0));

        $this->assertEquals("\e[39;49;38;2;0;0;0mtext\e[0m", $text);
    }

    /** @test */
    public function itCanRenderColorRaw()
    {
        $cmd  = new Style('text');
        $text = $cmd->raw('38;2;0;0;0');

        $this->assertEquals("\e[39;49;38;2;0;0;0mtext\e[0m", $text);
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

        $this->assertEquals("\e[2;49mi\e[0m\e[31;49mlove\e[0m\e[34;49mphp\e[0m", $text, 'text must return blue text terminal code');
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
            "start \e[39;44mi\e[0m\e[39;44m love \e[0m\e[39;44mphp\e[0m end",
            $out
        );
    }
}
