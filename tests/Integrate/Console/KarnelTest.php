<?php

declare(strict_types=1);

namespace System\Test\Integrate\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Integrate\Application;
use System\Integrate\Console\Karnel;
use System\Integrate\ValueObjects\CommandMap;
use System\Text\Str;

use function System\Console\style;

final class KarnelTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = new Application('/');
    }

    protected function tearDown(): void
    {
        $this->app->flush();
    }

    /** @test */
    public function itCanReturnNothingBecauseCommandNotFound()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'test']);
        $out     = ob_get_clean();

        $this->assertEquals(1, $exit);
        $hasContent = Str::contains($out, 'Command Not Found, run help command');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingFullCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:full']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingGroupCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:group']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingStartCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'start:tesing']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingWithoutModeCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:without_mode']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingWithoutMainCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:without_main']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingMatchCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:match']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandUsingPatternCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:pattern']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanCallCommandWithDefaultOption()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:default_option', '--default="test"']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'test');
        $this->assertTrue($hasContent);
    }
}

class NormalCommand extends Karnel
{
    protected function commands()
    {
        return [
            // olr style
            new CommandMap([
                'cmd'   => 'use:full',
                'mode'  => 'full',
                'class' => FoundedCommand::class,
                'fn'    => 'main',
            ]),
            new CommandMap([
                'cmd'   => ['use:group', 'group'],
                'class' => FoundedCommand::class,
                'fn'    => 'main',
            ]),
            new CommandMap([
                'cmd'   => 'start:',
                'mode'  => 'start',
                'class' => FoundedCommand::class,
                'fn'    => 'main',
            ]),
            new CommandMap([
                'cmd'   => 'use:without_mode',
                'class' => FoundedCommand::class,
                'fn'    => 'main',
            ]),
            new CommandMap([
                'cmd'   => 'use:without_main',
                'class' => FoundedCommand::class,
            ]),
            new CommandMap([
                'match' => fn ($given) => $given == 'use:match',
                'fn'    => [FoundedCommand::class, 'main'],
            ]),
            new CommandMap([
                'pattern' => 'use:pattern',
                'fn'      => [FoundedCommand::class, 'main'],
            ]),
            new CommandMap([
                'pattern' => 'use:default_option',
                'fn'      => [FoundedCommand::class, 'default'],
            ]),
        ];
    }
}

class FoundedCommand extends Command
{
    public function main(): int
    {
        style('command has founded')->out();

        return 0;
    }

    public function default(): int
    {
        style($this->default)->out(false);

        return 0;
    }
}
