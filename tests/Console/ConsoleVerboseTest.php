<?php

declare(strict_types=1);

namespace System\Test\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;

class ConsoleVerboseTest extends TestCase
{
    /** @test */
    public function itCanGetDefaultVerbosity()
    {
        $command = 'php cli test';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue(Command::VERBOSITY_NORMAL === $cli->getVerbosity());
    }

    /** @test */
    public function itCanSetVerbosity()
    {
        $command = 'php cli test';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);
        $cli->setVerbosity(Command::VERBOSITY_VERBOSE);

        $this->assertTrue(Command::VERBOSITY_VERBOSE === $cli->getVerbosity());
    }

    /** @test */
    public function itCanGetVerbositySilent()
    {
        $command = 'php cli test --silent';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isSilent());
    }

    /** @test */
    public function itCanGetVerbosityQuiet()
    {
        $command = 'php cli test --quiet';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isQuiet());
    }

    /** @test */
    public function itCanGetVerbosityVerbose()
    {
        $command = 'php cli test -v';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isVerbose());
    }

    /** @test */
    public function itCanGetVerbosityVeryVerbose()
    {
        $command = 'php cli test -vv';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isVeryVerbose());
    }

    /** @test */
    public function itCanGetVerbosityDebug()
    {
        $command = 'php cli test -vvv';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isDebug());

        $command = 'php cli test --debug';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isDebug());
    }

    /** @test */
    public function itCanGetAllVerboseIfRunInDebug()
    {
        $command = 'php cli test --vvv';
        $argv    = explode(' ', $command);
        $cli     = new Command($argv);

        $this->assertTrue($cli->isNormal());
        $this->assertTrue($cli->isVerbose());
        $this->assertTrue($cli->isVeryVerbose());
        $this->assertTrue($cli->isDebug());
    }
}
