<?php

use PHPUnit\Framework\TestCase;
use System\Http\HeaderCollection;
use System\Text\Str;

class ResponseCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function itCanGenerateHeaderToHeaderString()
    {
        $header = new HeaderCollection([
            'Host'       => 'test.test',
            'Accept'     => 'text/html',
            'Connection' => 'keep-alive',
        ]);

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * @test
     */
    public function itCanGenerateHeaderUsingSetWithValue()
    {
        $header = new HeaderCollection([]);
        $header->set('Host', 'test.test');
        $header->set('Accept', 'text/html');
        $header->set('Connection', 'keep-alive');

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * @test
     */
    public function itCanGenerateHeaderUsingSetWithKeyOnly()
    {
        $header = new HeaderCollection([]);
        $header->setRaw('Host: test.test');
        $header->setRaw('Accept: text/html');
        $header->setRaw('Connection: keep-alive');

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * @test
     */
    public function itCanGenerateHeaderUsingSetWithKeyOnlyButThrowError()
    {
        $header  = new HeaderCollection([]);
        $message = '';
        try {
            $header->setRaw('Host=test.test');
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }

        $this->assertEquals('Invalid header structur Host=test.test.', $message);
    }
}
