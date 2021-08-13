<?php

use PHPUnit\Framework\TestCase;
use System\Http\RequestFactory;
use System\Http\Response;

class ResponseTest extends TestCase
{
  /**
   * @var Response
   */
  private $response_html;
  /**
   * @var Response
   */
  private $response_json;

  protected function setUp(): void
  {
    $html = '<html><head></head><body></body></html>';
    $json = [
      'status'  => 'ok',
      'code'    => 200,
      'data'    => null
    ];

    $this->response_html = new Response($html, 200, []);
    $this->response_json = new Response($json, 200, []);
  }

  /**
   * @test
   */
  public function it_render_html_response()
  {
    ob_start();
    $this->response_html->html();
    $html = ob_get_clean();

    $this->assertEquals(
      '<html><head></head><body></body></html>',
      $html
    );
  }

  /**
   * @test
   */
  public function it_render_json_response()
  {
    ob_start();
    $this->response_json->json();
    $json = ob_get_clean();

    $this->assertJson($json);
    $this->assertEquals(
      [
        'status'  => 'ok',
        'code'    => 200,
        'data'    => null
      ],
      json_decode($json, true)
    );
  }
  /** @test */
  public function it_can_be_edited_content()
  {
    $this->response_html->setContent('edited');

    ob_start();
    $this->response_html->html();
    $html = ob_get_clean();

    $this->assertEquals(
      'edited',
      $html
    );
  }

}
