<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Console\Style\Style;
use System\Console\Traits\CommandTrait;
use System\Console\Traits\ValidateCommandTrait;
use System\Text\Str;
use Validator\Rule\ValidPool;

final class ValidateTraitTest extends TestCase
{
    /** @var class */
    private $command;

    protected function setUp(): void
    {
        $this->command = new class(['php', 'cli', '--test', 'oke']) extends Command {
            use ValidateCommandTrait;

            public function main()
            {
                $this->initValidate($this->option_mapper);
                $this->getValidateMessage(new Style())->out(false);
            }

            protected function validateRule(ValidPool $rules): void
            {
                $rules('test')->required()->min_len(5);
            }
        };
    }

    /** @test */
    public function itCanMakeTextRed()
    {
        ob_start();
        $this->command->main();
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'The Test field needs to be at least 5 characters'));
    }
}
