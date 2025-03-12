<?php

namespace System\Test\Htpp;

use PHPUnit\Framework\TestCase;
use System\Http\JsonResponse;

class JsonResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itCanRenderJsonString(): void
    {
        $response = new JsonResponse([
            'languange' => 'php',
            'ver'       => 80,
        ]);

        ob_start();
        $response->send();
        $json = ob_get_clean();

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals('{"languange":"php","ver":80}', $response->getContent());
        $this->assertEquals($data, $response->getData());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getContentType());
    }

    /**
     * @test
     */
    public function itConstructorEmptyCreatesJsonObject()
    {
        $this->markTestSkipped();
        $response = new JsonResponse();
        $this->assertSame('{}', $response->getContent());
    }

    /**
     * @test
     */
    public function itConstructorWithArrayCreatesJsonArray()
    {
        $response = new JsonResponse([0, 1, 2, 3]);
        $this->assertSame('[0,1,2,3]', $response->getContent());
    }

    /**
     * @test
     */
    public function itSetJson()
    {
        $response = new JsonResponse();
        $response->setJson('1');
        $this->assertEquals('1', $response->getContent());

        $response = new JsonResponse();
        $response->setJson('true');
        $this->assertEquals('true', $response->getContent());
    }

    /**
     * @test
     */
    public function itJsonEncodeFlags()
    {
        $this->markTestSkipped('work but require setData');
        $response = new JsonResponse();
        // $response->setData('<>\'&"');

        $this->assertEquals('"\u003C\u003E\u0027\u0026\u0022"', $response->getContent());
    }

    /**
     * @test
     */
    public function itGetEncodingOptions()
    {
        $response = new JsonResponse();

        $this->assertEquals(\JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_AMP | \JSON_HEX_QUOT, $response->getEncodingOptions());
    }

    /**
     * @test
     */
    public function itCanSetEncodingOptions(): void
    {
        $this->markTestSkipped('need more inspeks');
        $response = new JsonResponse();
        $response->setData([1, 2, 3]);

        $this->assertEquals('[[1,2,3]]', $response->getContent());

        $response->setEncodingOptions(\JSON_FORCE_OBJECT);

        $this->assertEquals('{"0":{"0":1,"1":2,"2":3}}', $response->getContent());
    }
}
