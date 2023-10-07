<?php

declare(strict_types=1);

namespace System\Integrate\Testing\Traits;

use System\Http\Response;

trait ResponseStatusTrait
{
    public function assertOk(): void
    {
        $this->assertStatusCode(Response::HTTP_OK, 'Respone code must return ok');
    }

    public function assertNoContent(): void
    {
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, 'Respone code must return no content');
    }

    public function assertNotFound(): void
    {
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, 'Respone code must return Not Found');
    }

    public function assertNotAllowed(): void
    {
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, 'Respone code must return Not Allowed');
    }
}
