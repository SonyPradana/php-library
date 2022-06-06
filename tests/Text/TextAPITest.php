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
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

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
}
