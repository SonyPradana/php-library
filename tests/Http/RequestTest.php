<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\RequestFactory;

class RequestTest extends TestCase
{
  /** @var Request */
  private $request;

  protected function setUp(): void
  {
    $this->request = new Request(
      'http://localhost/',
      ['query_1' => 'query'],
      ['post_1' => 'post'],
      ['costume' => 'costume'],
      ['cookies' => 'cookies'],
      [[
        'name'      => 'file_name',
        'type'      => 'text',
        'tmp_name'  => 'tmp_name',
        'error'     => 0,
        'size'      => 0
      ]],
      ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
      'POST',
      '127:0:0:1',
      fn() => '{"respone":"ok"}'
    );;
  }

  /**
   * @test
   */
  public function has_same_url()
  {
    $this->assertEquals('http://localhost/', $this->request->getUrl());
  }

  /**
   * @test
   */
  public function has_same_query()
  {
    $this->assertEquals('query', $this->request->getQuery('query_1'));
  }

  /**
   * @test
   */
  public function has_same_post()
  {
    $this->assertEquals('post', $this->request->getPost('post_1'));
  }

  /**
   * @test
   */
  public function has_same_cookies()
  {
    $this->assertEquals('cookies', $this->request->getCookie('cookies'));
  }

  /**
   * @test
   */
  public function has_same_file()
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
  public function has_same_header()
  {
    $this->assertEquals('header', $this->request->getHeaders('header_1'));
  }

  /**
   * @test
   */
  public function has_same_method()
  {
    $this->assertEquals('POST', $this->request->getMethod());
  }

  /**
   * @test
   */
  public function has_same_ip()
  {
    $this->assertEquals('127:0:0:1', $this->request->getRemoteAddress());
  }

  /**
   * @test
   */
  public function has_same_body()
  {
    $this->assertEquals('{"respone":"ok"}', $this->request->getRawBody());
  }

  /**
   * @test
   */
  public function has_same_body_json()
  {
    $this->assertEquals(
      ['respone' => 'ok'],
      $this->request->getJsonBody());
  }

  /**
   * @test
   */
  public function it_not_secure_request()
  {
    $this->assertFalse($this->request->isSecured());
  }

  /**
   * @test
   */
  public function has_header()
  {
    $this->assertTrue($this->request->hasHeader('header_2'));
  }

  /**
   * @test
   */
  public function is_header_contains()
  {
    $this->assertTrue($this->request->isHeader('foo', 'bar'));
  }
}
