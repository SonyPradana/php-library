<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Console\Command;
use System\Integrate\ConfigRepository;
use System\Integrate\Console\HelpCommand;

final class HelpCommandsTest extends CommandTest
{
    private array $command = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->set('config', fn () => new ConfigRepository([
            'commands' => [$this->command],
        ]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->command = [];
    }

    /**
     * @test
     */
    public function itCanCallHelpCommandMain()
    {
        $this->command = [
            [
                'cmd'       => ['-h', '--help'],
                'mode'      => 'full',
                'class'     => HelpCommand::class,
                'fn'        => 'main',
            ],
        ];

        $helpCommand = new HelpCommand(['cli', '--help']);
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
        $this->command = [
            [
                'pattern' => 'test',
                'fn'      => [RegisterHelpCommand::class, 'main'],
            ],
        ];

        $helpCommand = new HelpCommand(['cli', '--help']);

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
    public function itCanCallHelpCommandMainWithRegesterAnotherCommandUsingClass()
    {
        $this->command = [
            ['class' => RegisterHelpCommand::class],
        ];

        $helpCommand = new HelpCommand(['cli', '--help']);

        // use old style commandmaps
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
        $helpCommand = new HelpCommand(['cli', '--list']);

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
        $this->command = [
            [
                'pattern' => 'unit:test',
                'fn'      => [RegisterHelpCommand::class, 'main'],
            ],
        ];

        $helpCommand = new HelpCommand(['cli', '--list']);

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
        $helpCommand = new HelpCommand(['cli', 'help', 'serve']);
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
        $helpCommand =  new HelpCommand(['cli', 'help', 'main']);
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
        $helpCommand =  new HelpCommand(['cli', 'help']);
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
