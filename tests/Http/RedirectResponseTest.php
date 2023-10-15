<?php

use PHPUnit\Framework\TestCase;
use System\Http\RedirectResponse;
use System\Integrate\Testing\TestResponse;

class RedirectResponseTest extends TestCase
{
    /** @test */
    public function itCanGetResponeContent()
    {
        $res      = new RedirectResponse('/login');
        $redirect = new TestResponse($res);

        $redirect->assertSee('Redirecting to /login');
        $redirect->assertStatusCode(302);

        foreach ($res->getHeaders() as $key => $value) {
            if ('Location' === $key) {
                $this->assertEquals('/login', $value);
            }
        }
    }
}
