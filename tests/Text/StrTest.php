<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Text\Str;

final class StrTest extends TestCase
{
    /** @test */
    public function it_return_charcter_specifid_postion()
    {
        $text = 'i love laravel';

        $this->assertEquals('o', Str::chartAt($text, 3));
    }

    /** @test */
    public function it_Join_two_or_more_string_into_once()
    {
        $text = ['i', 'love', 'laravel'];

        $this->assertEquals('i love laravel', Str::concat($text));

        $this->assertEquals('i love and laravel', Str::concat($text, ' ', 'and'));
    }

    /** @test */
    public function it_can_find_index_of_string()
    {
        $text = 'i love laravel';

        $this->assertEquals(2, Str::indexOf($text, 'l'));
    }

    /** @test */
    public function it_can_find_last_index_of_string()
    {
        $text = 'i love laravel';

        $this->assertEquals(13, Str::lastIndexOf($text, 'l'));
    }

    /** @test */
    public function it_can_find_matches_from_pattern()
    {
        $text = 'i love laravel';

        $matches =  Str::match($text, '/love/');

        $this->assertContains('love', $matches);

        $matches = Str::match($text, '/rust/');

        $this->assertNull($matches, 'cek match return null if pattern not found');
    }

    /** @test */
    public function it_can_sarch_text()
    {
        $text = 'i love laravel';

        $this->assertEquals(7, Str::indexOf($text, 'laravel'));
        $this->assertFalse(Str::indexOf($text, 'rust'), 'the text nit contain spesifict string');
    }

    public function it_can_slice_string()
    {
        $text = 'i love laravel';

        $this->assertEquals('laravel', Str::slice($text, 7), 'without lenght');
        $this->assertEquals('lara', Str::slice($text, 7, 4), 'without lenght');
        $this->assertEquals('larave', Str::slice($text, 7, -1), 'without lenght');
        $this->assertFalse(Str::slice($text, 15), 'out of length');
    }

    /** @test */
    public function it_can_splint_string()
    {
        $text = 'i love laravel';

        $this->assertEquals(['i', 'love', 'laravel'], Str::split($text, ' '));
        $this->assertEquals(['i', 'love laravel'], Str::split($text, ' ', 2), 'with limit');
    }

    /** @test */
    public function it_can_find_and_replace_text()
    {
        $text = 'i love laravel';

        $this->assertEquals('i love php', Str::replace($text, 'laravel', 'php'));
    }

    /** @test */
    public function it_can_uppercase_string()
    {
        $text = 'i love laravel';

        $this->assertEquals('I LOVE LARAVEL', Str::toUpperCase($text));
    }

    /** @test */
    public function it_can_lowercase_string()
    {
        $text = 'I LOVE LARAVEL';

        $this->assertEquals('i love laravel', Str::toLowerCase($text));
    }

    /** @test */
    public function it_can_ucfirst_string()
    {
        $text = 'laravel';

        $this->assertEquals('Laravel', Str::firstUpper($text));
    }

    /** @test */
    public function it_can_ucword_string()
    {
        $text = 'i love laravel';

        $this->assertEquals('I Love Laravel', Str::firstUpperAll($text));
    }

    /** @test */
    public function it_can_snackcase()
    {
        $text = 'i love laravel';

        $this->assertEquals('i_love_laravel', Str::toSnackCase($text));

        $text = 'i-love-laravel';

        $this->assertEquals('i_love_laravel', Str::toSnackCase($text));

        $text = 'i_love_laravel';

        $this->assertEquals('i_love_laravel', Str::toSnackCase($text));

        $text = 'i+love+laravel';

        $this->assertEquals('i_love_laravel', Str::toSnackCase($text));

        $text = 'i+love_laravel';

        $this->assertEquals('i_love_laravel', Str::toSnackCase($text));
    }

    /** @test */
    public function it_can_kebabcase()
    {
        $text = 'i love laravel';

        $this->assertEquals('i-love-laravel', Str::toKebabCase($text));

        $text = 'i-love-laravel';

        $this->assertEquals('i-love-laravel', Str::toKebabCase($text));

        $text = 'i_love_laravel';

        $this->assertEquals('i-love-laravel', Str::toKebabCase($text));

        $text = 'i+love+laravel';

        $this->assertEquals('i-love-laravel', Str::toKebabCase($text));

        $text = 'i+love_laravel';

        $this->assertEquals('i-love-laravel', Str::toKebabCase($text));
    }

    /** @test */
    public function it_can_pascalcase()
    {
        $text = 'i love laravel';

        $this->assertEquals('ILoveLaravel', Str::toPascalCase($text));

        $text = 'i-love-laravel';

        $this->assertEquals('ILoveLaravel', Str::toPascalCase($text));

        $text = 'i_love_laravel';

        $this->assertEquals('ILoveLaravel', Str::toPascalCase($text));

        $text = 'i+love+laravel';

        $this->assertEquals('ILoveLaravel', Str::toPascalCase($text));

        $text = 'i+love_laravel';

        $this->assertEquals('ILoveLaravel', Str::toPascalCase($text));
    }

    /** @test */
    public function it_can_camelcase()
    {
        $text = 'i love laravel';

        $this->assertEquals('iLoveLaravel', Str::toCamelCase($text));

        $text = 'i-love-laravel';

        $this->assertEquals('iLoveLaravel', Str::toCamelCase($text));

        $text = 'i_love_laravel';

        $this->assertEquals('iLoveLaravel', Str::toCamelCase($text));

        $text = 'i+love+laravel';

        $this->assertEquals('iLoveLaravel', Str::toCamelCase($text));

        $text = 'i+love_laravel';

        $this->assertEquals('iLoveLaravel', Str::toCamelCase($text));
    }

    /** @test */
    public function it_can_detect_text_contain_with()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::contains($text, 'laravel'));
        $this->assertFalse(Str::contains($text, 'symfony'));
    }

    /** @test */
    public function it_can_detect_text_starts_with()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::startsWith($text, 'i'));
        $this->assertFalse(Str::startsWith($text, 'love'));
    }

    /** @test */
    public function it_can_detect_text_ends_with()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::endsWith($text, 'laravel'));
        $this->assertFalse(Str::endsWith($text, 'love'));
    }

}
