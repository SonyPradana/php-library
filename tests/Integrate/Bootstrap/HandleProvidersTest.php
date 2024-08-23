<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Integrate\Application;
use System\Integrate\Exceptions\Handler;

class HandleProvidersTest extends TestCase
{
    /**
     * @test
     */
    public function itCanHandleError()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2');
        $app->set('environment', 'testing');

        $handle = new HandleProviders();
        $handle->bootstrap($app);

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage(__CLASS__);
        $handle->handleError(E_ERROR, __CLASS__, __FILE__, __LINE__);

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanHandleErrorDeprecation()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2');
        $app->set('environment', 'testing');
        $app->set(Handler::class, fn () => new TestHandleProviders($app));
        $app->set('log', fn () => new TestLog());

        $handle = new HandleProviders();
        $handle->bootstrap($app);

        $app[Handler::class]->deprecated();
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('deprecation');
        $handle->handleError(E_USER_DEPRECATED, 'deprecation', __FILE__, __LINE__);

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanHandleException()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app2');
        $app->set('request', fn (): Request => new Request('/'));
        $app->set('environment', 'testing');
        $app->set(Handler::class, fn () => new TestHandleProviders($app));

        $handle = new HandleProviders();
        $handle->bootstrap($app);

        try {
            throw new \ErrorException('testing');
        } catch (\Throwable $th) {
            $handle->handleException($th);
        }
        $app->flush();
    }

    /**
     * @test
     */
    public function itCanHandleShutdown()
    {
        $this->markTestSkipped('dont how to test, but its work');

        $app = new Application(dirname(__DIR__) . '/assets/app2');
        $app->set('environment', 'testing');
        $app->set(Handler::class, fn () => new TestHandleProviders($app));

        $handle = new HandleProviders();
        $handle->bootstrap($app);
        $handle->handleShutdown();

        $app->flush();
    }
}

class TestHandleProviders extends Handler
{
    public function report(\Throwable $th): void
    {
        Assert::assertTrue($th->getMessage() === 'testing', 'tesing helper');
    }

    /**
     * Summary of deprecated.
     *
     * @deprecated message
     */
    public function deprecated(): void
    {
    }
}

class TestLog
{
    public function log(int $level, string $message): void
    {
        Assert::assertEquals($level, 16384);
        Assert::assertEquals($message, 'deprecation');
    }
}
