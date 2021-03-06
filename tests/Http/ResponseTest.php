<?php

use PHPUnit\Framework\TestCase;
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
      'data'    => null,
    ];

        $this->response_html = new Response($html, 200, []);
        $this->response_json = new Response($json, 200, []);
    }

    /**
     * @test
     */
    public function itRenderHtmlResponse()
    {
        ob_start();
        $this->response_html->html()->send();
        $html = ob_get_clean();

        $this->assertEquals(
            '<html><head></head><body></body></html>',
            $html
        );
    }

    /**
     * @test
     */
    public function itRenderJsonResponse()
    {
        ob_start();
        $this->response_json->json()->send();
        $json = ob_get_clean();

        $this->assertJson($json);
        $this->assertEquals(
            [
              'status'  => 'ok',
              'code'    => 200,
              'data'    => null,
            ],
            json_decode($json, true)
        );
    }

    /** @test */
    public function itCanBeEditedContent()
    {
        $this->response_html->setContent('edited');

        ob_start();
        $this->response_html->html()->send();
        $html = ob_get_clean();

        $this->assertEquals(
            'edited',
            $html
        );
    }
}
