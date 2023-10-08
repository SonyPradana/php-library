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

    public function assertCreated(): void
    {
        $this->assertStatusCode(Response::HTTP_CREATED, 'Respone code must return create');
    }

    public function assertNoContent(): void
    {
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, 'Respone code must return no content');
    }

    public function assertBadRequest(): void
    {
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, 'Respone code must return Bad Request');
    }

    public function assertUnauthorized(): void
    {
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, 'Respone code must return Unauthorized');
    }

    public function assertForbidden(): void
    {
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, 'Respone code must return Forbidden');
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
