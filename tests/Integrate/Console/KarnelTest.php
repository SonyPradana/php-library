<?php

declare(strict_types=1);

namespace System\Test\Integrate\Console;

use PHPUnit\Framework\TestCase;
use System\Console\Command;
use System\Integrate\Application;
use System\Integrate\Console\Karnel;
use System\Integrate\PackageManifest;
use System\Integrate\ValueObjects\CommandMap;
use System\Text\Str;

use function System\Console\style;

final class KarnelTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/app2/',
            application_cache_path: dirname(__DIR__) . '/assets/app2/bootstrap/cache/',
            vendor_path: '/app2/package/'
        ));
    }

    protected function tearDown(): void
    {
        $this->app->flush();
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
    public function itCanCallCommandUsingPatternGroupCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'pattern1']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'command has founded');
        $this->assertTrue($hasContent);

        // 2
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'pattern2']);
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
        $exit    = $karnel->handle(['cli', 'use:default_option']);
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $hasContent = Str::contains($out, 'test');
        $this->assertTrue($hasContent);
    }

    /** @test */
    public function itCanReturnNothingBecauseCommandNotFound()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'test']);
        ob_get_clean();

        $this->assertEquals(1, $exit);
    }

    /** @test */
    public function itCanReturnCommandNotFoundBecauseNotClosetAnotherCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'xzy']);
        $out     = ob_get_clean();

        $this->assertEquals(1, $exit);
        $condition =  Str::contains($out, 'Command Not Found, run help command');
        $this->assertTrue($condition);
    }

    /** @test */
    public function itCanReturnSuggestionCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:patern']);
        $out     = ob_get_clean();

        $this->assertEquals(1, $exit);
        $condition =  Str::contains($out, 'Did you mean?');
        $this->assertTrue($condition);
        $condition =  Str::contains($out, 'use:pattern');
        $this->assertTrue($condition);
    }

    /** @test */
    public function itCanGivenClosetCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit    = $karnel->handle(['cli', 'use:']);
        $out     = ob_get_clean();

        $this->assertEquals(1, $exit);
        $condition =  Str::contains($out, 'use:full');
        $this->assertTrue($condition);
    }

    /** @test */
    public function itCanBootstrap()
    {
        $this->assertFalse($this->app->isBootstrapped());
        $this->app->make(Karnel::class)->bootstrap();
        $this->assertTrue($this->app->isBootstrapped());
    }

    /** @test */
    public function itCanCallCommand()
    {
        $karnel = new NormalCommand($this->app);
        ob_start();
        $exit = $karnel->call('cli use:no-int-return');
        ob_get_clean();

        $this->assertEquals(0, $exit);
    }

    /**
     * @test
     */
    public function itCanGetSimilarCommand()
    {
        $karnel = new Karnel($this->app);
        $result = (fn () => $this->{'getSimilarity'}('make:view', ['view:clear', 'make:view', 'make:controller']))->call($karnel);
        $this->assertArrayHasKey('make:view', $result);
        $this->assertArrayHasKey('make:controller', $result);
    }
}

class NormalCommand extends Karnel
{
    protected function commands(): array
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
                'pattern' => ['pattern1', 'pattern2'],
                'fn'      => [FoundedCommand::class, 'main'],
            ]),
            new CommandMap([
                'pattern' => 'use:default_option',
                'fn'      => [FoundedCommand::class, 'default'],
                'default' => [
                    'default' => 'test',
                ],
            ]),
            new CommandMap([
                'pattern' => 'use:no-int-return',
                'fn'      => [FoundedCommand::class, 'returnVoid'],
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

    public function returnVoid(Application $app): void
    {
    }
}
