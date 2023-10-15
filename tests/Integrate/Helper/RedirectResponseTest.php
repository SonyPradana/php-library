<?php

declare(strict_types=1);

namespace System\Test\Integrate\Helper;

use PHPUnit\Framework\TestCase;
use System\Integrate\Testing\TestResponse;
use System\Router\Router;

final class RedirectResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itRiderectToCorrectUrl()
    {
        Router::get('/test/(:any)', fn ($test) => $test)->name('test');
        $redirect = redirect_route('test', ['ok']);
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test/ok');

        Router::reset();
    }

    /**
     * @test
     */
    public function itRiderectToCorrectUrlWithPlanUrl()
    {
        Router::get('/test', fn ($test) => $test)->name('test');
        $redirect = redirect_route('test');
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test');

        Router::reset();
    }

    /**
     * @test
     */
    public function itThrowErrorWhenPatternNotExist()
    {
        Router::get('/test/(:test)', fn ($test) => $test)->name('test');
        $message = '';
        try {
            redirect_route('test', ['test']);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }
        $this->assertEquals('parameter not matches with any pattern.', $message);

        Router::reset();
    }

    /**
     * @test
     */
    public function itCanRedirectUsingUlrGiven()
    {
        $redirect = redirect('/test');
        $response = new TestResponse($redirect);
        $response->assertStatusCode(302);
        $response->assertSee('Redirecting to /test');
    }
}
