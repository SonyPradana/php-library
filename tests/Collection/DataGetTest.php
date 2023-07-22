<?php

use PHPUnit\Framework\TestCase;

class DataGetTest extends TestCase
{
    private array $array = [
        'awsome' => [
            'lang' => [
                'go',
                'rust',
                'php',
                'python',
                'js',
            ],
        ],
        'fav' => [
            'lang' => [
                'rust',
                'php',
            ],
        ],
        'dont_know' => ['lang_' => ['back_end' => ['erlang', 'h-lang']]],
        'one'       => ['two' => ['three' => ['four' => ['five' => 6]]]],
    ];

    /** @test */
    public function itCanFindItemUsingDotKeys()
    {
        $this->assertEquals(6, data_get($this->array, 'one.two.three.four.five'));
    }

    /** @test */
    public function itCanFindItemUsingDotKeysButDontExist()
    {
        $this->assertEquals('six', data_get($this->array, '1.2.3.4.5', 'six'));
    }

    /** @test */
    public function itCanFindItemUsingDotKeysWithWildchart()
    {
        $this->assertEquals([
            ['go', 'rust', 'php', 'python', 'js'],
            ['rust', 'php'],
        ], data_get($this->array, '*.lang'));
    }

    /** @test */
    public function itcanGeKeysAsInteger()
    {
        $array5 = ['foo', 'bar', 'baz'];
        $this->assertEquals('bar', data_get($array5, 1));
        $this->assertNull(data_get($array5, 3));
        $this->assertEquals('qux', data_get($array5, 3, 'qux'));
    }
}
