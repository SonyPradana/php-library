<?php

use PHPUnit\Framework\TestCase;
use System\Text\Str;

final class StrMacroTest extends TestCase
{
    /** @test */
    public function itCanRegisterStringMacro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        $this->assertEquals('i love laravel', Str::add_prefix('laravel', 'i love '));

        Str::resetMacro();
    }

    /** @test */
    public function itCanThrowErrorWhenMacroNotFound()
    {
        $this->expectExceptionMessage('Macro hay is not macro able.');
        Str::hay();
    }

    /** @test */
    public function itCanResetStringMacro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        Str::resetMacro();

        $this->expectExceptionMessage('Macro add_prefix is not macro able.');

        Str::add_prefix('a', 'b');
        Str::resetMacro();
    }
}
