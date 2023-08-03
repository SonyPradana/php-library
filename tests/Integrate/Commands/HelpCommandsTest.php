<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Console\HelpCommand;

final class HelpCommandsTest extends CommandTest
{
    private function maker(string $argv): HelpCommand
    {
        return new class($this->argv($argv)) extends HelpCommand {
            public function __construct($argv)
            {
                parent::__construct($argv);
                $this->commands = [
                    [
                        'cmd'       => ['-h', '--help'],
                        'mode'      => 'full',
                        'class'     => self::class,
                        'fn'        => 'main',
                    ],
                ];
            }
        };
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandMain()
    {
        $helpCommand = $this->maker('php cli --help');
        ob_start();
        $exit = $helpCommand->main();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandCommandList()
    {
        $helpCommand = $this->maker('php cli --list');
        ob_start();
        $exit = $helpCommand->commandList();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandCommandHelpButNoFound()
    {
        $helpCommand = $this->maker('cli help main');
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertFails($exit);
        $this->assertContain('Help for `main` command not found', $out);
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandCommandHelpButNoResult()
    {
        $helpCommand = $this->maker('cli help');
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertFails($exit);
        $this->assertContain('php cli help <command_nama>', $out);
    }
}
