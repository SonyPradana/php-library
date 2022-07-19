<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;

class RequestTest extends TestCase
{
    /** @var Request */
    private $request;

    protected function setUp(): void
    {
        $this->request = new Request(
            'http://localhost/',
            ['query_1' => 'query'],
            ['post_1'  => 'post'],
            ['costume' => 'costume'],
            ['cookies' => 'cookies'],
            [[
              'name'      => 'file_name',
              'type'      => 'text',
              'tmp_name'  => 'tmp_name',
              'error'     => 0,
              'size'      => 0,
            ]],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'POST',
            '127:0:0:1',
            fn () => '{"respone":"ok"}'
        );
    }

    /**
     * @test
     */
    public function hasSameUrl()
    {
        $this->assertEquals('http://localhost/', $this->request->getUrl());
    }

    /**
     * @test
     */
    public function hasSameQuery()
    {
        $this->assertEquals('query', $this->request->getQuery('query_1'));
    }

    /**
     * @test
     */
    public function hasSamePost()
    {
        $this->assertEquals('post', $this->request->getPost('post_1'));
    }

    /**
     * @test
     */
    public function hasSameCookies()
    {
        $this->assertEquals('cookies', $this->request->getCookie('cookies'));
    }

    /**
     * @test
     */
    public function hasSameFile()
    {
        $file = $this->request->getFile(0);
        $this->assertEquals(
            'file_name',
            $file['name']
        );
        $this->assertEquals(
            'text',
            $file['type']
        );
        $this->assertEquals(
            'tmp_name',
            $file['tmp_name']
        );
        $this->assertEquals(
            0,
            $file['error']
        );
        $this->assertEquals(
            0,
            $file['size']
        );
    }

    /**
     * @test
     */
    public function hasSameHeader()
    {
        $this->assertEquals('header', $this->request->getHeaders('header_1'));
    }

    /**
     * @test
     */
    public function hasSameMethod()
    {
        $this->assertEquals('POST', $this->request->getMethod());
    }

    /**
     * @test
     */
    public function hasSameIp()
    {
        $this->assertEquals('127:0:0:1', $this->request->getRemoteAddress());
    }

    /**
     * @test
     */
    public function hasSameBody()
    {
        $this->assertEquals('{"respone":"ok"}', $this->request->getRawBody());
    }

    /**
     * @test
     */
    public function hasSameBodyJson()
    {
        $this->assertEquals(
            ['respone' => 'ok'],
            $this->request->getJsonBody());
    }

    /**
     * @test
     */
    public function itNotSecureRequest()
    {
        $this->assertFalse($this->request->isSecured());
    }

    /**
     * @test
     */
    public function hasHeader()
    {
        $this->assertTrue($this->request->hasHeader('header_2'));
    }

    /**
     * @test
     */
    public function isHeaderContains()
    {
        $this->assertTrue($this->request->isHeader('foo', 'bar'));
    }
}
