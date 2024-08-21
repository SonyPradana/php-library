<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;

class HandleProvidersTest extends TestCase
{
    /**
     * @test
     */
    public function itCanHandleException()
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
}
