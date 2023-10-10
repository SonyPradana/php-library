<?php

declare(strict_types=1);

namespace System\Test\Integrate\Testing;

use PHPUnit\Framework\TestCase;
use System\Http\Response;
use System\Integrate\Testing\TestResponse;

final class TestResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itCanTestResponeseAssert()
    {
        $response = new TestResponse(new Response('test', 200, []));

        $this->assertEquals('test', $response->getContent());
        $response->assertSee('test');
        $response->assertStatusCode(200);
    }
}
