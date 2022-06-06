<?php

use PHPUnit\Framework\TestCase;
use System\Text\Text;
use System\Text\Str;

use function System\Text\string;
use function System\Text\text;

class TextTest extends TestCase
{
    /** @test */
    public function can_create_new_intance_using_constructor()
    {
        $class = new Text('text');

        $this->assertInstanceOf(Text::class, $class);
    }

    /** @test */
    public function can_create_new_intance_using_helper()
    {
        $this->assertInstanceOf(Text::class, string('text'));
        $this->assertInstanceOf(Text::class, text('text'));
    }

    /** @test */
    public function can_create_new_intance_using_STR_class()
    {
        $this->assertInstanceOf(Text::class, Str::of('text'));
    }

    /** @test */
    public function can_set_get_current_text()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class->getText());
    }

    /** @test */
    public function can_set_get_current_text_using_toString()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class);
    }

    /** @test */
    public function can_set_new_text_whitout_reset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->text('string');

        $this->assertEquals('string', $class->getText());
        $this->assertCount(5, $class->logs());
    }

    /** @test */
    public function can_set_get_log_of_string()
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
    public function can_set_reset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->reset();

        $this->assertEquals('text', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function can_set_refresh()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->refresh('string');

        $this->assertEquals('string', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function can_chain_non_string_and_continue_chain_without_break()
    {
        $class = new Text('text');
        $class->upper()->firstUpper();

        $this->assertTrue($class->startsWith('T'));
        $this->assertTrue($class->length() === 4);

        $class->lower();
        $this->assertTrue($class->startsWith('t'));
    }
}
