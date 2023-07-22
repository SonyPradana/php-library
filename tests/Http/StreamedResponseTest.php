<?php

declare(strict_types=1);

namespace System\Test\Http;

use PHPUnit\Framework\TestCase;
use System\Http\Exceptions\StreamedResponseCallable;
use System\Http\Request;
use System\Http\StreamedResponse;

final class StreamedResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itCanUseContructor()
    {
        $response = new StreamedResponse(function () { echo 'php'; }, 200, ['Content-Type' => 'text/plain']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    /**
     * @test
     */
    public function itCanCreateStreamResponeUsingRequest()
    {
        $response = new StreamedResponse(function () { echo 'php'; }, 200, ['Content-Type' => 'application/json']);
        $request  = new Request('', [], [], [], [], [], ['Content-Type' => 'text/plain'], 'HEAD');
        $response->followRequest($request, ['Content-Type']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    /**
     * @test
     */
    public function itCanSendContent()
    {
        $called = 0;

        $response = new StreamedResponse(function () use (&$called) { $called++; });

        (fn () => $this->{'sendContent'}())->call($response);
        $this->assertEquals(1, $called);

        (fn () => $this->{'sendContent'}())->call($response);
        $this->assertEquals(1, $called);
    }

    /**
     * @test
     */
    public function itCanSendContentWithNonCallable()
    {
        $this->expectException(StreamedResponseCallable::class);
        $response = new StreamedResponse(null);
        (fn () => $this->{'sendContent'}())->call($response);
    }
}
