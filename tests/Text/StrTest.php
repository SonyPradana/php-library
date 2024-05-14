<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Text\Str;

final class StrTest extends TestCase
{
    /** @test */
    public function itReturnCharcterSpecifidPostion()
    {
        $text = 'i love laravel';

        $this->assertEquals('o', Str::chartAt($text, 3));
    }

    /** @test */
    public function itJoinTwoOrMoreStringIntoOnce()
    {
        $text = ['i', 'love', 'laravel'];

        $this->assertEquals('i love laravel', Str::concat($text));

        $this->assertEquals('i love and laravel', Str::concat($text, ' ', 'and'));
    }

    /** @test */
    public function itCanFindIndexOfString()
    {
        $text = 'i love laravel';

        $this->assertEquals(2, Str::indexOf($text, 'l'));
    }

    /** @test */
    public function itCanFindLastIndexOfString()
    {
        $text = 'i love laravel';

        $this->assertEquals(13, Str::lastIndexOf($text, 'l'));
    }

    /** @test */
    public function itCanFindMatchesFromPattern()
    {
        $text = 'i love laravel';

        $matches =  Str::match($text, '/love/');

        $this->assertContains('love', $matches);

        $matches = Str::match($text, '/rust/');

        $this->assertNull($matches, 'cek match return null if pattern not found');
    }

    /** @test */
    public function itCanSarchText()
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
    public function itCanSplintString()
    {
        $text = 'i love laravel';

        $this->assertEquals(['i', 'love', 'laravel'], Str::split($text, ' '));
        $this->assertEquals(['i', 'love laravel'], Str::split($text, ' ', 2), 'with limit');
    }

    /** @test */
    public function itCanFindAndReplaceText()
    {
        $text = 'i love laravel';

        $this->assertEquals('i love php', Str::replace($text, 'laravel', 'php'));
    }

    /** @test */
    public function itCanUppercaseString()
    {
        $text = 'i love laravel';

        $this->assertEquals('I LOVE LARAVEL', Str::toUpperCase($text));
    }

    /** @test */
    public function itCanLowercaseString()
    {
        $text = 'I LOVE LARAVEL';

        $this->assertEquals('i love laravel', Str::toLowerCase($text));
    }

    /** @test */
    public function itCanUcfirstString()
    {
        $text = 'laravel';

        $this->assertEquals('Laravel', Str::firstUpper($text));
    }

    /** @test */
    public function itCanUcwordString()
    {
        $text = 'i love laravel';

        $this->assertEquals('I Love Laravel', Str::firstUpperAll($text));
    }

    /** @test */
    public function itCanSnackcase()
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
    public function itCanKebabcase()
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
    public function itCanPascalcase()
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
    public function itCanCamelcase()
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
    public function itCanDetectTextContainWith()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::contains($text, 'laravel'));
        $this->assertFalse(Str::contains($text, 'symfony'));
    }

    /** @test */
    public function itCanDetectTextStartsWith()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::startsWith($text, 'i'));
        $this->assertFalse(Str::startsWith($text, 'love'));
    }

    /** @test */
    public function itCanDetectTextEndsWith()
    {
        $text = 'i love laravel';

        $this->assertTrue(Str::endsWith($text, 'laravel'));
        $this->assertFalse(Str::endsWith($text, 'love'));
    }

    /** @test */
    public function itCanMakeSlugifyFromText()
    {
        $text = 'i love laravel';

        $this->assertEquals('i-love-laravel', Str::slug($text));

        $text = '-~+-';

        try {
            Str::slug($text);
        } catch (Throwable $th) {
            $this->assertEquals("Method slug with {$text} doest return anythink.", $th->getMessage());
        }
    }

    /** @test */
    public function itCanRenderTemplateString()
    {
        $template = 'i love {lang}';
        $data     = ['lang' => 'laravel'];

        $this->assertEquals('i love laravel', Str::template($template, $data));
    }

    /** @test */
    public function itCanCountText()
    {
        $text = 'i love laravel';

        $this->assertEquals(14, Str::length($text));
    }

    /** @test */
    public function itCanRepeatText()
    {
        $text = 'test';

        $this->assertEquals('testtesttest', Str::repeat($text, 3));
    }

    /** @test */
    public function itCanDetectString()
    {
        $this->assertTrue(Str::isString('text'));

        $this->assertFalse(Str::isString(123));
        $this->assertFalse(Str::isString(false));
        $this->assertFalse(Str::isString([]));
    }

    /** @test */
    public function itCanDetectEmptyString()
    {
        $this->assertTrue(Str::isEmpty(''));
        $this->assertFalse(Str::isEmpty('test'));
    }

    /** @test */
    public function itCanDetectFillStringInTheStart()
    {
        $this->assertEquals('001212', Str::fill('1212', '0', 6));
    }

    /** @test */
    public function itCanDetectFillStringInTheEnd()
    {
        $this->assertEquals('121200', Str::fillEnd('1212', '0', 6));
    }

    /** @test */
    public function itCanMakeMask()
    {
        $this->assertEquals('l****el', Str::mask('laravel', '*', 1, 4));
        $this->assertEquals('l******', Str::mask('laravel', '*', 1));
        $this->assertEquals('lara*el', Str::mask('laravel', '*', -3, 1));
        $this->assertEquals('lara***', Str::mask('laravel', '*', -3));
    }

    /** @test */
    public function itCanMakeLimit()
    {
        $this->assertEquals('laravel best...', Str::limit('laravel best framework', 12));
    }

    /** @test */
    public function itCanGetTextAfter()
    {
        $this->assertEquals(
            '//localhost:8000/test',
            Str::after('https://localhost:8000/test', ':')
        );
    }

    /** @test */
    public function itCanGetTextAfterMustReturnBack()
    {
        $this->assertEquals(
            'https://localhost:8000/test',
            Str::after('https://localhost:8000/test', '~')
        );
    }
}
