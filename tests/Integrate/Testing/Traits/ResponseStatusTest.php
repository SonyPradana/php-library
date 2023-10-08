<?php

declare(strict_types=1);

namespace System\Test\Integrate\Testing\Traits;

use PHPUnit\Framework\TestCase;
use System\Http\Response;
use System\Integrate\Testing\TestResponse;

final class ResponseStatusTrait extends TestCase
{
    /**
     * @test
     */
    public function itCanTestResponeseAssertOk()
    {
        $response = new TestResponse(new Response('test', 200, []));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertCreate()
    {
        $response = new TestResponse(new Response('test', 201, []));

        $response->assertCreated();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNoContent()
    {
        $response = new TestResponse(new Response('', 204, []));

        $response->assertNoContent();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertBadRequest()
    {
        $response = new TestResponse(new Response('', 400, []));

        $response->assertBadRequest();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertUnauthorized()
    {
        $response = new TestResponse(new Response('', 401, []));

        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertForbidden()
    {
        $response = new TestResponse(new Response('', 403, []));

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNotFound()
    {
        $response = new TestResponse(new Response('', 404, []));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNotAllowed()
    {
        $response = new TestResponse(new Response('', 404, []));

        $response->assertNotFound();
    }
}
