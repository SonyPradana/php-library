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
}
