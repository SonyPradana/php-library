<?php

declare(strict_types=1);

namespace System\Integrate\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;
use System\Integrate\Http\Karnel;
use System\Integrate\ServiceProvider;
use System\Support\Facades\Facade;

class TestCase extends BaseTestCase
{
    protected Application $app;
    protected Karnel $kernel;
    protected string $class;

    protected function tearDown(): void
    {
        $this->app->flush();
        Facade::flushInstance();
        ServiceProvider::flushModule();
        unset($this->app);
        unset($this->kernel);
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
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     */
    protected function call(
        string $url,
        array $query = [],
        array $post = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $headers = [],
        string $method = 'GET',
        string $remoteAddress = '::1',
        ?string $rawBody = null,
    ): TestResponse {
        /** @var Karnel */
        $kernel   = $this->app->make(Karnel::class);
        $request  = new Request($url, $query, $post, $attributes, $cookies, $files, $headers, $method, $remoteAddress, $rawBody);
        $response = $kernel->handle($request);

        $kernel->terminate($request, $response);

        return new TestResponse($response);
    }

    /**
     * @param array<string, string> $parameter
     */
    protected function get(string $url, array $parameter = []): TestResponse
    {
        return $this->call(url: $url, query: $parameter, method: 'GET');
    }

    /**
     * @param array<string, string> $post
     * @param array<string, string> $files
     */
    protected function post(string $url, array $post, array $files =[]): TestResponse
    {
        return $this->call(url: $url, post: $post, files: $files, method: 'POST');
    }

    /**
     * @param array<string, string> $put
     * @param array<string, string> $files
     */
    protected function put(string $url, array $put, array $files = []): TestResponse
    {
        return $this->call(url: $url, attributes: $put, files: $files, method: 'PUT');
    }

    /**
     * @param array<string, string> $delete
     */
    protected function delete(string $url, array $delete): TestResponse
    {
        return $this->call(url: $url, post: $_POST, method: 'DELETE');
    }
}
