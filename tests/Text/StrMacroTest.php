<?php

use PHPUnit\Framework\TestCase;
use System\Text\Str;

final class StrMacroTest extends TestCase
{
    /** @test */
    public function it_can_register_string_macro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        $this->assertEquals('i love laravel', Str::add_prefix('laravel', 'i love '));

        Str::resetMacro();
    }

    /** @test */
    public function it_can_throw_error_when_macro_not_found()
    {
        $this->expectExceptionMessage('Macro hay is not macro able.');
        Str::hay();
    }

    /** @test */
    public function it_can_reset_string_macro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        Str::resetMacro();

        $this->expectExceptionMessage('Macro add_prefix is not macro able.');

        Str::add_prefix('a', 'b');
        Str::resetMacro();
    }
}
