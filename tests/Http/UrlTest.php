<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Url;

class UrlTest extends TestCase
{
    /**
     * @test
     */
    public function itUrlParse(): void
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
    public function itUrlParseUsingRequest(): void
    {
        $request = new Request('http://username:password@hostname:9090/path?arg=value#anchor');
        $url     = Url::fromRequest($request);

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
    public function itUrlParseMissingSchema(): void
    {
        $url = Url::parse('//www.example.com/path?googleguy=googley');

        $this->assertEquals('www.example.com', $url->host());
        $this->assertEquals('/path', $url->path());
        $this->assertEquals(['googleguy' => 'googley'], $url->query());
    }

    /**
     * @test
     */
    public function itCanCheckUrlParse(): void
    {
        $url = Url::parse('http://username:password@hostname:9090/path?arg=value#anchor');

        $this->assertTrue($url->hasSchema());
        $this->assertTrue($url->hasHost());
        $this->assertTrue($url->hasPort());
        $this->assertTrue($url->hasUser());
        $this->assertTrue($url->hasPassword());
        $this->assertTrue($url->hasPath());
        $this->assertTrue($url->hasQuery());
        $this->assertTrue($url->hasFragment());
    }

    /**
     * @test
     */
    public function itCanCheckUrlParseMissingSchema(): void
    {
        $url = Url::parse('//www.example.com/path?googleguy=googley');

        $this->assertFalse($url->hasSchema());
        $this->assertTrue($url->hasHost());
        $this->assertFalse($url->hasPort());
        $this->assertFalse($url->hasUser());
        $this->assertFalse($url->hasPassword());
        $this->assertTrue($url->hasPath());
        $this->assertTrue($url->hasQuery());
        $this->assertFalse($url->hasFragment());
    }
}
