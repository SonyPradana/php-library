<?php

use PHPUnit\Framework\TestCase;
use System\Support\Exceptions\MacroNotFound;
use System\Support\Marco;

final class MacroTest extends TestCase
{
    protected $mock_class;

    protected function setUp(): void
    {
        $this->mock_class = new class() {
            use Marco;
        };
    }

    protected function tearDown(): void
    {
        $this->mock_class->resetMacro();
    }

    /** @test */
    public function itCanAddMacro()
    {
        $this->mock_class->macro('test', fn (): bool => true);
        $this->mock_class->macro('test_param', fn (bool $bool): bool => $bool);

        $this->assertTrue($this->mock_class->test());
        $this->assertTrue($this->mock_class->test_param(true));
    }

    /** @test */
    public function itCanAddMacroStatic()
    {
        $this->mock_class->macro('test', fn (): bool => true);
        $this->mock_class->macro('test_param', fn (bool $bool): bool => $bool);

        $this->assertTrue($this->mock_class::test());
        $this->assertTrue($this->mock_class::test_param(true));
    }

    /** @test */
    public function itCanCheackMacro()
    {
        $this->mock_class->macro('test', fn (): bool => true);

        $this->assertTrue($this->mock_class->hasMacro('test'));
        $this->assertFalse($this->mock_class->hasMacro('test2'));
    }

    /** @test */
    public function itThrowWhenMacroNotRegister()
    {
        $this->expectException(MacroNotFound::class);

        $this->mock_class->test();
    }
}
