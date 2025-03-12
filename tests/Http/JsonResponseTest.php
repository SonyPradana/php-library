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
        $json_response = new JsonResponse([
            'languange' => 'php',
            'ver'       => 80,
        ]);

        ob_start();
        $json_response->send();
        $json = ob_get_clean();

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals($data, $json_response->getData());
        $this->assertEquals(200, $json_response->getStatusCode());
        $this->assertEquals('application/json', (fn () => $this->{'content_type'})->call($json_response));
    }
}
