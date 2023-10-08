<?php

declare(strict_types=1);

namespace System\Test\Integrate\Testing;

use PHPUnit\Framework\TestCase;
use System\Http\Response;
use System\Integrate\Testing\TestJsonResponse;

final class TestJsonResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itCanTestResponeseAsArray()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));
        $response['test'] = 'test';

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('test', $response['test']);
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssert()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));

        $this->assertEquals(['test' => 'success'], $response->getData());
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertEqual()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => 'success',
            ],
            'error' => null,
        ]));

        $response->assertEqual('data.test', 'success');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertTrue()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => true,
            ],
            'error' => null,
        ]));

        $response->assertTrue('data.test');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertFalse()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertFalse('data.test');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNull()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertNull('error');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNotNull()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => [
                'test' => 'some erroe',
            ],
        ]));

        $response->assertNotNull('error');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertEmpty()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [],
            'error' => null,
        ]));

        $response->assertEmpty('error');
    }

    /**
     * @test
     */
    public function itCanTestResponeseAssertNotEmpty()
    {
        $response = new TestJsonResponse(new Response([
            'status'=> 'ok',
            'code'  => 200,
            'data'  => [
                'test' => false,
            ],
            'error' => null,
        ]));

        $response->assertNotEmpty('error');
    }
}
