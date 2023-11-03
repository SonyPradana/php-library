<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Console\Command;
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
    public function itCanCallHelpCommandMainWithRegesterAnotherCommand()
    {
        $helpCommand = new class(['php', 'cli', '--help']) extends HelpCommand {
            protected array $commands = [
                [
                    'pattern' => 'test',
                    'fn'      => [RegisterHelpCommand::class, 'main'],
                ],
            ];

            public function useCommands($commands): void
            {
                $this->commands = $commands;
            }
        };

        ob_start();
        $exit = $helpCommand->main();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('some test will appere in test', $out);
        $this->assertContain('this also will display in test', $out);

        // use old style commandmaps
        $helpCommand->useCommands([
            ['class' => RegisterHelpCommand::class],
        ]);
        ob_start();
        $exit = $helpCommand->main();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('some test will appere in test', $out);
        $this->assertContain('this also will display in test', $out);
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
    public function itCanCallHelpCommandCommandListWithRegisterAnotherCommand()
    {
        $helpCommand = new class(['php', 'cli', '--list']) extends HelpCommand {
            protected array $commands = [
                [
                    'pattern' => 'unit:test',
                    'fn'      => [RegisterHelpCommand::class, 'main'],
                ],
            ];

            public function useCommands($commands): void
            {
                $this->commands = $commands;
            }
        };

        ob_start();
        $exit = $helpCommand->commandList();
        $out  = ob_get_clean();

        $this->assertContain('unit:test', $out);
        $this->assertContain('System\Test\Integrate\Commands\RegisterHelpCommand', $out);
        $this->assertSuccess($exit);
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandCommandHelp()
    {
        $helpCommand = $this->maker('cli help serve');
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('Serve server with port number (default 8080)', $out);
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

class RegisterHelpCommand extends Command
{
    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'test' => 'some test will appere in test',
            ],
            'options'   => [
                '--test' => 'this also will display in test',
            ],
            'relation'  => [
                'test' => ['[unit]'],
            ],
        ];
    }
}
