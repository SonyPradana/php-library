<?php

declare(strict_types=1);

namespace System\Integrate\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;

class TestCase extends BaseTestCase
{
    protected Application $app;
    protected Karnel $karnel;
    protected string $class;

    protected function setUp(): void
    {
        // create app
        $this->karnel = $this->app->make(Karnel::class);
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        unset($this->app);
        unset($this->karnel);
    }

    /**
     * @param array<string, string>|string $call   call the given function using the given parameters
     * @param array<string, string>        $params
     */
    protected function json($call, array $params = []): TestJsonResponse
    {
        $data     = $this->app->call($call, $params);
        $response = new Response($data);
        if (array_key_exists('code', $data)) {
            $response->setResponeCode((int) $data['code']);
        }
        if (array_key_exists('headers', $data)) {
            $response->setHeaders($data['headers']);
        }

        return new TestJsonResponse($response);
    }

    /**
     * @param array<string, string> $parameter
     */
    protected function get(string $url, array $parameter = []): TestResponse
    {
        return new TestResponse(
            $this->karnel->handle(new Request($url, $parameter, [], [], [], [], [], 'GET'))
        );
    }

    /**
     * @param array<string, string> $post
     */
    protected function post(string $url, array $post): TestResponse
    {
        return new TestResponse(
            $this->karnel->handle(new Request($url, [], $post, [], [], [], [], 'POST'))
        );
    }
}
