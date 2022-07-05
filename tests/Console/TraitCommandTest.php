<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Console\Traits\CommandTrait;

final class TraitCommandTest extends TestCase
{
    /** @var class */
    private $command;

    protected function setUp(): void
    {
        $this->command = new class(['cli', '--test']) extends Command {
            use CommandTrait;

            public function __call($name, $arguments)
            {
                if ($name === 'echoTextRed') {
                    echo $this->textRed('Color');
                }
                if ($name === 'echoTextYellow') {
                    echo $this->textYellow('Color');
                }
                if ($name === 'echoTextGreen') {
                    echo $this->textGreen('Color');
                }
                if ($name === 'textColor') {
                    echo $this->textColor((int) $arguments[0], 'Color');
                }
            }
        };
    }

    /** @test */
    public function itCanMakeTextRed()
    {
        ob_start();
        $this->command->echoTextRed();
        $out = ob_get_clean();

        $this->assertEquals("\e[31mColor\e[0m", $out);
    }

    /** @test */
    public function itCanMakeTextYellow()
    {
        ob_start();
        $this->command->echoTextYellow();
        $out = ob_get_clean();

        $this->assertEquals("\e[33mColor\e[0m", $out);
    }

    /** @test */
    public function itCanMakeTextGreen()
    {
        ob_start();
        $this->command->echoTextGreen();
        $out = ob_get_clean();

        $this->assertEquals("\e[32mColor\e[0m", $out);
    }

    /** @test */
    public function itCanMakeTextColor()
    {
        ob_start();
        $this->command->textColor(25);
        $out = ob_get_clean();

        $this->assertEquals("\e[38;5;25mColor\e[0m", $out);
    }
}
