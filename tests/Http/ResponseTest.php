<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
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

    /** @test */
    public function itCanSetHeaderUsingConstructContent()
    {
        $res = new Response([
            'headers' => [
                'test' => 'test',
            ],
        ]);

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /** @test */
    public function itCanSetHeaderUsingConstructHeader()
    {
        $res = new Response('content', 200, ['test' => 'test']);

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /** @test */
    public function itCanSetHeaderUsingSetHeaders()
    {
        $res = new Response('content');
        $res->setHeaders(['test' => 'test']);

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /** @test */
    public function itCanSetHeaderUsingHeader()
    {
        $res = new Response('content');
        $res->header('test', 'test');

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /** @test */
    public function itCanSetHeaderUsingFollowRequest()
    {
        $req = new Request('test', null, null, null, null, null, ['test' => 'test']);
        $res = new Response('content');

        $res->followRequest($req, ['test']);
        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }
}
