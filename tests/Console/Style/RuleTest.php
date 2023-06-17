<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Style\Rule;

final class RuleTest extends TestCase
{
    /** @test */
    public function itCanGetsTextColorTerminalCode()
    {
        $cmd  = new Rule();
        $text = $cmd->textBlue();

        $this->assertEquals([[34, 49], [0]], $text->toArray());
    }

    /** @test */
    public function itCanGetsTextColorTerminalCodeMulty()
    {
        $cmd  = new Rule();
        $text = $cmd
            ->textBlue()
            ->bgYellow()
            ->rawReset([22]);

        $this->assertEquals([[34, 43], [22]], $text->toArray());
    }
}
