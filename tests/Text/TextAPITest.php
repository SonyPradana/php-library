<?php

use PHPUnit\Framework\TestCase;
use System\Text\Regex;
use System\Text\Text;

class TextAPITest extends TestCase
{
    /** @var Text */
    private $text;

    protected function setUp(): void
    {
        $this->text = new Text('i love symfony');
    }

    protected function tearDown(): void
    {
        $this->text->reset();
    }

    // api test ----------------------------

    /** @test */
    public function itCanReturnChartAt()
    {
        $this->assertEquals('o', $this->text->chartAt(3));
    }

    /** @test */
    public function itCanReturnSlice()
    {
        $this->assertEquals('symfony', $this->text->slice(7));
    }

    /** @test */
    public function itCanReturnLower()
    {
        $this->assertEquals('i love symfony', $this->text->lower());
    }

    /** @test */
    public function itCanReturnUpper()
    {
        $this->assertEquals('I LOVE SYMFONY', $this->text->upper());
    }

    /** @test */
    public function itCanReturnFirstUpper()
    {
        $this->assertEquals('I love symfony', $this->text->firstUpper());
    }

    /** @test */
    public function itCanReturnFirstUpperAll()
    {
        $this->assertEquals('I Love Symfony', $this->text->firstUpperAll());
    }

    /** @test */
    public function itCanReturnSnack()
    {
        $this->assertEquals('i_love_symfony', $this->text->snack());
    }

    /** @test */
    public function itCanReturnKebab()
    {
        $this->assertEquals('i-love-symfony', $this->text->kebab());
    }

    /** @test */
    public function itCanReturnPascal()
    {
        $this->assertEquals('ILoveSymfony', $this->text->pascal());
    }

    /** @test */
    public function itCanReturnCamel()
    {
        $this->assertEquals('iLoveSymfony', $this->text->camel());
    }

    /** @test */
    public function itCanReturnSlug()
    {
        $this->assertEquals('i-love-symfony', $this->text->slug());
    }

    // bool ------------------------------

    /** @test */
    public function itCanReturnIsEmpty()
    {
        $this->assertFalse($this->text->isEmpty());
    }

    /** @test */
    public function itCanReturnIs()
    {
        $this->assertFalse($this->text->is(Regex::USER));
    }

    /** @test */
    public function itCanReturnContains()
    {
        $this->assertTrue($this->text->contains('love'));
    }

    /** @test */
    public function itCanReturnStartsWith()
    {
        $this->assertTrue($this->text->startsWith('i love'));
    }

    /** @test */
    public function itCanReturnEndsWith()
    {
        $this->assertTrue($this->text->endsWith('symfony'));
    }

    // int ---------------------------------------

    /** @test */
    public function itCanReturnLength()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(14, $this->text->length());
    }

    /** @test */
    public function itCanReturnIndexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(7, $this->text->indexOf('symfony'));
    }

    /** @test */
    public function itCanReturnLastIndexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(3, $this->text->indexOf('o'));
    }

    /** @test */
    public function itCanReturnFill()
    {
        $this->text->text('1234');
        $this->assertEquals('001234', $this->text->fill('0', 6));
    }

    /** @test */
    public function itCanReturnFillEnd()
    {
        $this->text->text('1234');
        $this->assertEquals('123400', $this->text->fillEnd('0', 6));
    }

    /** @test */
    public function itCanReturnMask()
    {
        $this->text->text('laravel');
        $this->assertEquals('l****el', $this->text->mask('*', 1, 4));

        $this->text->text('laravel');
        $this->assertEquals('l******', $this->text->mask('*', 1));

        $this->text->text('laravel');
        $this->assertEquals('lara*el', $this->text->mask('*', -3, 1));

        $this->text->text('laravel');
        $this->assertEquals('lara***', $this->text->mask('*', -3));
    }

    public function itCanReturnLimit()
    {
        $this->assertEquals('laravel...', $this->text->limit(7));
    }

    /** @test */
    public function itCanReturnAfetText()
    {
        $this->assertEquals('symfony', $this->text->after('love ')->__toString());
    }
}
