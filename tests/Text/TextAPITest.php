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
    public function it_can_return_chartAt()
    {
        $this->assertEquals('o', $this->text->chartAt(3));
    }

    /** @test */
    public function it_can_return_slice()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertEquals('symfony', $this->text->slice(7));
    }

    /** @test */
    public function it_can_return_lower()
    {
        $this->assertEquals('i love symfony', $this->text->lower());
    }

    /** @test */
    public function it_can_return_upper()
    {
        $this->assertEquals('I LOVE SYMFONY', $this->text->upper());
    }

    /** @test */
    public function it_can_return_firstUpper()
    {
        $this->assertEquals('I love symfony', $this->text->firstUpper());
    }

    /** @test */
    public function it_can_return_firstUpperAll()
    {
        $this->assertEquals('I Love Symfony', $this->text->firstUpperAll());
    }

    /** @test */
    public function it_can_return_snack()
    {
        $this->assertEquals('i_love_symfony', $this->text->snack());
    }

    /** @test */
    public function it_can_return_kebab()
    {
        $this->assertEquals('i-love-symfony', $this->text->kebab());
    }

    /** @test */
    public function it_can_return_pascal()
    {
        $this->assertEquals('ILoveSymfony', $this->text->pascal());
    }

    /** @test */
    public function it_can_return_camel()
    {
        $this->assertEquals('iLoveSymfony', $this->text->camel());
    }

    /** @test */
    public function it_can_return_slug()
    {
        $this->assertEquals('i-love-symfony', $this->text->slug());
    }

    // bool ------------------------------

    /** @test */
    public function it_can_return_isEmpty()
    {
        $this->assertFalse($this->text->isEmpty());
    }

    /** @test */
    public function it_can_return_is()
    {
        $this->assertFalse($this->text->is(Regex::USER));
    }

    /** @test */
    public function it_can_return_contains()
    {
        $this->assertTrue($this->text->contains('love'));
    }

    /** @test */
    public function it_can_return_startsWith()
    {
        $this->assertTrue($this->text->startsWith('i love'));
    }

    /** @test */
    public function it_can_return_endsWith()
    {
        $this->assertTrue($this->text->endsWith('symfony'));
    }

    // int ---------------------------------------

    /** @test */
    public function it_can_return_length()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(14, $this->text->length());
    }

    /** @test */
    public function it_can_return_indexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(7, $this->text->indexOf('symfony'));
    }

    /** @test */
    public function it_can_return_LastIndexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(3, $this->text->indexOf('o'));
    }


}
