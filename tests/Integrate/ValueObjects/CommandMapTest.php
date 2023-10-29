<?php

declare(strict_types=1);

namespace System\Test\Integrate\ValueObjects;

use PHPUnit\Framework\TestCase;
use System\Integrate\ValueObjects\CommandMap;

class CommandMapTest extends TestCase
{
    /**
     * @test
     */
    public function itCanGetCmd()
    {
        $command = new CommandMap([
            'cmd' => 'test:test',
        ]);

        $this->assertEquals(['test:test'], $command->cmd());
    }

    /**
     * @test
     */
    public function itCanGetMode()
    {
        $command = new CommandMap([
            'cmd'  => 'test:test',
            'mode' => 'full',
        ]);

        $this->assertEquals('full', $command->mode());
    }

    /**
     * @test
     */
    public function itCanGetModeDefault()
    {
        $command = new CommandMap([]);

        $this->assertEquals('full', $command->mode());
    }

    /**
     * @test
     */
    public function itCanGetClass()
    {
        $command = new CommandMap([
            'class' => 'testclass',
        ]);

        $this->assertEquals('testclass', $command->class());
    }

    /**
     * @test
     */
    public function itCanGetClassUsingFn()
    {
        $command = new CommandMap([
            'fn' => ['testclass', 'main'],
        ]);

        $this->assertEquals('testclass', $command->class());
    }

    /**
     * @test
     */
    public function itWillThrowErrorWhenFnIsArrayButClassNotExist()
    {
        $command = new CommandMap([
            'fn' => [],
        ]);

        try {
            $command->class();
        } catch (\Throwable $th) {
            $this->assertEquals('Command map require class in (class or fn).', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itWillThrowErrorWhenClassNotExist()
    {
        $command = new CommandMap([]);

        try {
            $command->class();
        } catch (\Throwable $th) {
            $this->assertEquals('Command map require class in (class or fn).', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanGetFn()
    {
        $command = new CommandMap([
            'fn' => ['testclass', 'main'],
        ]);

        $this->assertEquals(['testclass', 'main'], $command->fn());
    }

    /**
     * @test
     */
    public function itCanGetFnDefault()
    {
        $command = new CommandMap([]);

        $this->assertEquals('main', $command->fn());
    }

    /**
     * @test
     */
    public function itCanGetDefaultOption()
    {
        $command = new CommandMap([]);

        $this->assertEquals('main', $command->fn());
    }

    /**
     * @test
     */
    public function itCanMatchCallbackUsingPattern()
    {
        $command = new CommandMap([
            'pattern' => 'test:test',
        ]);

        $this->assertTrue(($command->match())('test:test'));
    }

    /**
     * @test
     */
    public function itCanMatchCallbackUsingMatch()
    {
        $command = new CommandMap([
            'match' => fn ($given) => true,
        ]);

        $this->assertTrue(($command->match())('always_true'));
    }

    /**
     * @test
     */
    public function itCanMatchCallbackUsingCmdFull()
    {
        $command = new CommandMap([
            'cmd' => ['test:test', 'test:start'],
        ]);

        $this->assertTrue(($command->match())('test:test'));
    }

    /**
     * @test
     */
    public function itCanMatchCallbackUsingCmdStart()
    {
        $command = new CommandMap([
            'cmd'  => ['make:', 'test:'],
            'mode' => 'start',
        ]);

        $this->assertTrue(($command->match())('test:unit'));
    }

    /**
     * @test
     */
    public function itCanCallIsMatch()
    {
        $command = new CommandMap([
            'cmd'  => 'test:unit',
        ]);

        $this->assertTrue($command->isMatch('test:unit'));
    }

    /**
     * @test
     */
    public function itCanGetCallUsingFn()
    {
        $command = new CommandMap([
            'fn'  => ['someclass', 'main'],
        ]);

        $this->assertEquals(['someclass', 'main'], $command->call());
    }

    /**
     * @test
     */
    public function itCanGetCallUsingClass()
    {
        $command = new CommandMap([
            'class'=> 'someclass',
            // skip 'fn' becouse default if 'main'
        ]);

        $this->assertEquals(['someclass', 'main'], $command->call());
    }
}
