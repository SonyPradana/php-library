<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Style\Colors;
use System\Console\Style\Rule;

final class RuleTest extends TestCase
{
    /** @test */
    public function itCanRenderTextColorTerminalCode()
    {
        $cmd  = new Rule('text');
        $text =  $cmd->textBlue();

        $this->assertEquals("\e[34;49mtext\e[0m", $text, 'text must return blue text terminal code');
    }

    /** @test */
    public function itCanRenderBgColorTerminalCode()
    {
        $cmd  = new Rule('text');
        $text =  $cmd->bgBlue();

        $this->assertEquals("\e[39;44mtext\e[0m", $text, 'text must return blue bankground terminal code');
    }

    /** @test */
    public function itCanRenderTextAndBgColorTerminalCode()
    {
        $cmd  = new Rule('text');
        $text =  $cmd->textRed()->bgBlue();

        $this->assertEquals("\e[31;44mtext\e[0m", $text, 'text must return red text and blue text terminal code');
    }

    /** @test */
    public function itCanRenderColorUsingRawTerminalCode()
    {
        $cmd  = new Rule('text');
        $text =  $cmd->raw(Colors::hexRawText('#ffd787'));

        $this->assertEquals("\e[39;49;38;5;222mtext\e[0m", $text, 'text must return raw color terminal code');
    }

    /** @test */
    public function itCanRenderChainCode()
    {
        $cmd = new Rule('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->out(false)
            ;
        $text = ob_get_clean();

        $this->assertEquals("\e[2;49mi\e[0m\e[31;49mlove\e[0m\e[34;49mphp\e[0m", $text, 'text must return blue text terminal code');
    }
}
