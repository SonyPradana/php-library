<?php

use PHPUnit\Framework\TestCase;
use System\Text\Str;
use System\Text\Text;

use function System\Text\string;
use function System\Text\text;

class TextTest extends TestCase
{
    /** @test */
    public function canCreateNewIntanceUsingConstructor()
    {
        $class = new Text('text');

        $this->assertInstanceOf(Text::class, $class);
    }

    /** @test */
    public function canCreateNewIntanceUsingHelper()
    {
        $this->assertInstanceOf(Text::class, string('text'));
        $this->assertInstanceOf(Text::class, text('text'));
    }

    /** @test */
    public function canCreateNewIntanceUsingSTRClass()
    {
        $this->assertInstanceOf(Text::class, Str::of('text'));
    }

    /** @test */
    public function canSetGetCurrentText()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class->getText());
    }

    /** @test */
    public function canSetGetCurrentTextUsingToString()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class);
    }

    /** @test */
    public function canSetNewTextWhitoutReset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->text('string');

        $this->assertEquals('string', $class->getText());
        $this->assertCount(5, $class->logs());
    }

    /** @test */
    public function canSetGetLogOfString()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();

        $this->assertIsArray($class->logs());
        foreach ($class->logs() as $log) {
            $this->assertArrayHasKey('function', $log);
            $this->assertArrayHasKey('return', $log);
            $this->assertArrayHasKey('type', $log);

            if ($log['type'] === 'string') {
                $this->assertIsString($log['return']);
            }
        }
    }

    /** @test */
    public function canSetReset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->reset();

        $this->assertEquals('text', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function canSetRefresh()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->refresh('string');

        $this->assertEquals('string', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function canChainNonStringAndContinueChainWithoutBreak()
    {
        $class = new Text('text');
        $class->upper()->firstUpper();

        $this->assertTrue($class->startsWith('T'));
        $this->assertTrue($class->length() === 4);

        $class->lower();
        $this->assertTrue($class->startsWith('t'));
    }
}
