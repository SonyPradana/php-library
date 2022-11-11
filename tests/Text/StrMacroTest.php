<?php

use PHPUnit\Framework\TestCase;
use System\Support\Exceptions\MacroNotFound;
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
        $this->expectException(MacroNotFound::class);
        Str::hay();
    }

    /** @test */
    public function itCanResetStringMacro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        $add_prefix = Str::add_prefix('a', 'b');
        $this->assertEquals('ba', $add_prefix);
        Str::resetMacro();

        $this->expectException(MacroNotFound::class);

        Str::add_prefix('a', 'b');
        Str::resetMacro();
    }
}
