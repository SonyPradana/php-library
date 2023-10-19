<?php

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Text\Str;

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
    public function itCanSetHeaderUsingHeaderAndSenitazerHeader()
    {
        $res = new Response('content');
        $res->header('test : test:ok');

        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test:ok', $get_header);
    }

    /** @test */
    public function itCanSetHeaderUsingFollowRequest()
    {
        $req = new Request('test', [], [], [], [], [], ['test' => 'test']);
        $res = new Response('content');

        $res->followRequest($req, ['test']);
        $get_header = $res->getHeaders()['test'];

        $this->assertEquals('test', $get_header);
    }

    /** @test */
    public function itCanGetResponeStatusCode()
    {
        $res = new Response('content', 200);

        $this->assertEquals(200, $res->getStatusCode());
    }

    /** @test */
    public function itCanGetResponeContent()
    {
        $res = new Response('content', 200);

        $this->assertEquals('content', $res->getContent());
    }

    /** @test */
    public function itCanGetTypeOfResponseCode()
    {
        $res = new Response('content', rand(100, 199));
        $this->assertTrue($res->isInformational());

        $res = new Response('content', rand(200, 299));
        $this->assertTrue($res->isSuccessful());

        $res = new Response('content', rand(300, 399));
        $this->assertTrue($res->isRedirection());

        $res = new Response('content', rand(400, 499));
        $this->assertTrue($res->isClientError());

        $res = new Response('content', rand(500, 599));
        $this->assertTrue($res->isServerError());
    }

    /** @test */
    public function itCanChangeProtocolVersion()
    {
        $res = new Response('content');
        $res->setProtocolVersion('1.0');

        $this->assertTrue(Str::contains((string) $res, '1.0'), 'Test protocol version');
    }
}
