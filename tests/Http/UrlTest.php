<?php

use PHPUnit\Framework\TestCase;
use System\Http\Url;

class UrlTest extends TestCase
{
    /**
     * @test
     */
    public function testUrlParse(): void
    {
        $url = Url::parse('http://username:password@hostname:9090/path?arg=value#anchor');

        $this->assertEquals('http', $url->schema());
        $this->assertEquals('hostname', $url->host());
        $this->assertEquals(9090, $url->port());
        $this->assertEquals('username', $url->user());
        $this->assertEquals('password', $url->password());
        $this->assertEquals('/path', $url->path());
        $this->assertEquals(['arg' => 'value'], $url->query());
        $this->assertEquals('anchor', $url->fragment());
    }

    /**
     * @test
     */
    public function testUrlParseMissingSchema(): void
    {
        $url = Url::parse('//www.example.com/path?googleguy=googley');

        $this->assertEquals('www.example.com', $url->host());
        $this->assertEquals('/path', $url->path());
        $this->assertEquals(['googleguy' => 'googley'], $url->query());
    }
}
