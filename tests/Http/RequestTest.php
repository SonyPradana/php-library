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
            [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'GET',
            '127:0:0:1',
            '{"respone":"ok"}'
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
        $this->assertEquals('query', $this->request->query()->get('query_1'));
    }

    /**
     * @test
     */
    public function hasSamePost()
    {
        $this->assertEquals('post', $this->request->getPost('post_1'));
        $this->assertEquals('post', $this->request->post()->get('post_1'));
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
        $file = $this->request->getFile('file_1');
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
        $this->assertEquals('GET', $this->request->getMethod());
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

    /**
     * @test
     */
    public function itCanGetAllProperty()
    {
        $this->assertEquals($this->request->all(), [
            'header_1'          => 'header',
            'header_2'          => 123,
            'foo'               => 'bar',
            'query_1'           => 'query',
            'post_1'            => 'post',
            'costume'           => 'costume',
            'x-raw'             => '{"respone":"ok"}',
            'x-method'          => 'GET',
            'cookies'           => 'cookies',
            'files'             => [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itCanThrowErrorWhenBodyEmpty()
    {
        $request = new Request('test.test', [], [], [], [], [], ['Content-Length' => '1'], 'PUT', '::1', '');

        $this->expectErrorMessage('Request body is empty.');
        $request->all();
    }

    /**
     * @test
     */
    public function itCanThrowErrorWhenBodyCantDecode()
    {
        $request = new Request('test.test', [], [], [], [], [], ['Content-Length' => '1'], 'PUT', '::1', 'nobody');

        $this->expectErrorMessage('Could not decode request body.');
        $request->all();
    }

    /**
     * @test
     */
    public function itCanAccessAsArrayGet()
    {
        $this->assertEquals('query', $this->request['query_1']);
        $this->assertEquals(null, $this->request['query_x']);
    }

    /**
     * @test
     */
    public function itCanAccessAsArrayHas()
    {
        $this->assertTrue(isset($this->request['query_1']));
        $this->assertFalse(isset($this->request['query_x']));
    }

    /**
     * @test
     */
    public function itCanAccessUsingGetter()
    {
        $this->assertEquals('query', $this->request->query_1);
    }
}
